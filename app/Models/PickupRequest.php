<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickupRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'parent_id',
        'student_id',
        'city_id',
        'area_id',
        'pickup_point',
        'pickup_lat',
        'pickup_lng',
        'drop_point',
        'drop_lat',
        'drop_lng',
        'pickup_time',
        'drop_time',
        'days',
        'status',
        'driver_id',
        'vehicle_id',
        'scheduled_date',
        'cancelled_at',
        'completed_at',
    ];

    protected $casts = [
        'days' => 'array',
        'pickup_lat' => 'float',
        'pickup_lng' => 'float',
        'drop_lat' => 'float',
        'drop_lng' => 'float',
        'scheduled_date' => 'date',
        'cancelled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}

