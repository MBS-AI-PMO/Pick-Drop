<?php

namespace App\Services;

use App\Mail\InvoiceMail;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\PaymentSetting;
use App\Models\PickDropCharge;
use App\Models\PickupRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Throwable;

class InvoiceService
{
    public function __construct(
        private readonly StripeService $stripe,
        private readonly AppNotificationService $notifier
    ) {
    }

    /**
     * @param  array<int, array{description: string, quantity: float|int|string, unit_price: float|int|string}>  $items
     */
    public function create(array $payload, array $items, ?int $issuedBy = null): Invoice
    {
        $settings = PaymentSetting::current();
        $charge = PickDropCharge::query()->first();

        $invoice = DB::transaction(function () use ($payload, $items, $issuedBy, $settings, $charge) {
            $invoice = Invoice::create([
                'invoice_number' => Invoice::nextNumber($settings->invoice_prefix),
                'user_id' => $payload['user_id'],
                'student_id' => $payload['student_id'] ?? null,
                'pickup_request_id' => $payload['pickup_request_id'] ?? null,
                'issued_by' => $issuedBy,
                'status' => Invoice::STATUS_DRAFT,
                'currency' => strtoupper($payload['currency'] ?? $charge?->currency ?? 'PKR'),
                'tax_percent' => (float) ($payload['tax_percent'] ?? $settings->tax_percent),
                'issue_date' => $payload['issue_date'] ?? now()->toDateString(),
                'due_date' => $payload['due_date'] ?? now()->addDays(7)->toDateString(),
                'notes' => $payload['notes'] ?? null,
                'terms' => $payload['terms'] ?? 'Payment is due by the date shown on this invoice.',
            ]);

            $this->syncItems($invoice, $items);
            $this->recalculate($invoice);

            return $invoice->fresh(['items', 'customer', 'student']);
        });

        Notification::create([
            'title' => 'Invoice created',
            'message' => $invoice->invoice_number . ' was created for ' . ($invoice->customer?->name ?? 'a customer') . '.',
            'type' => 'info',
        ]);

        $this->sendQuietly($invoice);

        return $invoice->fresh(['items', 'customer', 'student']);
    }

    public function createForAcceptedShift(PickupRequest $pickupRequest): Invoice
    {
        $existing = Invoice::query()
            ->where('pickup_request_id', $pickupRequest->id)
            ->whereNotIn('status', [Invoice::STATUS_CANCELLED])
            ->latest('id')
            ->first();

        if ($existing) {
            return $existing->load(['items', 'customer', 'student']);
        }

        $fare = app(ShiftFareService::class);
        $quote = $fare->quoteFromRequest($pickupRequest);
        $fare->apply($pickupRequest);
        $pickupRequest->save();

        $invoice = $this->create([
            'user_id' => $pickupRequest->parent_id,
            'student_id' => $pickupRequest->student_id,
            'pickup_request_id' => $pickupRequest->id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(3)->toDateString(),
            'currency' => $quote['currency'],
            'notes' => sprintf(
                'Advance payment for %d-month pick-drop service (request #%d). Service starts after this invoice is paid.',
                $quote['duration_months'],
                $pickupRequest->id
            ),
            'terms' => 'This is an advance monthly service fee. The shift starts only after full payment. Minimum duration is 1 month. The driver is paid separately by PickDrop at month end.',
        ], [
            [
                'description' => sprintf(
                    'Advance monthly pick-drop: %s km one-way × %d trips (%d month%s, %s to %s)',
                    number_format($quote['distance_km'], 2),
                    $quote['trip_count'],
                    $quote['duration_months'],
                    $quote['duration_months'] === 1 ? '' : 's',
                    $quote['shift_start_date'],
                    $quote['shift_end_date']
                ),
                'quantity' => $quote['trip_count'],
                'unit_price' => $quote['per_trip_amount'],
            ],
        ]);

        $pickupRequest->update([
            'payment_status' => PickupRequest::PAYMENT_UNPAID,
            'estimated_amount' => $invoice->total,
        ]);

        return $invoice;
    }

