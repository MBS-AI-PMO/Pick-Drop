<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PickupRequest;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;

class LocalPaymentService
{
    public function __construct(private readonly InvoiceService $invoices)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function jazzcashCheckout(Invoice $invoice): array
    {
        $settings = PlatformSetting::current();
        if (!$settings->jazzcash_enabled || !filled($settings->jazzcash_merchant_id)) {
            throw new RuntimeException('JazzCash is not configured.');
        }

        if (!$invoice->isPayable()) {
            throw new RuntimeException('This invoice is not payable.');
        }

        $txnRef = 'JC' . $invoice->id . Str::upper(Str::random(8));
        $datetime = now()->format('YmdHis');
        $expiry = now()->addHours(2)->format('YmdHis');
        $amount = (int) round($invoice->balance() * 100);

        $fields = [
            'pp_Version' => '1.1',
            'pp_TxnType' => 'MWALLET',
            'pp_Language' => 'EN',
            'pp_MerchantID' => $settings->jazzcash_merchant_id,
            'pp_Password' => $settings->jazzcash_password,
            'pp_TxnRefNo' => $txnRef,
            'pp_Amount' => (string) $amount,
            'pp_TxnCurrency' => 'PKR',
            'pp_TxnDateTime' => $datetime,
            'pp_BillReference' => $invoice->invoice_number,
            'pp_Description' => 'Invoice ' . $invoice->invoice_number,
            'pp_TxnExpiryDateTime' => $expiry,
            'pp_ReturnURL' => $settings->jazzcash_return_url ?: url('/payments/jazzcash/callback'),
            'ppmpf_1' => (string) $invoice->id,
        ];

        $fields['pp_SecureHash'] = $this->jazzcashHash($fields, (string) $settings->jazzcash_integrity_salt);
        $invoice->update(['gateway_txn_ref' => $txnRef]);

        return [
            'gateway' => 'jazzcash',
            'checkout_url' => 'https://sandbox.jazzcash.com.pk/CustomerPortal/transactionmanagement/merchantform/',
            'txn_ref' => $txnRef,
            'fields' => $fields,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function easypaisaCheckout(Invoice $invoice): array
    {
        $settings = PlatformSetting::current();
        if (!$settings->easypaisa_enabled || !filled($settings->easypaisa_store_id)) {
            throw new RuntimeException('EasyPaisa is not configured.');
        }

        if (!$invoice->isPayable()) {
            throw new RuntimeException('This invoice is not payable.');
        }

        $txnRef = 'EP' . $invoice->id . Str::upper(Str::random(8));
        $amount = number_format($invoice->balance(), 2, '.', '');
        $payload = [
            'storeId' => $settings->easypaisa_store_id,
            'orderId' => $txnRef,
            'transactionAmount' => $amount,
            'mobileAccountNo' => '',
            'emailAddress' => $invoice->customer?->email,
            'tokenExpiry' => now()->addHours(2)->toIso8601String(),
        ];
        $payload['hash'] = hash_hmac('sha256', implode('&', $payload), (string) $settings->easypaisa_hash_key);
        $invoice->update(['gateway_txn_ref' => $txnRef]);

        return [
            'gateway' => 'easypaisa',
            'checkout_url' => $settings->easypaisa_return_url ?: 'https://easypay.easypaisa.com.pk/easypay/Index.jsf',
            'txn_ref' => $txnRef,
            'fields' => $payload,
        ];
    }

    public function handleJazzcashCallback(Request $request): ?Invoice
    {
        $invoiceId = (int) $request->input('ppmpf_1');
        $invoice = Invoice::query()->find($invoiceId);
        if (!$invoice || $invoice->isPaid()) {
            return $invoice;
        }

        $code = (string) $request->input('pp_ResponseCode');
        if ($code !== '000') {
            return $invoice;
        }

        $this->invoices->recordPayment(
            $invoice,
            'jazzcash',
            $invoice->balance(),
            Payment::STATUS_COMPLETED,
            ['reference' => $request->input('pp_TxnRefNo') ?: $invoice->gateway_txn_ref]
        );

        return $invoice->fresh();
    }

    public function handleEasypaisaCallback(Request $request): ?Invoice
    {
        $txn = (string) $request->input('orderId', $request->input('transactionRef'));
        $invoice = Invoice::query()->where('gateway_txn_ref', $txn)->first();
        if (!$invoice || $invoice->isPaid()) {
            return $invoice;
        }

        $ok = in_array(strtolower((string) $request->input('status', $request->input('transactionStatus'))), ['success', 'paid', '000', 'completed'], true);
        if (!$ok) {
            return $invoice;
        }

        $this->invoices->recordPayment(
            $invoice,
            'easypaisa',
            $invoice->balance(),
            Payment::STATUS_COMPLETED,
            ['reference' => $txn]
        );

        return $invoice->fresh();
    }

    /**
     * @param  array<string, string>  $fields
     */
    private function jazzcashHash(array $fields, string $salt): string
    {
        ksort($fields);
        $parts = [$salt];
        foreach ($fields as $value) {
            if ($value !== '' && $value !== null) {
                $parts[] = $value;
            }
        }

        return strtoupper(hash_hmac('sha256', implode('&', $parts), $salt));
    }
}
