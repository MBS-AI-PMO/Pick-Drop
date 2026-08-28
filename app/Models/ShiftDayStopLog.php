<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ShiftDayStopLog extends Model
{
    protected $fillable = [
        'shift_day_run_id',
        'pickup_request_stop_id',
        'status',
        'photo_path',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }

    public function dayRun(): BelongsTo
    {
        return $this->belongsTo(ShiftDayRun::class, 'shift_day_run_id');
    }

    public function stop(): BelongsTo
    {
        return $this->belongsTo(PickupRequestStop::class, 'pickup_request_stop_id');
    }

    public function photoUrl(): ?string
    {
        return $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null;
    }
}
