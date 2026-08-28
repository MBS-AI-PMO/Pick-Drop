<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class ShiftDayRun extends Model
{
    public const SCHEDULED = 'scheduled';
    public const SKIPPED = 'skipped';
    public const PICKED_UP = 'picked_up';
    public const DROPPED = 'dropped';
    public const COMPLETED = 'completed';
    public const ABSENT = 'absent';

    protected $fillable = [
        'pickup_request_id',
        'date',
        'status',
        'pickup_otp',
        'pickup_photo_path',
        'pickup_verified_at',
        'arrival_notified_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'pickup_verified_at' => 'datetime',
            'arrival_notified_at' => 'datetime',
        ];
    }

    public function pickupRequest(): BelongsTo
    {
        return $this->belongsTo(PickupRequest::class);
    }

    public function stopLogs(): HasMany
    {
        return $this->hasMany(ShiftDayStopLog::class);
    }

    public function photoUrl(): ?string
    {
        return $this->pickup_photo_path
            ? Storage::disk('public')->url($this->pickup_photo_path)
            : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toApiArray(bool $includeOtp = false): array
    {
        return [
            'id' => $this->id,
            'pickup_request_id' => $this->pickup_request_id,
            'date' => $this->date?->toDateString(),
            'status' => $this->status,
            'pickup_otp' => $includeOtp ? $this->pickup_otp : null,
            'pickup_photo_url' => $this->photoUrl(),
            'pickup_verified_at' => $this->pickup_verified_at?->toIso8601String(),
        ];
    }
}
