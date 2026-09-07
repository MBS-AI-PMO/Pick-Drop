<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftAttendance extends Model
{
    public const PRESENT = 'present';
    public const SKIPPED = 'skipped';
    public const HOLIDAY = 'holiday';
    public const ABSENT = 'absent';
    public const LEAVE = 'leave';

    protected $fillable = [
        'pickup_request_id',
        'date',
        'status',
        'reason',
        'refunded_amount',
        'refunded_at',
        'refund_reason',
        'marked_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'refunded_amount' => 'float',
            'refunded_at' => 'datetime',
        ];
    }

    public function pickupRequest(): BelongsTo
    {
        return $this->belongsTo(PickupRequest::class);
    }

    public function marker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    public function isOffDay(): bool
    {
        return in_array($this->status, [self::SKIPPED, self::HOLIDAY], true);
    }

    /**
     * @return array<string, mixed>
     */
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'pickup_request_id' => $this->pickup_request_id,
            'date' => $this->date?->toDateString(),
            'status' => $this->status,
            'reason' => $this->reason,
            'refunded_amount' => $this->refunded_amount !== null ? (float) $this->refunded_amount : null,
            'refunded_at' => $this->refunded_at?->toIso8601String(),
            'refund_reason' => $this->refund_reason,
        ];
    }
}