    public function syncPickupRequestPayment(Invoice $invoice): void
    {
        if (!$invoice->pickup_request_id) {
            return;
        }

        $pickupRequest = PickupRequest::query()->find($invoice->pickup_request_id);
        if (!$pickupRequest) {
            return;
        }

        if ($invoice->isPaid()) {
            $pickupRequest->update(['payment_status' => PickupRequest::PAYMENT_PAID]);
            $this->notifier->notifyShiftPaymentReceived($pickupRequest, $invoice);

            return;
        }

        $pendingBank = $invoice->payments()
            ->where('method', Payment::METHOD_BANK)
            ->where('status', Payment::STATUS_PENDING)
            ->exists();

        $pickupRequest->update([
            'payment_status' => $pendingBank
                ? PickupRequest::PAYMENT_PENDING
                : PickupRequest::PAYMENT_UNPAID,
        ]);
    }

    public function cancelOpenShiftInvoice(PickupRequest $pickupRequest): void
    {
        Invoice::query()
            ->where('pickup_request_id', $pickupRequest->id)
            ->whereNotIn('status', [Invoice::STATUS_PAID, Invoice::STATUS_CANCELLED])
            ->get()
            ->each(function (Invoice $invoice) {
                $this->cancel($invoice);
            });
    }

    /**
     * @param  array<int, array{description: string, quantity: float|int|string, unit_price: float|int|string}>  $items
     */
    public function syncItems(Invoice $invoice, array $items): void
    {
        $invoice->items()->delete();

        foreach ($items as $row) {
            $qty = max(0.01, (float) ($row['quantity'] ?? 1));
            $price = max(0, (float) ($row['unit_price'] ?? 0));
            $invoice->items()->create([
                'description' => $row['description'],
                'quantity' => $qty,
                'unit_price' => $price,
                'total' => round($qty * $price, 2),
            ]);
        }
    }

    public function recalculate(Invoice $invoice): void
    {
        $subtotal = round((float) $invoice->items()->sum('total'), 2);
        $tax = round($subtotal * ((float) $invoice->tax_percent / 100), 2);
        $total = round($subtotal + $tax, 2);

        $paid = round((float) $invoice->payments()->where('status', Payment::STATUS_COMPLETED)->sum('amount'), 2);

        $status = $invoice->status;
        if ($status !== Invoice::STATUS_CANCELLED) {
            if ($paid >= $total && $total > 0) {
                $status = Invoice::STATUS_PAID;
            } elseif ($invoice->due_date && $invoice->due_date->lt(now()->startOfDay()) && $paid < $total) {
                $status = Invoice::STATUS_OVERDUE;
            } elseif ($status === Invoice::STATUS_PAID && $paid < $total) {
                $status = Invoice::STATUS_UNPAID;
            }
        }

        $invoice->update([
            'subtotal' => $subtotal,
            'tax_amount' => $tax,
            'total' => $total,
            'amount_paid' => $paid,
            'status' => $status,
            'paid_at' => $status === Invoice::STATUS_PAID ? ($invoice->paid_at ?? now()) : null,
        ]);
    }

    public function send(Invoice $invoice, bool $markUnpaid = true): void
    {
        $invoice->loadMissing(['items', 'customer', 'student', 'payments']);

        if (!$invoice->customer?->email) {
            throw new RuntimeException('Customer does not have an email address.');
        }

        if ($invoice->status === Invoice::STATUS_CANCELLED) {
            throw new RuntimeException('Cancelled invoices cannot be sent.');
        }

        if ($invoice->items->isEmpty()) {
            throw new RuntimeException('Add at least one line item before sending.');
        }

        $this->deliverInvoiceMail($invoice);

        $invoice->update([
            'sent_at' => now(),
            'status' => $invoice->isPaid()
                ? Invoice::STATUS_PAID
                : ($markUnpaid ? Invoice::STATUS_UNPAID : $invoice->status),
        ]);
        $invoice->syncOverdueStatus();

        $this->notifier->notify(
            (int) $invoice->user_id,
            'invoice_sent',
            'Invoice ' . $invoice->invoice_number,
            'A new invoice of ' . $invoice->formattedTotal() . ' is ready. Please complete payment.',
            ['invoice_id' => $invoice->id, 'invoice_number' => $invoice->invoice_number],
            'payment_reminders'
        );
    }

