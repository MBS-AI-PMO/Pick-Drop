<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolRoute extends Model
{
    use HasFactory;

    protected $table = 'routes';

    protected $fillable = [
        'city_id',
        'area_id',
        'area_ids',
        'code',
        'name',
        'shift',
        'vehicle_id',
        'start_time',
        'end_time',
        'destination',
        'destination_latitude',
        'destination_longitude',
        'description',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time'   => 'datetime:H:i',
        'area_ids'   => 'array',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function stops()
    {
        return $this->hasMany(RouteStop::class, 'route_id')->orderBy('order');
    }
}

