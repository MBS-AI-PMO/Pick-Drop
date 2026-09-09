<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickupRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'service_type',
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
        'round_trip',
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
        'cancelled_by',
        'cancelled_by_role',
        'completed_at',
        'match_expires_at',
        'auto_assign_attempts',
        'assignment_source',
        'auto_renew',
        'renewal_status',
        'renewed_from_id',
        'last_delay_notified_on',
        'renewal_notified_at',
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
        'match_expires_at' => 'datetime',
        'auto_renew' => 'boolean',
        'round_trip' => 'boolean',
        'last_delay_notified_on' => 'date',
        'renewal_notified_at' => 'datetime',
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

    public function attendances()
    {
        return $this->hasMany(ShiftAttendance::class);
    }

    public function payrollBills()
    {
        return $this->hasMany(DriverPayrollBill::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function issues()
    {
        return $this->hasMany(IssueReport::class);
    }

    public function sosAlerts()
    {
        return $this->hasMany(SosAlert::class);
    }

    public function replacements()
    {
        return $this->hasMany(ShiftReplacement::class);
    }

    public function payrolls()
    {
        return $this->hasMany(DriverPayrollItem::class);
    }

    /**
     * Outbound + return legs so the driver always brings the passenger back.
     *
     * @return list<array<string, mixed>>
     */
    public function journeyApiArray(): array
    {
        $this->loadMissing('stops.area');
        $stops = $this->stops;

        if ($stops->isEmpty()) {
            $stops = collect();
        }

        $rows = $stops->values()->map(function (PickupRequestStop $stop, int $index) {
            $payload = $stop->toApiArray();
            $payload['leg'] = $stop->leg ?: 'outbound';
            $payload['action'] = $stop->isPickup()
                ? 'Pick up from ' . $stop->point
                : 'Drop at ' . $stop->point;
            $payload['virtual'] = false;

            return $payload;
        })->all();

        $roundTrip = $this->round_trip !== false && $this->resolvedServiceType() === self::SERVICE_BOTH;
        // Virtual return legs only for classic 1 pickup + 1 drop. Multi-stop / one-sided journeys use stored stops as-is.
        if ($roundTrip && count($rows) === 2) {
            $pickup = $rows[0];
            $drop = $rows[1];
            $returnPickup = $drop;
            $returnPickup['type'] = PickupRequestStop::TYPE_PICKUP;
            $returnPickup['leg'] = 'return';
            $returnPickup['sequence'] = 3;
            $returnPickup['name'] = 'Return pickup';
            $returnPickup['action'] = 'Pick up from ' . ($drop['point'] ?? $this->drop_point);
            $returnPickup['virtual'] = true;
            $returnDrop = $pickup;
            $returnDrop['type'] = PickupRequestStop::TYPE_DROP;
            $returnDrop['leg'] = 'return';
            $returnDrop['sequence'] = 4;
            $returnDrop['name'] = 'Return drop';
            $returnDrop['action'] = 'Drop back at ' . ($pickup['point'] ?? $this->pickup_point);
            $returnDrop['virtual'] = true;
            $rows[0]['leg'] = 'outbound';
            $rows[1]['leg'] = 'outbound';
            $rows[] = $returnPickup;
            $rows[] = $returnDrop;
        }

        return [
            'round_trip' => $roundTrip,
            'rule' => 'Passenger is dropped back at the same place they were picked up.',
            'from' => [
                'point' => $this->pickup_point,
                'lat' => $this->pickup_lat,
                'lng' => $this->pickup_lng,
                'time' => $this->formatTime($this->pickup_time),
            ],
            'to' => [
                'point' => $this->drop_point,
                'lat' => $this->drop_lat,
                'lng' => $this->drop_lng,
                'time' => $this->formatTime($this->drop_time),
            ],
            'stops' => $rows,
        ];
    }

    public function renewedFrom()
    {
        return $this->belongsTo(self::class, 'renewed_from_id');
    }

    public const PAYMENT_UNPAID = 'unpaid';
    public const PAYMENT_PENDING = 'pending_confirmation';
    public const PAYMENT_PAID = 'paid';

    public const SERVICE_BOTH = 'both';
    public const SERVICE_PICKUP_ONLY = 'pickup_only';
    public const SERVICE_DROP_ONLY = 'drop_only';

    public function resolvedServiceType(): string
    {
        $stored = strtolower(trim((string) ($this->service_type ?: '')));
        if (in_array($stored, [self::SERVICE_BOTH, self::SERVICE_PICKUP_ONLY, self::SERVICE_DROP_ONLY], true)) {
            return $stored;
        }

        return $this->serviceTypeFromStops();
    }

    public function serviceTypeFromStops(): string
    {
        $this->loadMissing('stops');
        $hasPickup = $this->stops->contains(fn (PickupRequestStop $stop) => $stop->isPickup());
        $hasDrop = $this->stops->contains(fn (PickupRequestStop $stop) => $stop->type === PickupRequestStop::TYPE_DROP);

        if ($hasPickup && $hasDrop) {
            return self::SERVICE_BOTH;
        }
        if ($hasPickup) {
            return self::SERVICE_PICKUP_ONLY;
        }
        if ($hasDrop) {
            return self::SERVICE_DROP_ONLY;
        }

        if (filled($this->pickup_point) && filled($this->drop_point)) {
            return self::SERVICE_BOTH;
        }
        if (filled($this->pickup_point)) {
            return self::SERVICE_PICKUP_ONLY;
        }
        if (filled($this->drop_point)) {
            return self::SERVICE_DROP_ONLY;
        }

        return self::SERVICE_BOTH;
    }

    public function isPickupOnly(): bool
    {
        return $this->resolvedServiceType() === self::SERVICE_PICKUP_ONLY;
    }

    public function isDropOnly(): bool
    {
        return $this->resolvedServiceType() === self::SERVICE_DROP_ONLY;
    }

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
            default => $this->driver_id ? 'Payment pending' : 'Unpaid',
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
            'status_label' => $this->paymentStatusLabel(),
            'payment_pending' => $required,
            'can_start_trip' => $this->isShiftPaid(),
            'message' => $required
                ? 'Payment is pending. The shift will start only after payment is completed.'
                : ($this->isShiftPaid() ? 'Payment received. Shift can start.' : null),
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

    /**
     * @return array<string, mixed>
     */
    public function trackingApiArray(): array
    {
        $this->loadMissing(['driver.driverVerification', 'vehicle']);
        $driver = $this->driver;
        $verification = $driver?->driverVerification;

        return [
            'status' => $this->status,
            'driver_id' => $this->driver_id,
            'vehicle_id' => $this->vehicle_id,
            'driver_status' => $driver?->last_ride_status,
            'driver_name' => $driver?->name,
            'driver_phone' => $driver?->phone,
            'driver_photo' => $verification?->documentUrl($verification->selfie_photo),
            'vehicle_plate' => $this->vehicle?->license_plate,
            'vehicle_name' => $this->vehicle?->name,
            'lat' => $driver?->last_lat !== null ? (float) $driver->last_lat : null,
            'lng' => $driver?->last_lng !== null ? (float) $driver->last_lng : null,
            'updated_at' => $driver?->last_location_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function todayRunApiArray(bool $includeOtp = false): ?array
    {
        $run = $this->relationLoaded('dayRuns')
            ? $this->dayRuns->firstWhere('date', now()->toDateString())
            : ShiftDayRun::query()
                ->where('pickup_request_id', $this->id)
                ->whereDate('date', now()->toDateString())
                ->first();

        return $run?->toApiArray($includeOtp);
    }

    public function dayRuns()
    {
        return $this->hasMany(ShiftDayRun::class);
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
            'service_type' => $this->resolvedServiceType(),
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
            'journey' => $this->journeyApiArray(),
            'round_trip' => $this->round_trip !== false,
            'days' => $this->days ?? [],
            'duration_months' => (int) ($this->duration_months ?: 1),
            'shift_start_date' => $this->shift_start_date?->toDateString(),
            'shift_end_date' => $this->shift_end_date?->toDateString(),
            'distance_km' => $this->distance_km,
            'trip_count' => $this->trip_count,
            'estimated_amount' => $this->estimated_amount,
            'payment_status' => $this->payment_status ?: self::PAYMENT_UNPAID,
            'payment' => $this->paymentApiArray($audience),
            'tracking' => $this->trackingApiArray(),
            'today' => $this->todayRunApiArray($audience === 'user'),
            'passenger_count' => (int) ($this->passenger_count ?: 1),
            'auto_renew' => (bool) $this->auto_renew,
            'renewal_status' => $this->renewal_status ?: 'none',
            'match_expires_at' => $this->match_expires_at?->toIso8601String(),
            'assignment_source' => $this->assignment_source,
            'scheduled_date' => $this->scheduled_date?->toDateString(),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'cancelled_by' => $this->cancelled_by,
            'cancelled_by_role' => $this->cancelled_by_role,
            'completed_at' => $this->completed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

