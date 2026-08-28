<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
    public const STRIPE_MODE_TEST = 'test';
    public const STRIPE_MODE_LIVE = 'live';

    protected $fillable = [
        'company_name',
        'company_email',
        'company_phone',
        'company_address',
        'invoice_prefix',
        'tax_percent',
        'bank_enabled',
        'bank_name',
        'bank_account_title',
        'bank_account_number',
        'bank_iban',
        'bank_swift',
        'bank_branch',
        'stripe_enabled',
        'stripe_mode',
        'stripe_publishable_key',
        'stripe_secret_key',
        'stripe_webhook_secret',
        'stripe_test_publishable_key',
        'stripe_test_secret_key',
        'stripe_test_webhook_secret',
        'stripe_live_publishable_key',
        'stripe_live_secret_key',
        'stripe_live_webhook_secret',
        'stripe_currency',
    ];

    protected function casts(): array
    {
        return [
            'tax_percent' => 'float',
            'bank_enabled' => 'boolean',
            'stripe_enabled' => 'boolean',
            'stripe_secret_key' => 'encrypted',
            'stripe_webhook_secret' => 'encrypted',
            'stripe_test_secret_key' => 'encrypted',
            'stripe_test_webhook_secret' => 'encrypted',
            'stripe_live_secret_key' => 'encrypted',
            'stripe_live_webhook_secret' => 'encrypted',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate(
            ['id' => 1],
            [
                'company_name' => 'PickDrop',
                'invoice_prefix' => 'INV',
                'tax_percent' => 0,
                'bank_enabled' => true,
                'stripe_enabled' => false,
                'stripe_mode' => self::STRIPE_MODE_TEST,
                'stripe_currency' => 'pkr',
            ]
        );
    }

    public function isStripeLive(): bool
    {
        return strtolower((string) $this->stripe_mode) === self::STRIPE_MODE_LIVE;
    }

    public function hasBankDetails(): bool
    {
        return $this->bank_enabled
            && filled($this->bank_name)
            && filled($this->bank_account_title)
            && filled($this->bank_account_number);
    }

    public function stripePublishableKey(): ?string
    {
        $fromMode = $this->isStripeLive()
            ? $this->stripe_live_publishable_key
            : $this->stripe_test_publishable_key;

        return filled($fromMode) ? $fromMode : ($this->stripe_publishable_key ?: config('services.stripe.key'));
    }

    public function stripeSecret(): ?string
    {
        $fromMode = $this->isStripeLive()
            ? $this->stripe_live_secret_key
            : $this->stripe_test_secret_key;

        if (filled($fromMode)) {
            return $fromMode;
        }

        return filled($this->stripe_secret_key)
            ? $this->stripe_secret_key
            : config('services.stripe.secret');
    }

    public function stripeWebhookSecret(): ?string
    {
        $fromMode = $this->isStripeLive()
            ? $this->stripe_live_webhook_secret
            : $this->stripe_test_webhook_secret;

        if (filled($fromMode)) {
            return $fromMode;
        }

        return filled($this->stripe_webhook_secret)
            ? $this->stripe_webhook_secret
            : config('services.stripe.webhook_secret');
    }

    /**
     * @return list<string>
     */
    public function stripeWebhookSecrets(): array
    {
        $candidates = [];

        try {
            $candidates[] = $this->stripeWebhookSecret();
        } catch (\Throwable) {
        }

        foreach ([
            'stripe_test_webhook_secret',
            'stripe_live_webhook_secret',
            'stripe_webhook_secret',
        ] as $field) {
            try {
                $candidates[] = $this->{$field};
            } catch (\Throwable) {
            }
        }

        $candidates[] = config('services.stripe.webhook_secret');

        return array_values(array_unique(array_filter($candidates)));
    }

    public function hasSavedSecret(string $mode): bool
    {
        try {
            return $mode === self::STRIPE_MODE_LIVE
                ? filled($this->stripe_live_secret_key)
                : filled($this->stripe_test_secret_key);
        } catch (\Throwable) {
            return false;
        }
    }

    public function hasSavedWebhookSecret(string $mode): bool
    {
        try {
            return $mode === self::STRIPE_MODE_LIVE
                ? filled($this->stripe_live_webhook_secret)
                : filled($this->stripe_test_webhook_secret);
        } catch (\Throwable) {
            return false;
        }
    }

    public function hasStripe(): bool
    {
        return $this->stripe_enabled && filled($this->stripeSecret());
    }

    /**
     * @return array<string, mixed>
     */
    public function bankDetails(): array
    {
        return [
            'enabled' => $this->hasBankDetails(),
            'bank_name' => $this->bank_name,
            'account_title' => $this->bank_account_title,
            'account_number' => $this->bank_account_number,
            'iban' => $this->bank_iban,
            'swift' => $this->bank_swift,
            'branch' => $this->bank_branch,
        ];
    }
}
