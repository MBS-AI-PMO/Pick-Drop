<?php

namespace App\Mail;

use App\Mail\Concerns\SendsAsPickDrop;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Refund confirmation sent to the customer, in the same style as the invoice / receipt emails.
 * The admin's internal refund reason is deliberately not included.
 */
class RefundMail extends Mailable
{
    use Queueable, SerializesModels, SendsAsPickDrop;

    public function __construct(
        public Invoice $invoice,
        public Payment $payment,
        public PaymentSetting $settings
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->pickDropFrom(),
            subject: 'PickDrop refund confirmation ' . $this->invoice->invoice_number
                . ' — ' . $this->invoice->formatMoney((float) $this->payment->amount)
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.refund',
            with: [
                'invoice' => $this->invoice,
                'payment' => $this->payment,
                'settings' => $this->settings,
            ],
        );
    }
}
