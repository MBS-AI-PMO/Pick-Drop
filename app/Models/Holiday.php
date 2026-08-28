<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Holiday extends Model
{
    protected $fillable = [
        'date',
        'name',
        'type',
        'city_id',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public static function covers(Carbon|string $date, ?int $cityId = null): ?self
    {
        $day = $date instanceof Carbon ? $date->toDateString() : $date;

        return static::query()
            ->whereDate('date', $day)
            ->where(function ($q) use ($cityId) {
                $q->whereNull('city_id');
                if ($cityId) {
                    $q->orWhere('city_id', $cityId);
                }
            })
            ->orderByRaw('city_id is null')
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    public function toApiArray(): array
    {
        $this->loadMissing('city');

        return [
            'id' => $this->id,
            'date' => $this->date?->toDateString(),
            'name' => $this->name,
            'type' => $this->type,
            'city_id' => $this->city_id,
            'city' => $this->city?->name,
            'applies_to' => $this->city_id ? ($this->city?->name ?? 'City') : 'All cities',
        ];
    }
}
