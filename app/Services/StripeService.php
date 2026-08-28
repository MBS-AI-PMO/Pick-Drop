<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\PaymentSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class StripeService
{
    public function createCheckoutSession(Invoice $invoice, string $successUrl, string $cancelUrl): array
    {
        $settings = PaymentSetting::current();
        if (!$settings->hasStripe()) {
            throw new RuntimeException('Stripe is not configured.');
        }

        $currency = strtolower($settings->stripe_currency ?: $invoice->currency);
        $lineItems = [];

        foreach ($invoice->items as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => [
                        'name' => $item->description,
                    ],
                    'unit_amount' => $this->toStripeAmount((float) $item->unit_price, $currency),
                ],
                'quantity' => max(1, (int) round((float) $item->quantity)),
            ];
        }

        if ($invoice->tax_amount > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => [
                        'name' => 'Tax (' . rtrim(rtrim(number_format($invoice->tax_percent, 2), '0'), '.') . '%)',
                    ],
                    'unit_amount' => $this->toStripeAmount((float) $invoice->tax_amount, $currency),
                ],
                'quantity' => 1,
            ];
        }

        $payload = [
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'customer_email' => $invoice->customer?->email,
            'client_reference_id' => (string) $invoice->id,
            'metadata' => [
                'invoice_id' => (string) $invoice->id,
                'invoice_number' => $invoice->invoice_number,
            ],
        ];

        foreach ($lineItems as $i => $item) {
            $payload['line_items[' . $i . '][quantity]'] = $item['quantity'];
            $payload['line_items[' . $i . '][price_data][currency]'] = $item['price_data']['currency'];
            $payload['line_items[' . $i . '][price_data][unit_amount]'] = $item['price_data']['unit_amount'];
            $payload['line_items[' . $i . '][price_data][product_data][name]'] = $item['price_data']['product_data']['name'];
        }

        $session = $this->request('post', 'checkout/sessions', $payload, (string) $settings->stripeSecret());

        $invoice->update([
            'stripe_checkout_session_id' => $session['id'] ?? null,
            'stripe_payment_intent_id' => is_string($session['payment_intent'] ?? null)
                ? $session['payment_intent']
                : ($session['payment_intent']['id'] ?? $invoice->stripe_payment_intent_id),
        ]);

        return $session;
    }

    public function retrieveSession(string $sessionId): array
    {
        $settings = PaymentSetting::current();

        return $this->request('get', 'checkout/sessions/' . $sessionId, [
            'expand[]' => 'payment_intent',
        ], (string) $settings->stripeSecret());
    }

    public function parseWebhook(string $payload, string $signatureHeader): array
    {
        $settings = PaymentSetting::current();
        $secrets = $settings->stripeWebhookSecrets();
        if ($secrets === []) {
            throw new RuntimeException('Stripe webhook secret is not configured.');
        }

        $lastError = 'Invalid Stripe signature.';
        foreach ($secrets as $secret) {
            try {
                $this->verifySignature($payload, $signatureHeader, $secret);
                $event = json_decode($payload, true);
                if (!is_array($event)) {
                    throw new RuntimeException('Invalid Stripe webhook payload.');
                }

                return $event;
            } catch (RuntimeException $e) {
                $lastError = $e->getMessage();
            }
        }

        throw new RuntimeException($lastError);
    }

    private function verifySignature(string $payload, string $header, string $secret): void
    {
        $timestamp = null;
        $signatures = [];

        foreach (explode(',', $header) as $part) {
            [$k, $v] = array_pad(explode('=', trim($part), 2), 2, null);
            if ($k === 't') {
                $timestamp = $v;
            }
            if ($k === 'v1' && $v) {
                $signatures[] = $v;
            }
        }

        if (!$timestamp || $signatures === []) {
            throw new RuntimeException('Missing Stripe signature.');
        }

        if (abs(time() - (int) $timestamp) > 300) {
            throw new RuntimeException('Stripe webhook timestamp too old.');
        }

        $expected = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);
        foreach ($signatures as $signature) {
            if (hash_equals($expected, $signature)) {
                return;
            }
        }

        throw new RuntimeException('Invalid Stripe signature.');
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    private function request(string $method, string $path, array $params, string $secret): array
    {
        $http = Http::withBasicAuth($secret, '')
            ->asForm()
            ->acceptJson()
            ->timeout(30);

        $response = strtolower($method) === 'get'
            ? $http->get('https://api.stripe.com/v1/' . ltrim($path, '/'), $params)
            : $http->post('https://api.stripe.com/v1/' . ltrim($path, '/'), $params);

        $json = $response->json();
        if (!$response->successful()) {
            $message = is_array($json) ? ($json['error']['message'] ?? 'Stripe request failed.') : 'Stripe request failed.';
            Log::warning('Stripe API error', ['path' => $path, 'status' => $response->status()]);
            throw new RuntimeException($message);
        }

        return is_array($json) ? $json : [];
    }

    private function toStripeAmount(float $amount, string $currency): int
    {
        $zeroDecimal = ['bif', 'clp', 'djf', 'gnf', 'jpy', 'kmf', 'krw', 'mga', 'pyg', 'rwf', 'ugx', 'vnd', 'vuv', 'xaf', 'xof', 'xpf'];

        if (in_array(strtolower($currency), $zeroDecimal, true)) {
            return (int) round($amount);
        }

        return (int) round($amount * 100);
    }
}
