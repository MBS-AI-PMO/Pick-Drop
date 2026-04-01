<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickDropCharge extends Model
{
    use HasFactory;

    protected $fillable = [
        'per_km_rate',
        'currency',
        'is_active',
    ];
}

