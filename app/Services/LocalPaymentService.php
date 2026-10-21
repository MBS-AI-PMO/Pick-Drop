<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PlatformSetting;
use App\Support\EasyPaisaSigner;
use App\Support\JazzCashSigner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class LocalPaymentService
{
    public function __construct(private readonly InvoiceService $invoices)
    {
    }

    /**
     * Pay from JazzCash mobile wallet. Invoice is marked paid only if JazzCash returns success.
     *
     * @return array<string, mixed>
     */
    public function payJazzcashWallet(Invoice $invoice, string $mobileNumber, string $cnic): array
    {
        $settings = PlatformSetting::current();
        $this->assertJazzcashConfigured($settings);
        $invoice = $this->preparePayable($invoice);

        $existing = $this->confirmIfGatewayPaid($invoice);
        if ($existing->isPaid()) {
            return $this->paidPayload($existing, 'jazzcash', 'Already paid.');
        }

        $mobile = $this->normalizePkMobile($mobileNumber);
        $cnicDigits = $this->cnicLastSix($cnic);
        $txnRef = $this->newTxnRef('JC');
        $amountPaisa = (int) round($invoice->balance() * 100);
        $datetime = now()->format('YmdHis');

        $fields = [
            'pp_Version' => '1.1',
            'pp_TxnType' => 'MWALLET',
            'pp_Language' => 'EN',
            'pp_MerchantID' => (string) $settings->jazzcash_merchant_id,
            'pp_Password' => (string) $settings->jazzcash_password,
            'pp_TxnRefNo' => $txnRef,
            'pp_Amount' => (string) $amountPaisa,
            'pp_TxnCurrency' => 'PKR',
            'pp_TxnDateTime' => $datetime,
            'pp_BillReference' => Str::limit((string) $invoice->invoice_number, 50, ''),
            'pp_Description' => Str::limit('Invoice ' . $invoice->invoice_number, 50, ''),
            'pp_TxnExpiryDateTime' => now()->addHours(2)->format('YmdHis'),
            'pp_ReturnURL' => $settings->jazzcash_return_url ?: url('/payments/jazzcash/callback'),
            'pp_MobileNumber' => $mobile,
            'pp_CNIC' => $cnicDigits,
            'ppmpf_1' => (string) $invoice->id,
        ];
        $fields['pp_SecureHash'] = JazzCashSigner::hash($fields, (string) $settings->jazzcash_integrity_salt);

        $invoice->update(['gateway_txn_ref' => $txnRef]);

        try {
            $response = Http::timeout(90)
                ->acceptJson()
                ->asForm()
                ->post($this->jazzcashPayUrl($settings), $fields);
        } catch (Throwable $e) {
            Log::warning('JazzCash wallet request failed', [
                'invoice_id' => $invoice->id,
                'txn_ref' => $txnRef,
                'error' => $e->getMessage(),
            ]);

            return $this->pendingPayload($invoice->fresh(), 'jazzcash', $txnRef, 'JazzCash did not respond. Approve the payment in JazzCash if prompted, then tap Refresh.');
        }

        $body = $this->gatewayArray($response->json() ?? $response->body());
        $code = (string) ($body['pp_ResponseCode'] ?? '');

        if ($code === '000') {
            $this->completeGatewayPayment($invoice, Payment::METHOD_JAZZCASH, $txnRef, $body);

            return $this->paidPayload($invoice->fresh(['items', 'payments', 'student']), 'jazzcash', 'JazzCash payment confirmed.');
        }

        $message = (string) ($body['pp_ResponseMessage'] ?? $body['pp_RetreivalReferenceNo'] ?? 'JazzCash did not confirm this payment.');

        Log::info('JazzCash wallet not confirmed', [
            'invoice_id' => $invoice->id,
            'txn_ref' => $txnRef,
            'code' => $code,
            'message' => $message,
        ]);

        return $this->pendingPayload(
            $invoice->fresh(['items', 'payments', 'student']),
            'jazzcash',
            $txnRef,
            $this->userSafeGatewayMessage($message, 'Approve the payment in the JazzCash app, then tap Refresh.')
        );
    }

    /**
     * Pay from EasyPaisa mobile account. Invoice is marked paid only if EasyPaisa returns success.
     *
     * @return array<string, mixed>
     */
    public function payEasypaisaWallet(Invoice $invoice, string $mobileNumber): array
    {
        $settings = PlatformSetting::current();
        $this->assertEasypaisaConfigured($settings);
        $invoice = $this->preparePayable($invoice);

        $existing = $this->confirmIfGatewayPaid($invoice);
        if ($existing->isPaid()) {
            return $this->paidPayload($existing, 'easypaisa', 'Already paid.');
        }

        $mobile = $this->normalizePkMobile($mobileNumber);
        $txnRef = $this->newTxnRef('EP');
        $amount = number_format($invoice->balance(), 2, '.', '');

        $payload = [
            'orderId' => $txnRef,
            'storeId' => (string) $settings->easypaisa_store_id,
            'transactionAmount' => $amount,
            'transactionType' => 'MA',
            'mobileAccountNo' => $mobile,
            'emailAddress' => (string) ($invoice->customer?->email ?: 'billing@pickdrop.local'),
        ];
        $hash = EasyPaisaSigner::hash($payload, (string) $settings->easypaisa_hash_key);
        $payload['hash'] = $hash;

        $invoice->update(['gateway_txn_ref' => $txnRef]);

        try {
            $response = Http::timeout(90)
                ->acceptJson()
                ->withHeaders(['Credentials' => $hash])
                ->post($this->easypaisaPayUrl($settings), $payload);
        } catch (Throwable $e) {
            Log::warning('EasyPaisa wallet request failed', [
                'invoice_id' => $invoice->id,
                'txn_ref' => $txnRef,
                'error' => $e->getMessage(),
            ]);

            return $this->pendingPayload($invoice->fresh(), 'easypaisa', $txnRef, 'EasyPaisa did not respond. Approve the payment in EasyPaisa if prompted, then tap Refresh.');
        }

        $body = $this->gatewayArray($response->json() ?? $response->body());
        if ($this->easypaisaSucceeded($body)) {
            $this->completeGatewayPayment(
                $invoice,
                Payment::METHOD_EASYPAISA,
                (string) ($body['transactionId'] ?? $txnRef),
                $body
            );

            return $this->paidPayload($invoice->fresh(['items', 'payments', 'student']), 'easypaisa', 'EasyPaisa payment confirmed.');
        }

        $message = (string) ($body['responseDesc'] ?? $body['responseMessage'] ?? $body['message'] ?? 'EasyPaisa did not confirm this payment.');

        Log::info('EasyPaisa wallet not confirmed', [
            'invoice_id' => $invoice->id,
            'txn_ref' => $txnRef,
            'body' => $body,
        ]);

        return $this->pendingPayload(
            $invoice->fresh(['items', 'payments', 'student']),
            'easypaisa',
            $txnRef,
            $this->userSafeGatewayMessage($message, 'Approve the payment in the EasyPaisa app, then tap Refresh.')
        );
    }

    public function handleJazzcashCallback(Request $request): ?Invoice
    {
        $settings = PlatformSetting::current();
        $fields = $this->jazzcashRequestFields($request);

        if (! JazzCashSigner::matches($fields, (string) $settings->jazzcash_integrity_salt, $request->input('pp_SecureHash'))) {
            Log::warning('JazzCash callback rejected: invalid hash', [
                'txn' => $request->input('pp_TxnRefNo'),
            ]);

            return null;
        }

        $txn = (string) $request->input('pp_TxnRefNo');
        $invoice = Invoice::query()->where('gateway_txn_ref', $txn)->first();
        if (! $invoice) {
            Log::warning('JazzCash callback rejected: unknown txn', ['txn' => $txn]);

            return null;
        }

        if ($invoice->isPaid()) {
            return $invoice;
        }

        if ((string) $request->input('pp_ResponseCode') !== '000') {
            return $invoice;
        }

        if (! $this->jazzcashAmountMatches($invoice, $request->input('pp_Amount'))) {
            Log::warning('JazzCash callback rejected: amount mismatch', [
                'invoice_id' => $invoice->id,
                'txn' => $txn,
                'pp_Amount' => $request->input('pp_Amount'),
            ]);

            return $invoice;
        }

        $this->completeGatewayPayment($invoice, Payment::METHOD_JAZZCASH, $txn, $fields);

        return $invoice->fresh();
    }

    public function handleEasypaisaCallback(Request $request): ?Invoice
    {
        $settings = PlatformSetting::current();
        $payload = collect($request->all())
            ->except(['hash', 'Credentials'])
            ->map(fn ($v) => is_scalar($v) ? (string) $v : '')
            ->all();

        $givenHash = $request->input('hash', $request->header('Credentials'));
        if (! EasyPaisaSigner::matches($payload, (string) $settings->easypaisa_hash_key, is_string($givenHash) ? $givenHash : null)) {
            Log::warning('EasyPaisa callback rejected: invalid hash', [
                'orderId' => $request->input('orderId'),
            ]);

            return null;
        }

        $txn = (string) $request->input('orderId', $request->input('transactionRef'));
        $invoice = Invoice::query()->where('gateway_txn_ref', $txn)->first();
        if (! $invoice) {
            Log::warning('EasyPaisa callback rejected: unknown txn', ['txn' => $txn]);

            return null;
        }

        if ($invoice->isPaid()) {
            return $invoice;
        }

        if (! $this->easypaisaSucceeded($request->all())) {
            return $invoice;
        }

        $this->completeGatewayPayment(
            $invoice,
            Payment::METHOD_EASYPAISA,
            (string) ($request->input('transactionId') ?: $txn),
            $request->all()
        );

        return $invoice->fresh();
    }

    public function confirmIfGatewayPaid(Invoice $invoice): Invoice
    {
        $invoice->refresh();
        if ($invoice->isPaid() || ! filled($invoice->gateway_txn_ref)) {
            return $invoice;
        }

        $ref = (string) $invoice->gateway_txn_ref;
        if (str_starts_with($ref, 'JC')) {
            return $this->inquireJazzcash($invoice);
        }
        if (str_starts_with($ref, 'EP')) {
            return $this->inquireEasypaisa($invoice);
        }

        return $invoice;
    }

    public function inquireJazzcash(Invoice $invoice): Invoice
    {
        $settings = PlatformSetting::current();
        if (! $this->jazzcashReady($settings) || $invoice->isPaid() || ! filled($invoice->gateway_txn_ref)) {
            return $invoice;
        }

        $fields = [
            'pp_TxnRefNo' => (string) $invoice->gateway_txn_ref,
            'pp_MerchantID' => (string) $settings->jazzcash_merchant_id,
            'pp_Password' => (string) $settings->jazzcash_password,
        ];
        $fields['pp_SecureHash'] = JazzCashSigner::hash($fields, (string) $settings->jazzcash_integrity_salt);

        try {
            $response = Http::timeout(30)->acceptJson()->asForm()->post($this->jazzcashInquireUrl($settings), $fields);
            $body = $this->gatewayArray($response->json() ?? $response->body());
            $code = (string) ($body['pp_ResponseCode'] ?? $body['pp_PaymentResponseCode'] ?? '');
            if ($code === '000') {
                $this->completeGatewayPayment($invoice, Payment::METHOD_JAZZCASH, (string) $invoice->gateway_txn_ref, $body);
            }
        } catch (Throwable $e) {
            Log::warning('JazzCash inquiry failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $invoice->fresh(['items', 'payments', 'student']) ?? $invoice;
    }

    public function inquireEasypaisa(Invoice $invoice): Invoice
    {
        $settings = PlatformSetting::current();
        if (! $this->easypaisaReady($settings) || $invoice->isPaid() || ! filled($invoice->gateway_txn_ref)) {
            return $invoice;
        }

        $payload = [
            'orderId' => (string) $invoice->gateway_txn_ref,
            'storeId' => (string) $settings->easypaisa_store_id,
        ];
        $hash = EasyPaisaSigner::hash($payload, (string) $settings->easypaisa_hash_key);

        try {
            $response = Http::timeout(30)
                ->acceptJson()
                ->withHeaders(['Credentials' => $hash])
                ->post($this->easypaisaInquireUrl($settings), $payload + ['hash' => $hash]);
            $body = $this->gatewayArray($response->json() ?? $response->body());
            if ($this->easypaisaSucceeded($body)) {
                $this->completeGatewayPayment(
                    $invoice,
                    Payment::METHOD_EASYPAISA,
                    (string) ($body['transactionId'] ?? $invoice->gateway_txn_ref),
                    $body
                );
            }
        } catch (Throwable $e) {
            Log::warning('EasyPaisa inquiry failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $invoice->fresh(['items', 'payments', 'student']) ?? $invoice;
    }

    public function jazzcashReady(?PlatformSetting $settings = null): bool
    {
        $settings ??= PlatformSetting::current();

        return $settings->jazzcashReady();
    }

    public function easypaisaReady(?PlatformSetting $settings = null): bool
    {
        $settings ??= PlatformSetting::current();

        return $settings->easypaisaReady();
    }

    private function completeGatewayPayment(Invoice $invoice, string $method, string $reference, array $raw): void
    {
        $invoice->refresh();
        if ($invoice->isPaid() || ! $invoice->isPayable()) {
            return;
        }

        $already = $invoice->payments()
            ->where('method', $method)
            ->where('reference', $reference)
            ->where('status', Payment::STATUS_COMPLETED)
            ->exists();
        if ($already) {
            return;
        }

        $this->invoices->recordPayment(
            $invoice,
            $method,
            $invoice->balance(),
            Payment::STATUS_COMPLETED,
            [
                'reference' => $reference,
                'notes' => Str::limit(json_encode([
                    'code' => $raw['pp_ResponseCode'] ?? $raw['responseCode'] ?? null,
                    'message' => $raw['pp_ResponseMessage'] ?? $raw['responseDesc'] ?? null,
                ]), 500, ''),
            ]
        );
    }

    private function preparePayable(Invoice $invoice): Invoice
    {
        if (! $invoice->isPayable()) {
            throw new RuntimeException('This invoice is not payable.');
        }

        if ($invoice->status === Invoice::STATUS_DRAFT) {
            $invoice->update(['status' => Invoice::STATUS_UNPAID]);
        }

        return $invoice->fresh(['customer']) ?? $invoice;
    }

    private function assertJazzcashConfigured(PlatformSetting $settings): void
    {
        if (! $this->jazzcashReady($settings)) {
            throw new RuntimeException('JazzCash is not configured. Use wallet or bank transfer.');
        }
    }

    private function assertEasypaisaConfigured(PlatformSetting $settings): void
    {
        if (! $this->easypaisaReady($settings)) {
            throw new RuntimeException('EasyPaisa is not configured. Use wallet or bank transfer.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function paidPayload(Invoice $invoice, string $gateway, string $message): array
    {
        return [
            'paid' => true,
            'status' => 'paid',
            'gateway' => $gateway,
            'txn_ref' => $invoice->gateway_txn_ref,
            'message' => $message,
            'invoice' => $invoice->toApiArray(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function pendingPayload(Invoice $invoice, string $gateway, string $txnRef, string $message): array
    {
        return [
            'paid' => false,
            'status' => 'pending',
            'gateway' => $gateway,
            'txn_ref' => $txnRef,
            'message' => $message,
            'invoice' => $invoice->toApiArray(),
        ];
    }

    private function newTxnRef(string $prefix): string
    {
        return $prefix . now()->format('ymdHis') . Str::upper(Str::random(4));
    }

    public function normalizePkMobile(string $raw): string
    {
        $digits = preg_replace('/\D+/', '', $raw) ?? '';
        if (str_starts_with($digits, '92') && strlen($digits) === 12) {
            $digits = '0' . substr($digits, 2);
        }
        if (strlen($digits) === 10 && str_starts_with($digits, '3')) {
            $digits = '0' . $digits;
        }
        if (! preg_match('/^03\d{9}$/', $digits)) {
            throw new RuntimeException('Enter a valid Pakistani mobile number, e.g. 03001234567.');
        }

        return $digits;
    }

    public function cnicLastSix(string $raw): string
    {
        $digits = preg_replace('/\D+/', '', $raw) ?? '';
        if (strlen($digits) < 6) {
            throw new RuntimeException('Enter the last 6 digits of CNIC.');
        }

        return substr($digits, -6);
    }

    /**
     * @return array<string, mixed>
     */
    private function gatewayArray(mixed $body): array
    {
        if (is_array($body)) {
            return $body;
        }
        if (is_string($body) && $body !== '') {
            parse_str($body, $parsed);
            if (is_array($parsed) && $parsed !== []) {
                return $parsed;
            }
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private function easypaisaSucceeded(array $body): bool
    {
        $code = strtolower((string) ($body['responseCode'] ?? $body['status'] ?? $body['transactionStatus'] ?? ''));

        return in_array($code, ['0000', '000', 'success', 'paid', 'completed', '00'], true);
    }

    private function jazzcashAmountMatches(Invoice $invoice, mixed $paisa): bool
    {
        $got = (int) $paisa;
        if ($got <= 0) {
            return false;
        }

        $expectedBalance = (int) round($invoice->balance() * 100);
        $expectedTotal = (int) round((float) $invoice->total * 100);

        return $got === $expectedBalance || $got === $expectedTotal;
    }

    /**
     * @return array<string, string>
     */
    private function jazzcashRequestFields(Request $request): array
    {
        $fields = [];
        foreach ($request->all() as $key => $value) {
            if (! is_string($key) || ! is_scalar($value)) {
                continue;
            }
            if (str_starts_with($key, 'pp_') || str_starts_with($key, 'ppmpf_')) {
                $fields[$key] = (string) $value;
            }
        }

        return $fields;
    }

    private function userSafeGatewayMessage(string $gatewayMessage, string $fallback): string
    {
        $clean = trim($gatewayMessage);
        if ($clean === '' || strlen($clean) > 180) {
            return $fallback;
        }

        return $clean;
    }

    private function jazzcashPayUrl(PlatformSetting $settings): string
    {
        return $this->isJazzcashSandbox($settings)
            ? 'https://sandbox.jazzcash.com.pk/ApplicationAPI/API/2.0/Purchase/DoMWalletTransaction'
            : 'https://payments.jazzcash.com.pk/ApplicationAPI/API/2.0/Purchase/DoMWalletTransaction';
    }

    private function jazzcashInquireUrl(PlatformSetting $settings): string
    {
        return $this->isJazzcashSandbox($settings)
            ? 'https://sandbox.jazzcash.com.pk/ApplicationAPI/API/PaymentInquiry/Inquire'
            : 'https://payments.jazzcash.com.pk/ApplicationAPI/API/PaymentInquiry/Inquire';
    }

    private function easypaisaPayUrl(PlatformSetting $settings): string
    {
        return $this->isEasypaisaSandbox($settings)
            ? 'https://easypaystg.easypaisa.com.pk/easypay-service/rest/v4/initiate-ma-transaction'
            : 'https://easypay.easypaisa.com.pk/easypay-service/rest/v4/initiate-ma-transaction';
    }

    private function easypaisaInquireUrl(PlatformSetting $settings): string
    {
        return $this->isEasypaisaSandbox($settings)
            ? 'https://easypaystg.easypaisa.com.pk/easypay-service/rest/v4/inquire-transaction'
            : 'https://easypay.easypaisa.com.pk/easypay-service/rest/v4/inquire-transaction';
    }

    private function isJazzcashSandbox(PlatformSetting $settings): bool
    {
        return $settings->jazzcash_sandbox !== false;
    }

    private function isEasypaisaSandbox(PlatformSetting $settings): bool
    {
        return $settings->easypaisa_sandbox !== false;
    }
}
