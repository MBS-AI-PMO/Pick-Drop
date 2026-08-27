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

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels, SendsAsPickDrop;

    public function __construct(public Invoice $invoice, public PaymentSetting $settings)
    {
        $this->invoice->loadMissing(['items', 'customer', 'student', 'payments']);
    }

    public function envelope(): Envelope
    {
        $number = $this->invoice->invoice_number . ' — ' . $this->invoice->formattedTotal();

        if ($this->invoice->isPaid()) {
            $subject = 'PickDrop payment received ' . $number;
        } elseif ($this->invoice->hasPendingBankTransfer()) {
            $subject = 'PickDrop bank payment received ' . $number;
        } else {
            $subject = 'PickDrop invoice ' . $number;
        }

        return new Envelope(
            from: $this->pickDropFrom(),
            subject: $subject
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.invoice',
            with: [
                'invoice' => $this->invoice,
                'settings' => $this->settings,
                'isReceipt' => $this->invoice->isPaid(),
                'pendingBank' => $this->invoice->hasPendingBankTransfer(),
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $pdf = app(InvoicePdfService::class);
        $number = $this->invoice->invoice_number;
        $files = [
            Attachment::fromData(
                fn () => $pdf->output($this->invoice, $this->settings, false),
                'Invoice-' . $number . '.pdf'
            )->withMime('application/pdf'),
        ];

        if ($this->invoice->isPaid()) {
            $files[] = Attachment::fromData(
                fn () => $pdf->output($this->invoice, $this->settings, true),
                'Receipt-' . $number . '.pdf'
            )->withMime('application/pdf');
        }

        return $files;
    }
}
