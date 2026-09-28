<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model
{
    protected $fillable = [
        'sms_enabled',
        'sms_provider',
        'sms_api_url',
        'sms_api_key',
        'sms_sender',
        'fcm_enabled',
        'fcm_server_key',
        'jazzcash_enabled',
        'jazzcash_merchant_id',
        'jazzcash_password',
        'jazzcash_integrity_salt',
        'jazzcash_return_url',
        'jazzcash_sandbox',
        'easypaisa_enabled',
        'easypaisa_store_id',
        'easypaisa_hash_key',
        'easypaisa_return_url',
        'easypaisa_sandbox',
        'cancel_hours',
        'cancel_fee_percent',
        'geofence_meters',
        'referral_bonus',
        'pickup_otp_enabled',
    ];

    protected function casts(): array
    {
        return [
            'sms_enabled' => 'boolean',
            'fcm_enabled' => 'boolean',
            'jazzcash_enabled' => 'boolean',
            'jazzcash_sandbox' => 'boolean',
            'easypaisa_enabled' => 'boolean',
            'easypaisa_sandbox' => 'boolean',
            'pickup_otp_enabled' => 'boolean',
            'sms_api_key' => 'encrypted',
            'fcm_server_key' => 'encrypted',
            'jazzcash_password' => 'encrypted',
            'jazzcash_integrity_salt' => 'encrypted',
            'easypaisa_hash_key' => 'encrypted',
            'cancel_fee_percent' => 'float',
            'referral_bonus' => 'float',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate(
            ['id' => 1],
            [
                'cancel_hours' => 24,
                'cancel_fee_percent' => 0,
                'geofence_meters' => 300,
                'referral_bonus' => 0,
                'pickup_otp_enabled' => true,
            ]
        );
    }

    public function jazzcashReady(): bool
    {
        return (bool) $this->jazzcash_enabled
            && filled($this->jazzcash_merchant_id)
            && filled($this->jazzcash_password)
            && filled($this->jazzcash_integrity_salt);
    }

    public function easypaisaReady(): bool
    {
        return (bool) $this->easypaisa_enabled
            && filled($this->easypaisa_store_id)
            && filled($this->easypaisa_hash_key);
    }
}
