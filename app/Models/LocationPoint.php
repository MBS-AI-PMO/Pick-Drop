<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocationPoint extends Model
{
    public const TYPE_PICKUP = 'pickup';
    public const TYPE_DROP = 'drop';
    public const TYPE_BOTH = 'both';

    public const TYPES = [
        self::TYPE_PICKUP => 'Pickup',
        self::TYPE_DROP => 'Drop',
        self::TYPE_BOTH => 'Pickup & Drop',
    ];

    protected $fillable = [
        'city_id',
        'area_id',
        'name',
        'type',
        'address',
        'latitude',
        'longitude',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? ucfirst((string) $this->type);
    }

    /**
     * @return array<string, mixed>
     */
    public function toApiArray(): array
    {
        $this->loadMissing(['city', 'area']);

        return [
            'id' => $this->id,
            'city_id' => $this->city_id,
            'city' => $this->city?->name,
            'area_id' => $this->area_id,
            'area' => $this->area?->name,
            'name' => $this->name,
            'type' => $this->type,
            'type_label' => $this->typeLabel(),
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'status' => $this->status,
        ];
    }
}
