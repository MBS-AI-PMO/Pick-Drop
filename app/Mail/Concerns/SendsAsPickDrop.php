<?php

namespace App\Mail\Concerns;

use Illuminate\Mail\Mailables\Address;

trait SendsAsPickDrop
{
    protected function pickDropFrom(): Address
    {
        return new Address((string) config('mail.from.address'), 'PickDrop');
    }
}
