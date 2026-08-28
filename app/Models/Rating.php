<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rating extends Model
{
    protected $fillable = [
        'pickup_request_id',
        'from_user_id',
        'to_user_id',
        'rating',
        'comment',
    ];

    public function pickupRequest(): BelongsTo
    {
        return $this->belongsTo(PickupRequest::class);
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    /**
     * @return array<string, mixed>
     */
    public function toApiArray(): array
    {
        $this->loadMissing(['fromUser', 'toUser']);

        return [
            'id' => $this->id,
            'pickup_request_id' => $this->pickup_request_id,
            'rating' => (int) $this->rating,
            'comment' => $this->comment,
            'from_user_id' => $this->from_user_id,
            'to_user_id' => $this->to_user_id,
            'from' => $this->fromUser?->name,
            'to' => $this->toUser?->name,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
