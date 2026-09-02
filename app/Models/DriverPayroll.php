<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DriverPayroll extends Model
{
    public const DRAFT = 'draft';
    public const APPROVED = 'approved';
    public const PAID = 'paid';

    protected $fillable = [
        'driver_id',
        'month',
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
        'status',
        'approved_at',
        'paid_at',
        'processed_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'daily_rate' => 'float',
            'gross' => 'float',
            'deduction' => 'float',
            'net' => 'float',
            'expected_net' => 'float',
            'approved_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DriverPayrollItem::class);
    }

    public function isLocked(): bool
    {
        return $this->status === self::PAID;
    }

    public function monthEnded(): bool
    {
        try {
            return now()->gte(\Illuminate\Support\Carbon::createFromFormat('Y-m', $this->month)->endOfMonth()->addDay()->startOfDay());
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function phase(): string
    {
        if ($this->status === self::PAID) {
            return 'paid';
        }
        if ($this->status === self::APPROVED) {
            return 'approved';
        }

        return $this->monthEnded() ? 'ready' : 'running';
    }

    public function phaseLabel(): string
    {
        return match ($this->phase()) {
            'paid' => 'Paid',
            'approved' => 'Approved — pay now',
            'ready' => 'Month ended — check & pay',
            default => 'Month still running',
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function toApiArray(): array
    {
        $this->loadMissing(['driver', 'items.pickupRequest']);

        return [
            'id' => $this->id,
            'driver_id' => $this->driver_id,
            'driver_name' => $this->driver?->name,
            'month' => $this->month,
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
            'status' => $this->status,
            'approved_at' => $this->approved_at?->toIso8601String(),
            'paid_at' => $this->paid_at?->toIso8601String(),
            'items' => $this->items->map->toApiArray()->values()->all(),
        ];
    }
}
