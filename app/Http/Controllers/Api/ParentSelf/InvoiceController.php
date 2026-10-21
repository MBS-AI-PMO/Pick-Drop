<?php

namespace App\Http\Controllers\Api\ParentSelf;

use App\Models\Invoice;
use App\Models\PaymentSetting;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class InvoiceController extends BaseApiController
{
    public function __construct(private readonly InvoiceService $invoices)
    {
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $denied = $this->denyUnlessAccountType($user, $request);
            if ($denied) {
                return $denied;
            }

            $this->invoices->markOverdueInvoices();

            $invoices = Invoice::query()
                ->with(['items', 'student', 'payments'])
                ->where('user_id', $user->id)
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
                ->latest('id')
                ->paginate(\App\Support\AppPagination::PER_PAGE);

            $invoices->getCollection()->transform(fn (Invoice $invoice) => $invoice->toApiArray());

            return $this->successResponse($invoices, 'Invoices');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch invoices');
        }
    }

    public function show(Request $request, Invoice $invoice): JsonResponse
    {
        try {
            $denied = $this->denyInvoiceOwner($request, $invoice);
            if ($denied) {
                return $denied;
            }

            $invoice->syncOverdueStatus();

            return $this->successResponse($invoice->toApiArray(), 'Invoice detail');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch invoice');
        }
    }

    public function methods(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $denied = $this->denyUnlessAccountType($user, $request);
            if ($denied) {
                return $denied;
            }

            $settings = PaymentSetting::current();
            $platform = \App\Models\PlatformSetting::current();

            return $this->successResponse([
                'currency' => 'PKR',
                'screenshot' => [
                    'id' => 'screenshot',
                    'enabled' => true,
                    'label' => 'Payment screenshot',
                    'fields' => [
                        ['key' => 'proof', 'label' => 'Payment screenshot', 'required' => true],
                        ['key' => 'method', 'label' => 'How you paid', 'required' => false, 'options' => ['bank_transfer', 'jazzcash', 'easypaisa', 'manual']],
                        ['key' => 'reference', 'label' => 'Transaction reference', 'required' => false],
                        ['key' => 'notes', 'label' => 'Note', 'required' => false],
                    ],
                    'statuses' => [
                        ['key' => 'pending', 'label' => 'Pending', 'meaning' => 'Screenshot has not been sent yet.'],
                        ['key' => 'not_received', 'label' => 'Not received', 'meaning' => 'Screenshot sent. Admin has not confirmed it yet.'],
                        ['key' => 'received', 'label' => 'Received', 'meaning' => 'Admin confirmed the payment was received.'],
                    ],
                    'hint' => 'Pay by bank, JazzCash, or EasyPaisa, then upload the screenshot. Status stays pending until the screenshot is sent.',
                ],
                'bank' => $settings->bankDetails(),
                'banks' => \App\Support\PakistaniBanks::names(),
                'company' => [
                    'name' => $settings->company_name,
                    'email' => $settings->company_email,
                    'phone' => $settings->company_phone,
                ],
                'pickup_otp_enabled' => (bool) $platform->pickup_otp_enabled,
                'cancel' => [
                    'hours' => (int) $platform->cancel_hours,
                    'fee_percent' => (float) $platform->cancel_fee_percent,
                ],
            ], 'Payment methods');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to load payment methods');
        }
    }

    public function payStripe(Request $request, Invoice $invoice): JsonResponse
    {
        try {
            $denied = $this->denyInvoiceOwner($request, $invoice);
            if ($denied) {
                return $denied;
            }

            $session = $this->invoices->createStripeCheckout(
                $invoice,
                url('/payments/stripe/complete') . '?session_id={CHECKOUT_SESSION_ID}',
                url('/payments/stripe/cancel/' . $invoice->id)
            );

            return $this->successResponse([
                'gateway' => 'stripe',
                'checkout_url' => $session['url'] ?? $session['checkout_url'] ?? null,
                'session' => $session,
            ], 'Stripe checkout created');
        } catch (RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to start card payment');
        }
    }

    public function payScreenshot(Request $request, Invoice $invoice): JsonResponse
    {
        try {
            $denied = $this->denyInvoiceOwner($request, $invoice);
            if ($denied) {
                return $denied;
            }

            $validated = $request->validate([
                'proof' => ['required', 'image', 'max:5120'],
                'method' => ['nullable', 'in:bank_transfer,jazzcash,easypaisa,manual'],
                'reference' => ['nullable', 'string', 'max:100'],
                'notes' => ['nullable', 'string', 'max:500'],
            ]);

            $payment = $this->invoices->submitPaymentScreenshot(
                $invoice,
                $request->file('proof'),
                $validated['method'] ?? \App\Models\Payment::METHOD_MANUAL,
                $validated['reference'] ?? null,
                $validated['notes'] ?? null
            );

            $fresh = $invoice->fresh(['items', 'payments', 'student']);

            return $this->successResponse([
                'receipt_status' => $fresh->receiptStatus(),
                'receipt_status_label' => 'Not received',
                'paid' => false,
                'payment' => $payment->toApiArray(),
                'invoice' => $fresh->toApiArray(),
            ], 'Screenshot sent. Status is not received until admin confirms.');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to submit payment screenshot');
        }
    }

    public function payStatus(Request $request, Invoice $invoice): JsonResponse
    {
        try {
            $denied = $this->denyInvoiceOwner($request, $invoice);
            if ($denied) {
                return $denied;
            }

            $invoice->loadMissing('payments');
            $receipt = $invoice->receiptStatus();

            return $this->successResponse([
                'receipt_status' => $receipt,
                'receipt_status_label' => match ($receipt) {
                    \App\Models\Payment::RECEIPT_RECEIVED => 'Received',
                    \App\Models\Payment::RECEIPT_NOT_RECEIVED => 'Not received',
                    default => 'Pending',
                },
                'paid' => $invoice->isPaid(),
                'status' => $invoice->status,
                'invoice' => $invoice->toApiArray(),
            ], 'Payment receipt status');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to check payment status');
        }
    }

    public function payBank(Request $request, Invoice $invoice): JsonResponse
    {
        try {
            $denied = $this->denyInvoiceOwner($request, $invoice);
            if ($denied) {
                return $denied;
            }

            $validated = $request->validate([
                'reference' => ['nullable', 'string', 'max:100'],
                'notes' => ['nullable', 'string', 'max:500'],
                'proof' => ['required', 'image', 'max:5120'],
            ]);

            $payment = $this->invoices->submitPaymentScreenshot(
                $invoice,
                $request->file('proof'),
                \App\Models\Payment::METHOD_BANK,
                $validated['reference'] ?? null,
                $validated['notes'] ?? null
            );

            $fresh = $invoice->fresh(['items', 'payments', 'student']);

            return $this->successResponse([
                'receipt_status' => $fresh->receiptStatus(),
                'receipt_status_label' => 'Not received',
                'paid' => false,
                'payment' => $payment->toApiArray(),
                'invoice' => $fresh->toApiArray(),
            ], 'Screenshot sent. Status is not received until admin confirms.');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to submit bank transfer');
        }
    }

    private function denyInvoiceOwner(Request $request, Invoice $invoice): ?JsonResponse
    {
        $user = $request->user();
        $denied = $this->denyUnlessAccountType($user, $request);
        if ($denied) {
            return $denied;
        }

        if ((int) $invoice->user_id !== (int) $user->id) {
            return $this->errorResponse('Invoice not found', 404);
        }

        return null;
    }
}
