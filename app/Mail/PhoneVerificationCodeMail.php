<?php

namespace App\Mail;

use App\Mail\Concerns\SendsAsPickDrop;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PhoneVerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels, SendsAsPickDrop;

    public function __construct(
        public string $code,
        public string $userName,
        public string $phone
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->pickDropFrom(),
            subject: 'Verify Your Phone Number'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.phone-verification-code',
            with: [
                'code' => $this->code,
                'userName' => $this->userName,
                'phone' => $this->phone,
            ],
        );
    }
}
