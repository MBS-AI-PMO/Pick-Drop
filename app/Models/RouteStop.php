<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RouteStop extends Model
{
    use HasFactory;

    protected $fillable = [
        'route_id',
        'name',
        'arrival_time',
        'order',
        'created_by',
    ];

    protected $casts = [
        'arrival_time' => 'datetime:H:i',
    ];

    public function route()
    {
        return $this->belongsTo(SchoolRoute::class, 'route_id');
    }
}

