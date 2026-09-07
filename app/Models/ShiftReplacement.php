<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftReplacement extends Model
{
    public const OPEN = 'open';
    public const ACCEPTED = 'accepted';
    public const CLOSED = 'closed';

    public const REASON_BREAKDOWN = 'breakdown';
    public const REASON_UNAVAILABLE = 'unavailable';
    public const REASON_ABSENT = 'absent';

    protected $fillable = [
        'pickup_request_id',
        'date',
        'original_driver_id',
        'replacement_driver_id',
        'original_vehicle_id',
        'replacement_vehicle_id',
        'reason',
        'status',
        'notes',
        'accepted_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'accepted_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function pickupRequest(): BelongsTo
    {
        return $this->belongsTo(PickupRequest::class);
    }

    public function originalDriver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'original_driver_id');
    }

    public function replacementDriver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replacement_driver_id');
    }

    public function isOpen(): bool
    {
        return $this->status === self::OPEN;
    }

    /**
     * @return array<string, mixed>
     */
    public function toApiArray(): array
    {
        $this->loadMissing(['pickupRequest', 'originalDriver', 'replacementDriver']);

        return [
            'id' => $this->id,
            'pickup_request_id' => $this->pickup_request_id,
            'date' => $this->date?->toDateString(),
            'reason' => $this->reason,
            'status' => $this->status,
            'notes' => $this->notes,
            'original_driver' => [
                'id' => $this->original_driver_id,
                'name' => $this->originalDriver?->name,
            ],
            'replacement_driver' => $this->replacement_driver_id ? [
                'id' => $this->replacement_driver_id,
                'name' => $this->replacementDriver?->name,
            ] : null,
            'request' => $this->pickupRequest?->toApiArray('driver'),
            'accepted_at' => $this->accepted_at?->toIso8601String(),
        ];
    }
}
