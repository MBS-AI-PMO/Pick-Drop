<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentSetting;
use App\Models\Student;
use App\Models\User;
use App\Services\InvoiceService;
use App\Services\StripeService;
use App\Support\AppPagination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoices,
        private readonly StripeService $stripe
    ) {
    }

    public function index(Request $request)
    {
        $this->invoices->markOverdueInvoices();

        $query = Invoice::with(['customer', 'student'])
            ->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('method')) {
            $query->where('payment_method', $request->method);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('student', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $invoices = $query->paginate(AppPagination::PER_PAGE)->withQueryString();
        $customers = User::query()
            ->whereIn('role', ['parent', 'self'])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);
        $studentsByParent = Student::query()
            ->whereIn('parent_id', $customers->pluck('id'))
            ->get(['id', 'parent_id', 'name'])
            ->groupBy('parent_id')
            ->map(fn ($rows) => $rows->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])->values());
        $settings = PaymentSetting::current();

        $summary = [
            'collected' => (float) Invoice::query()->where('status', Invoice::STATUS_PAID)->sum('amount_paid'),
            'pending' => (float) Invoice::query()->where('status', Invoice::STATUS_UNPAID)->selectRaw('SUM(total - amount_paid) as bal')->value('bal'),
            'overdue' => (float) Invoice::query()->where('status', Invoice::STATUS_OVERDUE)->selectRaw('SUM(total - amount_paid) as bal')->value('bal'),
            'count' => Invoice::count(),
        ];

        return view('pickdrop.payments.index', compact('invoices', 'customers', 'settings', 'summary', 'studentsByParent'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'student_id' => ['nullable', 'integer', 'exists:students,id'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $customer = User::findOrFail($validated['user_id']);
        if (!in_array(strtolower((string) $customer->role), ['parent', 'self'], true)) {
            return back()->withInput()->with('error', 'Invoices can only be issued to parent or self accounts.');
        }

        if (!empty($validated['student_id'])) {
            $owns = Student::where('id', $validated['student_id'])->where('parent_id', $customer->id)->exists();
            if (!$owns) {
                return back()->withInput()->with('error', 'Selected student does not belong to this customer.');
            }
        }

        try {
            $invoice = $this->invoices->create($validated, $validated['items'], $request->user()->id);

            return redirect()
                ->route('payments.show', $invoice)
                ->with('success', 'Invoice created and emailed to the customer.');
        } catch (Throwable $e) {
            Log::error('Failed to create invoice', ['error' => $e->getMessage()]);

            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(Invoice $invoice)
    {
        $invoice->syncOverdueStatus();
        $invoice->load(['customer', 'student', 'items', 'payments.recorder', 'issuer', 'pickupRequest']);
        $settings = PaymentSetting::current();
        $banks = \App\Support\PakistaniBanks::names();

        return view('pickdrop.payments.show', compact('invoice', 'settings', 'banks'));
    }

    public function print(Invoice $invoice)
    {
        $invoice->load(['customer', 'student', 'items', 'payments']);
        $settings = PaymentSetting::current();

        return view('pickdrop.payments.print', compact('invoice', 'settings'));
    }

    public function send(Invoice $invoice)
    {
        try {
            $this->invoices->send($invoice);

            return back()->with('success', 'Invoice emailed to ' . $invoice->customer?->email . '.');
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function recordPayment(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'bank_name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $note = 'Bank: ' . $validated['bank_name'];
        if (filled($validated['notes'] ?? null)) {
            $note .= '. ' . $validated['notes'];
        }

        try {
            $this->invoices->recordPayment(
                $invoice,
                Payment::METHOD_BANK,
                (float) $validated['amount'],
                Payment::STATUS_COMPLETED,
                [
                    'reference' => $validated['reference'] ?? null,
                    'notes' => $note,
                ],
                $request->user()->id
            );

            return back()->with('success', 'Payment received. Invoice emailed to the customer.');
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function confirmBank(Request $request, Payment $payment)
    {
        try {
            $this->invoices->confirmBankPayment($payment, $request->user()->id);

            return back()->with('success', 'Bank transfer confirmed. Receipt emailed to the customer.');
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function rejectBank(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $this->invoices->rejectBankPayment($payment, $validated['reason'], $request->user()->id);

            return back()->with('success', 'Bank transfer rejected. Customer can submit payment again.');
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function refundPayment(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $this->invoices->refundPayment($payment, $validated['reason'], $request->user()->id);

            return back()->with('success', 'Payment refunded. Invoice balance updated.');
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function stripeCheckout(Invoice $invoice)
    {
        try {
            $session = $this->invoices->createStripeCheckout($invoice);

            return redirect()->away($session['url']);
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function stripeComplete(Request $request)
    {
        $sessionId = (string) $request->query('session_id', '');
        $message = 'If the payment succeeded, the invoice will be marked paid shortly.';
        $invoice = null;

        if ($sessionId !== '') {
            try {
                $session = $this->stripe->retrieveSession($sessionId);
                if (($session['payment_status'] ?? null) === 'paid') {
                    $invoice = $this->invoices->markPaidFromStripeSession($session);
                    $message = $invoice
                        ? 'Payment received. A receipt has been emailed.'
                        : $message;
                }
            } catch (Throwable $e) {
                Log::warning('Stripe complete page failed', ['error' => $e->getMessage()]);
            }
        }

        return view('pickdrop.payments.stripe-complete', [
            'invoice' => $invoice,
            'message' => $message,
            'success' => (bool) $invoice?->isPaid(),
        ]);
    }

    public function stripeCancel(Invoice $invoice)
    {
        if (auth()->check()) {
            return redirect()
                ->route('payments.show', $invoice)
                ->with('error', 'Stripe checkout was cancelled. The invoice is still unpaid.');
        }

        return view('pickdrop.payments.stripe-complete', [
            'invoice' => $invoice,
            'message' => 'Payment was cancelled. You can try again from the PickDrop app.',
            'success' => false,
        ]);
    }

    public function destroy(Invoice $invoice)
    {
        $number = $invoice->invoice_number;

        try {
            $this->invoices->delete($invoice);

            return redirect()
                ->route('payments.index')
                ->with('success', $number . ' deleted.');
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancel(Invoice $invoice)
    {
        try {
            $this->invoices->cancel($invoice);

            return back()->with('success', 'Invoice cancelled.');
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroyPayment(Payment $payment)
    {
        $invoice = $payment->invoice;

        try {
            $payment->delete();
            if ($invoice) {
                $this->invoices->recalculate($invoice->fresh());
            }

            return back()->with('success', 'Payment deleted.');
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        $this->invoices->markOverdueInvoices();

        $rows = Invoice::with(['customer', 'student'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('id')
            ->get();

        $filename = 'invoices-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Invoice', 'Customer', 'Email', 'Student', 'Issue Date', 'Due Date', 'Total', 'Paid', 'Balance', 'Status', 'Method']);
            foreach ($rows as $invoice) {
                fputcsv($out, [
                    $invoice->invoice_number,
                    $invoice->customer?->name,
                    $invoice->customer?->email,
                    $invoice->student?->name,
                    $invoice->issue_date?->format('Y-m-d'),
                    $invoice->due_date?->format('Y-m-d'),
                    number_format($invoice->total, 2, '.', ''),
                    number_format($invoice->amount_paid, 2, '.', ''),
                    number_format($invoice->balance(), 2, '.', ''),
                    $invoice->status,
                    $invoice->payment_method,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
