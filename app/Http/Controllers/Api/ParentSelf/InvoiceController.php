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
                'stripe_enabled' => $settings->hasStripe(),
                'jazzcash_enabled' => (bool) $platform->jazzcash_enabled,
                'easypaisa_enabled' => (bool) $platform->easypaisa_enabled,
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

    public function payJazzcash(Request $request, Invoice $invoice): JsonResponse
    {
        try {
            $denied = $this->denyInvoiceOwner($request, $invoice);
            if ($denied) {
                return $denied;
            }

            $payload = app(\App\Services\LocalPaymentService::class)->jazzcashCheckout($invoice);

            return $this->successResponse($payload, 'JazzCash checkout created. Post fields to checkout_url.');
        } catch (RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to start JazzCash payment');
        }
    }

    public function payEasypaisa(Request $request, Invoice $invoice): JsonResponse
    {
        try {
            $denied = $this->denyInvoiceOwner($request, $invoice);
            if ($denied) {
                return $denied;
            }

            $payload = app(\App\Services\LocalPaymentService::class)->easypaisaCheckout($invoice);

            return $this->successResponse($payload, 'EasyPaisa checkout created. Post fields to checkout_url.');
        } catch (RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to start EasyPaisa payment');
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
