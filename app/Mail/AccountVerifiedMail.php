<?php

namespace App\Mail;

use App\Models\User;
use App\Mail\Concerns\SendsAsPickDrop;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountVerifiedMail extends Mailable
{
    use Queueable, SerializesModels, SendsAsPickDrop;

    public function __construct(public User $user)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->pickDropFrom(),
            subject: 'Your PickDrop account is verified'
        );
    }

    public function content(): Content
    {
        $isSelf = strcasecmp((string) $this->user->role, 'self') === 0;

        return new Content(
            view: 'mail.account-verified',
            with: [
                'userName' => $this->user->name,
                'isSelf' => $isSelf,
            ],
        );
    }
}
