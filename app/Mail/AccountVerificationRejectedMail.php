<?php

namespace App\Mail;

use App\Models\User;
use App\Mail\Concerns\SendsAsPickDrop;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountVerificationRejectedMail extends Mailable
{
    use Queueable, SerializesModels, SendsAsPickDrop;

    public function __construct(public User $user, public string $reason)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->pickDropFrom(),
            subject: 'PickDrop identity verification update'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.account-verification-rejected',
            with: [
                'userName' => $this->user->name,
                'reason' => $this->reason,
            ],
        );
    }
}
