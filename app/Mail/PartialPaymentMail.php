<?php

namespace App\Mail;

use App\Mail\Concerns\SendsAsPickDrop;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentSetting;
use App\Services\InvoicePdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent automatically when a payment is received but the invoice is not yet fully paid:
 * tells the customer how much was paid and how much is still remaining.
 */
class PartialPaymentMail extends Mailable
{
    use Queueable, SerializesModels, SendsAsPickDrop;

    public function __construct(
        public Invoice $invoice,
        public Payment $payment,
        public PaymentSetting $settings
    ) {
        $this->invoice->loadMissing(['items', 'customer', 'student', 'payments']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->pickDropFrom(),
            subject: 'PickDrop payment received ' . $this->invoice->invoice_number
                . ' — ' . $this->invoice->formatMoney((float) $this->payment->amount)
                . ' paid, ' . $this->invoice->formatMoney($this->invoice->balance()) . ' remaining'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.partial-payment',
            with: [
                'invoice' => $this->invoice,
                'payment' => $this->payment,
                'settings' => $this->settings,
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $pdf = app(InvoicePdfService::class);

        return [
            Attachment::fromData(
                fn () => $pdf->output($this->invoice, $this->settings, false),
                'Invoice-' . $this->invoice->invoice_number . '.pdf'
            )->withMime('application/pdf'),
        ];
    }
}
