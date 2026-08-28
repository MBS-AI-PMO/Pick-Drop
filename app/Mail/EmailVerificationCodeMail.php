<?php

namespace App\Mail;

use App\Mail\Concerns\SendsAsPickDrop;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailVerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels, SendsAsPickDrop;

    public string $code;
    public string $userName;

    public function __construct(string $code, string $userName)
    {
        $this->code = $code;
        $this->userName = $userName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->pickDropFrom(),
            subject: 'Verify Your Email Address'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.email-verification-code',
            with: [
                'code' => $this->code,
                'userName' => $this->userName,
            ],
        );
    }
}