<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\LocalPaymentService;
use Illuminate\Http\Request;

class LocalPaymentCallbackController extends Controller
{
    public function jazzcash(Request $request, LocalPaymentService $payments)
    {
        $invoice = $payments->handleJazzcashCallback($request);

        return $this->result($invoice, 'JazzCash');
    }

    public function easypaisa(Request $request, LocalPaymentService $payments)
    {
        $invoice = $payments->handleEasypaisaCallback($request);

        return $this->result($invoice, 'EasyPaisa');
    }

    private function result(?Invoice $invoice, string $gateway)
    {
        $paid = $invoice?->isPaid();

        if ($requestWantsJson = request()->expectsJson() || request()->is('api/*')) {
            return response()->json([
                'success' => (bool) $paid,
                'gateway' => $gateway,
                'invoice' => $invoice?->toApiArray(),
            ]);
        }

        return view('pickdrop.payments.stripe-complete', [
            'invoice' => $invoice,
            'message' => $paid
                ? $gateway . ' payment received.'
                : $gateway . ' callback received. If paid, the invoice will update shortly.',
        ]);
    }
}
