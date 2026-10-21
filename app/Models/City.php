<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'status',
    ];

    public function areas()
    {
        return $this->hasMany(Area::class);
    }

    public function activeAreas()
    {
        return $this->areas()->where('status', 'Active')->orderBy('name');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    /**
     * City dropdown: each city with its active areas.
     */
    public static function dropdownWithAreas()
    {
        return static::query()
            ->active()
            ->with(['areas' => function ($q) {
                $q->active()
                    ->orderBy('name')
                    ->select('id', 'city_id', 'name', 'latitude', 'longitude', 'status');
            }])
            ->orderBy('name')
            ->get(['id', 'name', 'latitude', 'longitude', 'status']);
    }
}

