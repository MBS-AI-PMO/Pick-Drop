<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickDropCharge extends Model
{
    use HasFactory;

    protected $fillable = [
        'per_km_rate',
        'driver_monthly_rate',
        'currency',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'per_km_rate' => 'float',
            'driver_monthly_rate' => 'float',
            'is_active' => 'boolean',
        ];
    }
}

