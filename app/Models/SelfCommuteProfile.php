<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SelfCommuteProfile extends Model
{
    protected $fillable = [
        'user_id',
        'city_id',
        'pickup_area_id',
        'pickup_point',
        'pickup_lat',
        'pickup_lng',
        'office_name',
        'drop_area_id',
        'drop_point',
        'drop_lat',
        'drop_lng',
        'pickup_time',
        'drop_time',
        'days',
    ];

    protected function casts(): array
    {
        return [
            'pickup_lat' => 'float',
            'pickup_lng' => 'float',
            'drop_lat' => 'float',
            'drop_lng' => 'float',
            'days' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function pickupArea(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'pickup_area_id');
    }

    public function dropArea(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'drop_area_id');
    }

    private function formatTime(mixed $value): ?string
    {
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
        $this->loadMissing(['city', 'pickupArea', 'dropArea']);

        return [
            'id' => $this->id,
            'city_id' => $this->city_id,
            'city' => $this->city,
            'pickup_area_id' => $this->pickup_area_id,
            'pickup_area' => $this->pickupArea,
            'pickup_point' => $this->pickup_point,
            'pickup_lat' => $this->pickup_lat,
            'pickup_lng' => $this->pickup_lng,
            'office_name' => $this->office_name,
            'drop_area_id' => $this->drop_area_id,
            'drop_area' => $this->dropArea,
            'drop_point' => $this->drop_point,
            'drop_lat' => $this->drop_lat,
            'drop_lng' => $this->drop_lng,
            'pickup_time' => $this->formatTime($this->pickup_time),
            'drop_time' => $this->formatTime($this->drop_time),
            'office_timing' => [
                'start' => $this->formatTime($this->pickup_time),
                'end' => $this->formatTime($this->drop_time),
            ],
            'days' => $this->days ?? [],
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
