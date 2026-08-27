<?php

namespace App\Mail;

use App\Mail\Concerns\SendsAsPickDrop;
use App\Models\Invoice;
use App\Models\PaymentSetting;
use App\Services\InvoicePdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReceiptMail extends Mailable
{
    use Queueable, SerializesModels, SendsAsPickDrop;

    public function __construct(public Invoice $invoice, public PaymentSetting $settings)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->pickDropFrom(),
            subject: 'PickDrop payment receipt ' . $this->invoice->invoice_number . ' — ' . $this->invoice->formattedTotal()
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.invoice',
            with: [
                'invoice' => $this->invoice,
                'settings' => $this->settings,
                'isReceipt' => true,
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $pdf = app(InvoicePdfService::class)->output($this->invoice, $this->settings, true);

        return [
            Attachment::fromData(fn () => $pdf, 'Receipt-' . $this->invoice->invoice_number . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
