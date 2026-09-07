<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginLog extends Model
{
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';
    public const STATUS_DENIED = 'denied';

    public const CHANNEL_WEB = 'web';
    public const CHANNEL_DRIVER_API = 'driver-api';
    public const CHANNEL_PARENT_API = 'parent-api';

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'role',
        'channel',
        'status',
        'ip_address',
        'user_agent',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function channelLabel(): string
    {
        return match ($this->channel) {
            self::CHANNEL_DRIVER_API => 'Driver app',
            self::CHANNEL_PARENT_API => 'Parent / Self app',
            default => 'Admin panel',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_FAILED => 'Failed',
            self::STATUS_DENIED => 'Denied',
            default => 'Success',
        };
    }
}
