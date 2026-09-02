<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverPayrollItem extends Model
{
    protected $fillable = [
        'driver_payroll_id',
        'pickup_request_id',
        'scheduled_days',
        'worked_days',
        'leave_days',
        'absent_days',
        'holiday_days',
        'parent_skip_days',
        'upcoming_days',
        'daily_rate',
        'gross',
        'deduction',
        'net',
        'expected_net',
        'deduction_note',
    ];

    protected function casts(): array
    {
        return [
            'daily_rate' => 'float',
            'gross' => 'float',
            'deduction' => 'float',
            'net' => 'float',
            'expected_net' => 'float',
        ];
    }

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(DriverPayroll::class, 'driver_payroll_id');
    }

    public function pickupRequest(): BelongsTo
    {
        return $this->belongsTo(PickupRequest::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function toApiArray(): array
    {
        $this->loadMissing('pickupRequest');

        return [
            'id' => $this->id,
            'pickup_request_id' => $this->pickup_request_id,
            'passenger' => $this->pickupRequest?->requesterName(),
            'scheduled_days' => (int) $this->scheduled_days,
            'worked_days' => (int) $this->worked_days,
            'leave_days' => (int) $this->leave_days,
            'absent_days' => (int) $this->absent_days,
            'holiday_days' => (int) $this->holiday_days,
            'parent_skip_days' => (int) $this->parent_skip_days,
            'upcoming_days' => (int) $this->upcoming_days,
            'daily_rate' => (float) $this->daily_rate,
            'gross' => (float) $this->gross,
            'deduction' => (float) $this->deduction,
            'net' => (float) $this->net,
            'expected_net' => (float) $this->expected_net,
            'deduction_note' => $this->deduction_note,
        ];
    }
}
