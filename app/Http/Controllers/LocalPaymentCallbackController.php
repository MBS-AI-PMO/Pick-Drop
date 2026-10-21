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
        $paid = (bool) $invoice?->isPaid();

        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json([
                'success' => $paid,
                'gateway' => $gateway,
                'invoice' => $invoice?->toApiArray(),
            ], $invoice ? 200 : 400);
        }

        return view('pickdrop.payments.stripe-complete', [
            'invoice' => $invoice,
            'success' => $paid,
            'message' => $paid
                ? $gateway . ' payment confirmed.'
                : $gateway . ' did not confirm this payment. If you paid, wait a moment and open the invoice again.',
        ]);
    }
}
