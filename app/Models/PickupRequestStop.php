<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PickupRequestStop extends Model
{
    public const TYPE_PICKUP = 'pickup';
    public const TYPE_DROP = 'drop';

    public const STATUS_PENDING = 'pending';
    public const STATUS_DONE = 'done';
    public const STATUS_SKIPPED = 'skipped';

    protected $fillable = [
        'pickup_request_id',
        'type',
        'leg',
        'sequence',
        'name',
        'point',
        'lat',
        'lng',
        'area_id',
        'scheduled_time',
        'status',
        'completed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'float',
            'lng' => 'float',
            'completed_at' => 'datetime',
        ];
    }

    public function pickupRequest(): BelongsTo
    {
        return $this->belongsTo(PickupRequest::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function isPickup(): bool
    {
        return $this->type === self::TYPE_PICKUP;
    }

    public function isDrop(): bool
    {
        return $this->type === self::TYPE_DROP;
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function formattedTime(): ?string
    {
        $value = $this->scheduled_time;
        if (!$value) {
            return null;
        }

        return substr((string) $value, 0, 5);
    }

    /**
     * @return array<string, mixed>
     */
    public function toApiArray(): array
    {
        $this->loadMissing(['area', 'pickupRequest.student', 'pickupRequest.parent']);
        $request = $this->pickupRequest;

        return [
            'id' => $this->id,
            'pickup_request_id' => $this->pickup_request_id,
            'type' => $this->type,
            'leg' => $this->leg ?: 'outbound',
            'sequence' => (int) $this->sequence,
            'name' => $this->name ?: ($this->isPickup() ? 'Pickup' : 'Drop'),
            'point' => $this->point,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'area_id' => $this->area_id,
            'area' => $this->area,
            'time' => $this->formattedTime(),
            'status' => $this->getAttribute('today_status') ?: $this->status,
            'action' => $this->isPickup()
                ? 'Pick up from ' . $this->point
                : 'Drop at ' . $this->point,
            'completed_at' => optional($this->getAttribute('today_completed_at'))?->toIso8601String()
                ?: $this->completed_at?->toIso8601String(),
            'pickup_otp_required' => $this->isPickup(),
            'notes' => $this->notes,
            'passenger' => [
                'name' => $request?->student?->name ?: $request?->parent?->name,
                'type' => $request?->type,
                'student_id' => $request?->student_id,
                'parent_id' => $request?->parent_id,
            ],
        ];
    }
}
