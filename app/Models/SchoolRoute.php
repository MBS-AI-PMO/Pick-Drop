<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolRoute extends Model
{
    use HasFactory;

    protected $table = 'routes';

    protected $fillable = [
        'code',
        'name',
        'shift',
        'vehicle_id',
        'start_time',
        'end_time',
        'destination',
        'description',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time'   => 'datetime:H:i',
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

