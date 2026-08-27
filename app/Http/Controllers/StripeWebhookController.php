<?php

namespace App\Http\Controllers;

use App\Services\InvoiceService;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request, StripeService $stripe, InvoiceService $invoices): Response
    {
        $payload = $request->getContent();
        $signature = (string) $request->header('Stripe-Signature', '');

        try {
            $event = $stripe->parseWebhook($payload, $signature);
        } catch (Throwable $e) {
            Log::warning('Stripe webhook rejected', ['error' => $e->getMessage()]);

            return response('Invalid webhook', 400);
        }

        $type = $event['type'] ?? '';
        $object = $event['data']['object'] ?? [];

        try {
            if ($type === 'checkout.session.completed' && is_array($object)) {
                $invoices->markPaidFromStripeSession($object);
            }
        } catch (Throwable $e) {
            Log::error('Stripe webhook handling failed', [
                'type' => $type,
                'error' => $e->getMessage(),
            ]);

            return response('Webhook error', 500);
        }

        return response('ok', 200);
    }
}
