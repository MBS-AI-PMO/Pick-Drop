<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SosAlert extends Model
{
    public const OPEN = 'open';
    public const ACKNOWLEDGED = 'acknowledged';
    public const RESOLVED = 'resolved';

    protected $fillable = [
        'user_id',
        'pickup_request_id',
        'lat',
        'lng',
        'message',
        'status',
        'handled_by',
        'acknowledged_at',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'float',
            'lng' => 'float',
            'acknowledged_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pickupRequest(): BelongsTo
    {
        return $this->belongsTo(PickupRequest::class);
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    /**
     * @return array<string, mixed>
     */
    public function toApiArray(): array
    {
        $this->loadMissing(['user', 'pickupRequest']);

        return [
            'id' => $this->id,
            'pickup_request_id' => $this->pickup_request_id,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'message' => $this->message,
            'status' => $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
            'acknowledged_at' => $this->acknowledged_at?->toIso8601String(),
            'resolved_at' => $this->resolved_at?->toIso8601String(),
        ];
    }
}
