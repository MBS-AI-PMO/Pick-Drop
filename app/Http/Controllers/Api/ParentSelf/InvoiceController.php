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

            return $this->successResponse([
                'stripe_enabled' => false,
                'bank' => $settings->bankDetails(),
                'banks' => \App\Support\PakistaniBanks::names(),
                'company' => [
                    'name' => $settings->company_name,
                    'email' => $settings->company_email,
                    'phone' => $settings->company_phone,
                ],
            ], 'Payment methods');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to load payment methods');
        }
    }

    public function payStripe(Request $request, Invoice $invoice): JsonResponse
    {
        return $this->errorResponse('Card payments are not available. Please pay by bank transfer.', 422);
    }

    public function payBank(Request $request, Invoice $invoice): JsonResponse
    {
        try {
            $denied = $this->denyInvoiceOwner($request, $invoice);
            if ($denied) {
                return $denied;
            }

            $validated = $request->validate([
                'reference' => ['required', 'string', 'max:100'],
                'notes' => ['nullable', 'string', 'max:500'],
                'proof' => ['nullable', 'image', 'max:5120'],
            ]);

            $payment = $this->invoices->submitBankTransfer(
                $invoice,
                $validated['reference'],
                $request->file('proof'),
                $validated['notes'] ?? null
            );

            return $this->successResponse([
                'payment' => $payment->toApiArray(),
                'invoice' => $invoice->fresh(['items', 'payments', 'student'])->toApiArray(),
            ], 'Bank transfer submitted. Invoice PDF has been emailed. We will confirm once the amount is received.');
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
