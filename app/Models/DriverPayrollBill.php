<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverPayrollBill extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PAID = 'paid';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'bill_number',
        'driver_id',
        'pickup_request_id',
        'period_start',
        'period_end',
        'scheduled_days',
        'present_days',
        'absent_days',
        'skipped_days',
        'holiday_days',
        'monthly_rate',
        'calculated_amount',
        'status',
        'notes',
        'requested_at',
        'approved_at',
        'paid_at',
        'approved_by',
        'paid_by',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'monthly_rate' => 'float',
            'calculated_amount' => 'float',
            'requested_at' => 'datetime',
            'approved_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function pickupRequest(): BelongsTo
    {
        return $this->belongsTo(PickupRequest::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Pending review',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_PAID => 'Paid',
            self::STATUS_REJECTED => 'Rejected',
            default => ucfirst($this->status),
        };
    }

    public function statusBadgeStyle(): string
    {
        return match ($this->status) {
            self::STATUS_PAID => 'background:#d1fae5;color:#065f46;',
            self::STATUS_APPROVED => 'background:#eef4ff;color:#3f6fd9;',
            self::STATUS_REJECTED => 'background:#fee2e2;color:#991b1b;',
            default => 'background:#fef9c3;color:#92400e;',
        };
    }

    public function formattedAmount(): string
    {
        return 'PKR ' . number_format((float) $this->calculated_amount, 2);
    }

    public function periodLabel(): string
    {
        return $this->period_start->format('d M Y') . ' – ' . $this->period_end->format('d M Y');
    }

    public function attendanceSummary(): string
    {
        return sprintf(
            '%d present / %d scheduled (%d absent, %d skipped, %d holiday)',
            $this->present_days,
            $this->scheduled_days,
            $this->absent_days,
            $this->skipped_days,
            $this->holiday_days
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toApiArray(): array
    {
        $this->loadMissing(['pickupRequest.student']);

        return [
            'id' => $this->id,
            'bill_number' => $this->bill_number,
            'pickup_request_id' => $this->pickup_request_id,
            'student_name' => $this->pickupRequest?->student?->name,
            'period_start' => $this->period_start->toDateString(),
            'period_end' => $this->period_end->toDateString(),
            'period_label' => $this->periodLabel(),
            'scheduled_days' => $this->scheduled_days,
            'present_days' => $this->present_days,
            'absent_days' => $this->absent_days,
            'skipped_days' => $this->skipped_days,
            'holiday_days' => $this->holiday_days,
            'monthly_rate' => $this->monthly_rate,
            'calculated_amount' => $this->calculated_amount,
            'formatted_amount' => $this->formattedAmount(),
            'status' => $this->status,
            'status_label' => $this->statusLabel(),
            'requested_at' => $this->requested_at?->toIso8601String(),
            'approved_at' => $this->approved_at?->toIso8601String(),
            'paid_at' => $this->paid_at?->toIso8601String(),
            'notes' => $this->notes,
        ];
    }
}
