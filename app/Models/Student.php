<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'name',
        'grade',
        'school_name',
        'school_location',
        'city_id',
        'pickup_area_id',
        'pickup_location',
        'pickup_lat',
        'pickup_lng',
        'pickup_time',
        'dropoff_time',
        'status',
    ];

    protected $casts = [
        'pickup_lat' => 'float',
        'pickup_lng' => 'float',
    ];

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function pickupArea()
    {
        return $this->belongsTo(Area::class, 'pickup_area_id');
    }
}