    public function sendQuietly(Invoice $invoice): void
    {
        try {
            $this->send($invoice);
        } catch (Throwable $e) {
            Log::error('Failed to auto-send invoice', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function recordPayment(
        Invoice $invoice,
        string $method,
        float $amount,
        string $status = Payment::STATUS_COMPLETED,
        array $extra = [],
        ?int $recordedBy = null
    ): Payment {
        if ($invoice->status === Invoice::STATUS_CANCELLED) {
            throw new RuntimeException('Cancelled invoices cannot accept payment.');
        }

        $payment = DB::transaction(function () use ($invoice, $method, $amount, $status, $extra, $recordedBy) {
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'user_id' => $invoice->user_id,
                'amount' => round($amount, 2),
                'currency' => $invoice->currency,
                'method' => $method,
                'status' => $status,
                'reference' => $extra['reference'] ?? null,
                'stripe_payment_intent_id' => $extra['stripe_payment_intent_id'] ?? null,
                'proof_path' => $extra['proof_path'] ?? null,
                'notes' => $extra['notes'] ?? null,
                'recorded_by' => $recordedBy,
                'paid_at' => $status === Payment::STATUS_COMPLETED ? now() : null,
            ]);

            $this->recalculate($invoice->fresh());

            if ($status === Payment::STATUS_COMPLETED) {
                $invoice->refresh();
                if ($invoice->isPaid()) {
                    $invoice->update(['payment_method' => $method]);
                }
            }

            return $payment;
        });

        if ($status === Payment::STATUS_COMPLETED) {
            $this->notifyPayment($invoice->fresh(['customer', 'items', 'student', 'payments']));
        } else {
            $fresh = $invoice->fresh(['customer', 'items', 'student', 'payments']);
            $this->emailInvoicePdfQuietly($fresh);
            $this->notifyBankTransferSubmitted($fresh);
            $this->syncPickupRequestPayment($fresh);
        }

        return $payment;
    }

    public function confirmBankPayment(Payment $payment, ?int $recordedBy = null): Payment
    {
        if ($payment->method !== Payment::METHOD_BANK || $payment->status !== Payment::STATUS_PENDING) {
            throw new RuntimeException('This bank transfer is not waiting for confirmation.');
        }

        $payment->update([
            'status' => Payment::STATUS_COMPLETED,
            'paid_at' => now(),
            'recorded_by' => $recordedBy,
        ]);

        $invoice = $payment->invoice()->firstOrFail();
        $this->recalculate($invoice);
        $invoice->refresh();
        if ($invoice->isPaid()) {
            $invoice->update(['payment_method' => Payment::METHOD_BANK]);
        }

        $this->notifyPayment($invoice->fresh(['customer', 'items', 'student', 'payments']));

        return $payment->fresh();
    }

    public function submitBankTransfer(Invoice $invoice, string $reference, ?UploadedFile $proof = null, ?string $notes = null): Payment
    {
        if (!$invoice->isPayable()) {
            throw new RuntimeException('This invoice cannot accept a bank transfer.');
        }

        $settings = PaymentSetting::current();
        if (!$settings->hasBankDetails()) {
            throw new RuntimeException('Bank transfer is not available.');
        }

        $path = null;
        if ($proof) {
            $path = $proof->store('payment-proofs/' . $invoice->id, 'public');
        }

        return $this->recordPayment(
            $invoice,
            Payment::METHOD_BANK,
            $invoice->balance(),
            Payment::STATUS_PENDING,
            [
                'reference' => $reference,
                'proof_path' => $path,
                'notes' => $notes,
            ]
        );
    }

    public function createStripeCheckout(Invoice $invoice, ?string $successUrl = null, ?string $cancelUrl = null): array
    {
        if (!$invoice->isPayable()) {
            throw new RuntimeException('This invoice is not payable.');
        }

        $invoice->loadMissing(['items', 'customer']);

        if ($invoice->status === Invoice::STATUS_DRAFT) {
            $invoice->update(['status' => Invoice::STATUS_UNPAID]);
        }

        if (!$invoice->sent_at) {
            $this->sendQuietly($invoice);
        }

        $success = $successUrl ?: route('payments.stripe.complete') . '?session_id={CHECKOUT_SESSION_ID}';
        $cancel = $cancelUrl ?: route('payments.stripe.cancel', $invoice);

        return $this->stripe->createCheckoutSession($invoice, $success, $cancel);
    }

    public function markPaidFromStripeSession(array $session): ?Invoice
    {
        $invoiceId = (int) ($session['metadata']['invoice_id'] ?? $session['client_reference_id'] ?? 0);
        if ($invoiceId < 1) {
            return null;
        }

        $invoice = Invoice::query()->find($invoiceId);
        if (!$invoice || $invoice->isPaid()) {
            return $invoice;
        }

        $paymentIntent = $session['payment_intent'] ?? null;
        $intentId = is_array($paymentIntent) ? ($paymentIntent['id'] ?? null) : $paymentIntent;

        if ($intentId && Payment::query()->where('stripe_payment_intent_id', $intentId)->exists()) {
            return $invoice;
        }

        $amount = $invoice->balance();
        $invoice->update([
            'stripe_checkout_session_id' => $session['id'] ?? $invoice->stripe_checkout_session_id,
            'stripe_payment_intent_id' => $intentId ?: $invoice->stripe_payment_intent_id,
        ]);

        $this->recordPayment(
            $invoice,
            Payment::METHOD_STRIPE,
            $amount,
            Payment::STATUS_COMPLETED,
            [
                'reference' => $session['id'] ?? null,
                'stripe_payment_intent_id' => $intentId,
            ]
        );

        return $invoice->fresh();
    }

    public function delete(Invoice $invoice): void
    {
        DB::transaction(function () use ($invoice) {
            $invoice->payments()->delete();
            $invoice->items()->delete();
            $invoice->delete();
        });
    }

    public function cancel(Invoice $invoice): void
    {
        if ($invoice->isPaid()) {
            throw new RuntimeException('Paid invoices cannot be cancelled.');
        }

        $invoice->update([
            'status' => Invoice::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);
    }

    public function markOverdueInvoices(): int
    {
        return Invoice::query()
            ->whereIn('status', [Invoice::STATUS_UNPAID, Invoice::STATUS_OVERDUE])
            ->whereDate('due_date', '<', now()->toDateString())
            ->whereColumn('amount_paid', '<', 'total')
            ->update(['status' => Invoice::STATUS_OVERDUE]);
    }

    private function deliverInvoiceMail(Invoice $invoice): void
    {
        Mail::to($invoice->customer->email)->send(new InvoiceMail($invoice, PaymentSetting::current()));
    }

    private function emailInvoicePdfQuietly(Invoice $invoice): void
    {
        $invoice->loadMissing(['items', 'customer', 'student', 'payments']);

        if (!$invoice->customer?->email || $invoice->status === Invoice::STATUS_CANCELLED || $invoice->items->isEmpty()) {
            Log::warning('Invoice PDF email skipped', [
                'invoice_id' => $invoice->id,
                'email' => $invoice->customer?->email,
                'status' => $invoice->status,
                'items' => $invoice->items->count(),
            ]);

            return;
        }

        try {
            $this->deliverInvoiceMail($invoice);
            if (!$invoice->sent_at) {
                $invoice->update(['sent_at' => now()]);
            }
        } catch (Throwable $e) {
            Log::error('Failed to send invoice PDF email', [
                'invoice_id' => $invoice->id,
                'email' => $invoice->customer->email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function notifyBankTransferSubmitted(Invoice $invoice): void
    {
        $this->notifier->notify(
            (int) $invoice->user_id,
            'bank_transfer_submitted',
            'Bank payment received',
            'We received your bank transfer for invoice ' . $invoice->invoice_number . '. The invoice PDF has been emailed to ' . ($invoice->customer?->email ?: 'you') . '.',
            ['invoice_id' => $invoice->id, 'invoice_number' => $invoice->invoice_number],
            'payment_reminders'
        );

        Notification::create([
            'title' => 'Bank transfer submitted',
            'message' => $invoice->invoice_number . ' bank transfer submitted by ' . ($invoice->customer?->name ?? 'customer') . '. Invoice PDF emailed.',
            'type' => 'warning',
        ]);
    }

    private function notifyPayment(Invoice $invoice): void
    {
        $this->emailInvoicePdfQuietly($invoice);

        $title = $invoice->isPaid() ? 'Payment received' : 'Partial payment received';
        $body = $invoice->isPaid()
            ? 'Thank you. Invoice ' . $invoice->invoice_number . ' of ' . $invoice->formattedTotal() . ' has been paid.'
            : 'We received a payment toward invoice ' . $invoice->invoice_number . '. Remaining balance: ' . $invoice->formatMoney($invoice->balance()) . '.';

        $this->notifier->notify(
            (int) $invoice->user_id,
            'invoice_paid',
            $title,
            $body,
            ['invoice_id' => $invoice->id, 'invoice_number' => $invoice->invoice_number],
            'payment_reminders'
        );

        Notification::create([
            'title' => $invoice->isPaid() ? 'Invoice paid' : 'Invoice payment received',
            'message' => $invoice->invoice_number . ' payment recorded for ' . ($invoice->customer?->name ?? 'customer') . '.',
            'type' => 'success',
        ]);

        $this->syncPickupRequestPayment($invoice);
    }
}
