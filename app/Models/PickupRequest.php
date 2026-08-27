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
        'drop_area_id',
        'pickup_point',
        'pickup_lat',
        'pickup_lng',
        'drop_point',
        'drop_lat',
        'drop_lng',
        'pickup_time',
        'drop_time',
        'days',
        'duration_months',
        'shift_start_date',
        'shift_end_date',
        'distance_km',
        'trip_count',
        'estimated_amount',
        'driver_monthly_rate',
        'driver_payout_amount',
        'driver_payout_status',
        'driver_payout_due_on',
        'driver_payout_paid_at',
        'payment_status',
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
        'distance_km' => 'float',
        'estimated_amount' => 'float',
        'driver_monthly_rate' => 'float',
        'driver_payout_amount' => 'float',
        'scheduled_date' => 'date',
        'shift_start_date' => 'date',
        'shift_end_date' => 'date',
        'driver_payout_due_on' => 'date',
        'cancelled_at' => 'datetime',
        'completed_at' => 'datetime',
        'driver_payout_paid_at' => 'datetime',
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

    public function dropArea()
    {
        return $this->belongsTo(Area::class, 'drop_area_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driverRejections()
    {
        return $this->hasMany(DriverPickupRequestRejection::class);
    }

    public function stops()
    {
        return $this->hasMany(PickupRequestStop::class)->orderBy('sequence')->orderBy('scheduled_time');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function latestInvoice()
    {
        return $this->hasOne(Invoice::class)->latestOfMany();
    }

    public const PAYMENT_UNPAID = 'unpaid';
    public const PAYMENT_PENDING = 'pending_confirmation';
    public const PAYMENT_PAID = 'paid';

    public function isShiftPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_PAID;
    }

    public function needsPayment(): bool
    {
        return $this->driver_id !== null
            && $this->status !== 'cancelled'
            && $this->payment_status !== self::PAYMENT_PAID;
    }

    public function paymentStatusLabel(): string
    {
        return match ($this->payment_status) {
            self::PAYMENT_PAID => 'Paid',
            self::PAYMENT_PENDING => 'Awaiting confirmation',
            default => 'Unpaid',
        };
    }

    public function paymentStatusBadgeStyle(): string
    {
        return match ($this->payment_status) {
            self::PAYMENT_PAID => 'background:#d1fae5;color:#065f46;',
            self::PAYMENT_PENDING => 'background:#fef9c3;color:#92400e;',
            default => 'background:#fee2e2;color:#991b1b;',
        };
    }

    public const DRIVER_PAYOUT_UNPAID = 'unpaid';
    public const DRIVER_PAYOUT_PAID = 'paid';

    /**
     * @return array<string, mixed>
     */
    public function paymentApiArray(string $audience = 'user'): array
    {
        $this->loadMissing('latestInvoice');
        $settings = PaymentSetting::current();
        $invoice = $this->latestInvoice;
        $required = $this->needsPayment();
        $months = (int) ($this->duration_months ?: 1);

        $payload = [
            'model' => 'monthly_advance',
            'required' => $required,
            'status' => $this->payment_status ?: self::PAYMENT_UNPAID,
            'can_start_trip' => $this->isShiftPaid(),
            'duration_months' => $months,
            'min_months' => 1,
            'shift_start_date' => $this->shift_start_date?->toDateString(),
            'shift_end_date' => $this->shift_end_date?->toDateString(),
            'distance_km' => $this->distance_km,
            'trip_count' => $this->trip_count,
            'amount' => $this->estimated_amount,
            'formatted_amount' => $invoice
                ? $invoice->formattedTotal()
                : (($this->estimated_amount !== null)
                    ? 'PKR ' . number_format((float) $this->estimated_amount, 2)
                    : null),
            'invoice' => $invoice?->toApiArray(),
            'methods' => ($audience === 'user' && $required) ? [
                'stripe_enabled' => false,
                'bank' => $settings->bankDetails(),
            ] : null,
            'next_step' => $required
                ? ($this->payment_status === self::PAYMENT_PENDING ? 'await_payment_confirmation' : 'pay_invoice')
                : null,
        ];

        if ($audience === 'driver') {
            $payload['methods'] = null;
            $payload['invoice'] = $invoice ? [
                'id' => $invoice->id,
                'status' => $invoice->status,
                'formatted_total' => $invoice->formattedTotal(),
            ] : null;
            $payload['driver_payout'] = $this->driverPayoutApiArray();
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    public function driverPayoutApiArray(): array
    {
        return [
            'paid_by' => 'company',
            'schedule' => 'month_end',
            'monthly_rate' => $this->driver_monthly_rate,
            'months' => (int) ($this->duration_months ?: 1),
            'total' => $this->driver_payout_amount,
            'due_on' => $this->driver_payout_due_on?->toDateString(),
            'status' => $this->driver_payout_status ?: self::DRIVER_PAYOUT_UNPAID,
            'paid_at' => $this->driver_payout_paid_at?->toIso8601String(),
        ];
    }

    public function requesterName(): string
    {
        $this->loadMissing('parent');

        return $this->parent?->name ?? 'Unknown';
    }

    public function typeLabel(): string
    {
        return strcasecmp((string) $this->type, 'self') === 0 ? 'Self' : 'Parent';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'accepted' => 'Accepted',
            'picked_up' => 'Picked Up',
            'dropped' => 'Dropped',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst(str_replace('_', ' ', (string) $this->status)),
        };
    }

    public function statusBadgeStyle(): string
    {
        return match ($this->status) {
            'pending' => 'background:#fef9c3;color:#92400e;',
            'accepted' => 'background:#dbeafe;color:#1e40af;',
            'picked_up' => 'background:#e0f2fe;color:#075985;',
            'dropped' => 'background:#eef4ff;color:#3f6fd9;',
            'completed' => 'background:#d1fae5;color:#065f46;',
            'cancelled' => 'background:#fee2e2;color:#991b1b;',
            default => 'background:#f3f4f6;color:#374151;',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return [
            'pending' => 'Pending',
            'accepted' => 'Accepted',
            'picked_up' => 'Picked Up',
            'dropped' => 'Dropped',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];
    }

    private function formatTime(mixed $value): ?string
    {
        if (!$value) {
            return null;
        }

        return substr((string) $value, 0, 5);
    }

    /**
     * @return array<string, mixed>
     */
    public function toApiArray(string $audience = 'user'): array
    {
        $this->loadMissing(['city', 'area', 'dropArea', 'student', 'driver', 'vehicle', 'stops.area']);

        return [
            'id' => $this->id,
            'type' => $this->type,
            'status' => $this->status,
            'parent_id' => $this->parent_id,
            'parent' => $this->relationLoaded('parent') ? $this->parent : null,
            'student_id' => $this->student_id,
            'student' => $this->student,
            'driver_id' => $this->driver_id,
            'driver' => $this->driver,
            'vehicle_id' => $this->vehicle_id,
            'vehicle' => $this->vehicle,
            'city_id' => $this->city_id,
            'city' => $this->city,
            'area_id' => $this->area_id,
            'area' => $this->area,
            'drop_area_id' => $this->drop_area_id,
            'drop_area' => $this->dropArea,
            'pickup_point' => $this->pickup_point,
            'pickup_lat' => $this->pickup_lat,
            'pickup_lng' => $this->pickup_lng,
            'drop_point' => $this->drop_point,
            'drop_lat' => $this->drop_lat,
            'drop_lng' => $this->drop_lng,
            'pickup_time' => $this->formatTime($this->pickup_time),
            'drop_time' => $this->formatTime($this->drop_time),
            'pickup' => [
                'point' => $this->pickup_point,
                'lat' => $this->pickup_lat,
                'lng' => $this->pickup_lng,
                'time' => $this->formatTime($this->pickup_time),
                'area_id' => $this->area_id,
                'area' => $this->area,
            ],
            'drop' => [
                'point' => $this->drop_point,
                'lat' => $this->drop_lat,
                'lng' => $this->drop_lng,
                'time' => $this->formatTime($this->drop_time),
                'area_id' => $this->drop_area_id,
                'area' => $this->dropArea,
            ],
            'stops' => $this->stops->map(fn (PickupRequestStop $stop) => $stop->toApiArray())->values()->all(),
            'days' => $this->days ?? [],
            'duration_months' => (int) ($this->duration_months ?: 1),
            'shift_start_date' => $this->shift_start_date?->toDateString(),
            'shift_end_date' => $this->shift_end_date?->toDateString(),
            'distance_km' => $this->distance_km,
            'trip_count' => $this->trip_count,
            'estimated_amount' => $this->estimated_amount,
            'payment_status' => $this->payment_status ?: self::PAYMENT_UNPAID,
            'payment' => $this->paymentApiArray($audience),
            'scheduled_date' => $this->scheduled_date?->toDateString(),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

