<?php

namespace App\Services;

use App\Models\DriverPayrollBill;
use App\Models\Holiday;
use App\Models\PickupRequest;
use App\Models\ShiftAttendance;
use App\Models\ShiftDayRun;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use RuntimeException;

class DriverPayrollService
{
    public function __construct(
        private readonly ShiftFareService $fareService,
        private readonly AppNotificationService $notifier,
    ) {
    }

    /**
     * @return Collection<int, array{period_start: string, period_end: string, label: string, can_request: bool, bill: DriverPayrollBill|null}>
     */
    public function eligiblePeriods(PickupRequest $request): Collection
    {
        return collect($this->periodsForRequest($request))->map(function (array $period) use ($request) {
            $existing = DriverPayrollBill::query()
                ->where('pickup_request_id', $request->id)
                ->whereDate('period_start', $period['start'])
                ->whereDate('period_end', $period['end'])
                ->first();

            $periodEnd = Carbon::parse($period['end'])->endOfDay();
            $canRequest = !$existing
                && $periodEnd->lt(now())
                && $request->isShiftPaid()
                && $request->driver_id !== null
                && !in_array($request->status, ['cancelled', 'pending'], true);

            return [
                'period_start' => $period['start'],
                'period_end' => $period['end'],
                'label' => $period['label'],
                'can_request' => $canRequest,
                'bill' => $existing,
            ];
        });
    }

    /**
     * @return list<array{start: string, end: string, label: string}>
     */
    public function periodsForRequest(PickupRequest $request): array
    {
        if (!$request->shift_start_date) {
            return [];
        }

        $shiftStart = $request->shift_start_date->copy()->startOfDay();
        $shiftEnd = ($request->shift_end_date ?? now())->copy()->endOfDay();
        $periods = [];

        $cursor = $shiftStart->copy()->startOfMonth();
        while ($cursor->lte($shiftEnd)) {
            $monthStart = $cursor->copy()->startOfMonth();
            $monthEnd = $cursor->copy()->endOfMonth();

            $periodStart = $shiftStart->gt($monthStart) ? $shiftStart->copy() : $monthStart;
            $periodEnd = $shiftEnd->lt($monthEnd) ? $shiftEnd->copy() : $monthEnd;

            if ($periodStart->lte($periodEnd)) {
                $periods[] = [
                    'start' => $periodStart->toDateString(),
                    'end' => $periodEnd->toDateString(),
                    'label' => $periodStart->format('M Y'),
                ];
            }

            $cursor->addMonth()->startOfMonth();
        }

        return $periods;
    }

    /**
     * @return array{
     *     scheduled_days: int,
     *     present_days: int,
     *     absent_days: int,
     *     skipped_days: int,
     *     holiday_days: int,
     *     monthly_rate: float,
     *     calculated_amount: float,
     *     breakdown: list<array{date: string, status: string}>
     * }
     */
    public function calculate(PickupRequest $request, string $periodStart, string $periodEnd): array
    {
        $start = Carbon::parse($periodStart)->startOfDay();
        $end = Carbon::parse($periodEnd)->endOfDay();
        $days = $request->days ?? [];

        $attendanceMap = $request->attendances()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn (ShiftAttendance $a) => $a->date->toDateString());

        $scheduled = 0;
        $present = 0;
        $absent = 0;
        $skipped = 0;
        $holiday = 0;
        $breakdown = [];

        for ($cursor = $start->copy(); $cursor->lte($end); $cursor->addDay()) {
            if (!$this->isScheduledDay($cursor, $days)) {
                continue;
            }

            $dateStr = $cursor->toDateString();
            $holidayRecord = Holiday::covers($dateStr, $request->city_id);

            if ($holidayRecord) {
                $holiday++;
                $breakdown[] = ['date' => $dateStr, 'status' => 'holiday'];
                continue;
            }

            $scheduled++;
            $record = $attendanceMap->get($dateStr);

            if ($record) {
                match ($record->status) {
                    ShiftAttendance::PRESENT => $present++,
                    ShiftAttendance::ABSENT => $absent++,
                    ShiftAttendance::SKIPPED => $skipped++,
                    ShiftAttendance::HOLIDAY => $holiday++,
                    default => $absent++,
                };
                $breakdown[] = ['date' => $dateStr, 'status' => $record->status];
            } else {
                if ($cursor->lt(now()->startOfDay())) {
                    $absent++;
                    $breakdown[] = ['date' => $dateStr, 'status' => 'absent'];
                } else {
                    $breakdown[] = ['date' => $dateStr, 'status' => 'upcoming'];
                }
            }
        }

        $monthlyRate = max(0, (float) ($request->driver_monthly_rate ?? 0));
        $payableDays = $present + $skipped + $holiday;
        $calculatedAmount = $scheduled > 0
            ? round($monthlyRate * ($payableDays / $scheduled), 2)
            : 0.0;

        return [
            'scheduled_days' => $scheduled,
            'present_days' => $present,
            'absent_days' => $absent,
            'skipped_days' => $skipped,
            'holiday_days' => $holiday,
            'monthly_rate' => $monthlyRate,
            'calculated_amount' => $calculatedAmount,
            'breakdown' => $breakdown,
        ];
    }

    public function requestPayment(PickupRequest $request, User $driver, string $periodStart, string $periodEnd): DriverPayrollBill
    {
        if ((int) $request->driver_id !== (int) $driver->id) {
            throw new RuntimeException('This shift does not belong to you.');
        }

        if (!$request->isShiftPaid()) {
            throw new RuntimeException('Payment can only be requested after the customer has paid.');
        }

        $periodEndDate = Carbon::parse($periodEnd)->endOfDay();
        if ($periodEndDate->gte(now()->startOfDay())) {
            throw new RuntimeException('This month is not complete yet. Request payment after the period ends.');
        }

        $existing = DriverPayrollBill::query()
            ->where('pickup_request_id', $request->id)
            ->whereDate('period_start', $periodStart)
            ->whereDate('period_end', $periodEnd)
            ->first();

        if ($existing) {
            if ($existing->status === DriverPayrollBill::STATUS_REJECTED) {
                throw new RuntimeException('This payment request was rejected. Contact admin.');
            }

            throw new RuntimeException('Payment for this period has already been requested.');
        }

        $request->loadMissing('attendances');
        $calc = $this->calculate($request, $periodStart, $periodEnd);

        $bill = DriverPayrollBill::query()->create([
            'bill_number' => $this->nextBillNumber(),
            'driver_id' => $driver->id,
            'pickup_request_id' => $request->id,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'scheduled_days' => $calc['scheduled_days'],
            'present_days' => $calc['present_days'],
            'absent_days' => $calc['absent_days'],
            'skipped_days' => $calc['skipped_days'],
            'holiday_days' => $calc['holiday_days'],
            'monthly_rate' => $calc['monthly_rate'],
            'calculated_amount' => $calc['calculated_amount'],
            'status' => DriverPayrollBill::STATUS_PENDING,
            'requested_at' => now(),
        ]);

        $request->loadMissing('student');
        $studentName = $request->student?->name ?: 'shift #' . $request->id;

        $this->notifier->notifyAdminPanel(
            'Driver payment request',
            sprintf(
                '%s requested payment of %s for %s (%s).',
                $driver->name,
                $bill->formattedAmount(),
                $studentName,
                $bill->periodLabel()
            ),
            'warning'
        );

        return $bill;
    }

    public function approve(DriverPayrollBill $bill, User $admin): DriverPayrollBill
    {
        if ($bill->status !== DriverPayrollBill::STATUS_PENDING) {
            throw new RuntimeException('Only pending bills can be approved.');
        }

        $bill->update([
            'status' => DriverPayrollBill::STATUS_APPROVED,
            'approved_at' => now(),
            'approved_by' => $admin->id,
        ]);

        $this->notifier->notify(
            (int) $bill->driver_id,
            'payroll_approved',
            'Payment approved',
            sprintf('Your payment request %s has been approved.', $bill->bill_number),
            ['bill_id' => $bill->id]
        );

        return $bill->fresh();
    }

    public function markPaid(DriverPayrollBill $bill, User $admin, ?string $notes = null): DriverPayrollBill
    {
        if (!in_array($bill->status, [DriverPayrollBill::STATUS_PENDING, DriverPayrollBill::STATUS_APPROVED], true)) {
            throw new RuntimeException('This bill cannot be marked as paid.');
        }

        $bill->update([
            'status' => DriverPayrollBill::STATUS_PAID,
            'paid_at' => now(),
            'paid_by' => $admin->id,
            'approved_at' => $bill->approved_at ?? now(),
            'approved_by' => $bill->approved_by ?? $admin->id,
            'notes' => $notes ?: $bill->notes,
        ]);

        $this->notifier->notify(
            (int) $bill->driver_id,
            'payroll_paid',
            'Payment sent',
            sprintf('PickDrop paid %s for %s.', $bill->formattedAmount(), $bill->periodLabel()),
            ['bill_id' => $bill->id]
        );

        return $bill->fresh();
    }

    public function reject(DriverPayrollBill $bill, User $admin, ?string $notes = null): DriverPayrollBill
    {
        if ($bill->status !== DriverPayrollBill::STATUS_PENDING) {
            throw new RuntimeException('Only pending bills can be rejected.');
        }

        $bill->update([
            'status' => DriverPayrollBill::STATUS_REJECTED,
            'notes' => $notes,
            'approved_by' => $admin->id,
        ]);

        $this->notifier->notify(
            (int) $bill->driver_id,
            'payroll_rejected',
            'Payment request rejected',
            sprintf('Your payment request %s was rejected.', $bill->bill_number),
            ['bill_id' => $bill->id]
        );

        return $bill->fresh();
    }

    /**
     * @return Collection<int, array{
     *     pickup_request_id: int,
     *     driver_name: string,
     *     student_name: string|null,
     *     period_start: string,
     *     period_end: string,
     *     label: string,
     *     preview_amount: float
     * }>
     */
    public function readyForBilling(): Collection
    {
        $requests = PickupRequest::query()
            ->with(['driver', 'student', 'attendances'])
            ->whereNotNull('driver_id')
            ->where('payment_status', PickupRequest::PAYMENT_PAID)
            ->whereNotIn('status', ['cancelled', 'pending'])
            ->get();

        $rows = collect();

        foreach ($requests as $request) {
            foreach ($this->eligiblePeriods($request) as $period) {
                if (!$period['can_request']) {
                    continue;
                }

                $calc = $this->calculate($request, $period['period_start'], $period['period_end']);

                $rows->push([
                    'driver_id' => $request->driver_id,
                    'pickup_request_id' => $request->id,
                    'driver_name' => $request->driver?->name ?? 'Driver',
                    'student_name' => $request->student?->name,
                    'period_start' => $period['period_start'],
                    'period_end' => $period['period_end'],
                    'label' => $period['label'],
                    'preview_amount' => $calc['calculated_amount'],
                ]);
            }
        }

        return $rows->sortByDesc('period_end')->values();
    }

    /**
     * @return Collection<int, array{
     *     driver_id: int,
     *     driver_name: string,
     *     driver_phone: string|null,
     *     month: string,
     *     period_start: string,
     *     period_end: string,
     *     period_label: string,
     *     shift_count: int,
     *     total_estimated: float,
     *     student_names: list<string>,
     *     shifts: Collection<int, array<string, mixed>>
     * }>
     */
    public function groupedReadyForBilling(?string $month = null): Collection
    {
        $rows = $this->readyForBilling();

        if ($month) {
            $rows = $rows->filter(function (array $row) use ($month) {
                return Carbon::parse($row['period_start'])->format('Y-m') === $month
                    || Carbon::parse($row['period_end'])->format('Y-m') === $month;
            });
        }

        $driverPhones = User::query()
            ->whereIn('id', $rows->pluck('driver_id')->unique()->filter()->all())
            ->pluck('phone', 'id');

        return $rows
            ->groupBy(function (array $row) {
                $monthKey = Carbon::parse($row['period_start'])->format('Y-m');

                return $row['driver_id'] . '|' . $monthKey;
            })
            ->map(function (Collection $shifts) use ($driverPhones) {
                $first = $shifts->first();
                $driverId = (int) $first['driver_id'];
                $monthKey = Carbon::parse($first['period_start'])->format('Y-m');
                $monthStart = Carbon::parse($monthKey . '-01')->startOfMonth();
                $monthEnd = $monthStart->copy()->endOfMonth();

                $periodStart = $shifts->min('period_start');
                $periodEnd = $shifts->max('period_end');

                $studentNames = $shifts
                    ->pluck('student_name')
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                return [
                    'driver_id' => $driverId,
                    'driver_name' => $first['driver_name'] ?? 'Driver',
                    'driver_phone' => $driverPhones[$driverId] ?? null,
                    'month' => $monthKey,
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'period_label' => $monthStart->format('M Y'),
                    'month_label' => $monthStart->format('F Y'),
                    'month_bounds_start' => $monthStart->toDateString(),
                    'month_bounds_end' => $monthEnd->toDateString(),
                    'shift_count' => $shifts->count(),
                    'total_estimated' => round((float) $shifts->sum('preview_amount'), 2),
                    'student_names' => $studentNames,
                    'shifts' => $shifts->values(),
                ];
            })
            ->values()
            ->sort(function (array $a, array $b) {
                $monthCmp = strcmp($b['month'], $a['month']);
                if ($monthCmp !== 0) {
                    return $monthCmp;
                }

                return strcasecmp($a['driver_name'], $b['driver_name']);
            })
            ->values();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function readyBillingMonths(): array
    {
        return $this->readyForBilling()
            ->map(fn (array $row) => [
                'value' => Carbon::parse($row['period_start'])->format('Y-m'),
                'label' => Carbon::parse($row['period_start'])->format('F Y'),
            ])
            ->unique('value')
            ->sortByDesc('value')
            ->values()
            ->all();
    }

    /**
     * @return array{start: string, end: string, label: string}
     */
    public function resolvePeriodBounds(string $view, ?string $month, ?int $week, ?string $date): array
    {
        $view = in_array($view, ['monthly', 'weekly', 'daily'], true) ? $view : 'monthly';

        if ($view === 'daily') {
            $day = Carbon::parse($date ?: now()->toDateString())->startOfDay();

            return [
                'start' => $day->toDateString(),
                'end' => $day->toDateString(),
                'label' => $day->format('d M Y'),
            ];
        }

        $monthStart = Carbon::parse(($month ?: now()->format('Y-m')) . '-01')->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        if ($view === 'weekly') {
            $week = max(1, min(5, (int) ($week ?: 1)));
            $start = $monthStart->copy()->addDays(($week - 1) * 7);
            $end = $start->copy()->addDays(6);
            if ($end->gt($monthEnd)) {
                $end = $monthEnd->copy();
            }
            if ($start->gt($monthEnd)) {
                $start = $monthEnd->copy();
            }

            return [
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
                'label' => 'Week ' . $week . ' · ' . $monthStart->format('M Y'),
            ];
        }

        return [
            'start' => $monthStart->toDateString(),
            'end' => $monthEnd->toDateString(),
            'label' => $monthStart->format('F Y'),
        ];
    }

    /**
     * @return array{
     *     period: array{start: string, end: string, label: string},
     *     total_estimated: float,
     *     shifts: list<array<string, mixed>>
     * }
     */
    public function driverOverview(User $driver, string $view, ?string $month, ?int $week, ?string $date): array
    {
        $period = $this->resolvePeriodBounds($view, $month, $week, $date);
        $requests = PickupRequest::query()
            ->with(['student', 'attendances'])
            ->where('driver_id', $driver->id)
            ->where('payment_status', PickupRequest::PAYMENT_PAID)
            ->whereNotIn('status', ['cancelled', 'pending'])
            ->where(function ($q) use ($period) {
                $q->whereDate('shift_start_date', '<=', $period['end'])
                    ->where(function ($inner) use ($period) {
                        $inner->whereNull('shift_end_date')
                            ->orWhereDate('shift_end_date', '>=', $period['start']);
                    });
            })
            ->get();

        $shifts = [];
        $totalEstimated = 0.0;

        foreach ($requests as $request) {
            $clipStart = max($period['start'], $request->shift_start_date?->toDateString() ?? $period['start']);
            $clipEnd = min($period['end'], $request->shift_end_date?->toDateString() ?? $period['end']);

            if ($clipStart > $clipEnd) {
                continue;
            }

            $calc = $this->calculate($request, $clipStart, $clipEnd);
            $existingBill = DriverPayrollBill::query()
                ->where('pickup_request_id', $request->id)
                ->whereDate('period_start', '<=', $clipEnd)
                ->whereDate('period_end', '>=', $clipStart)
                ->latest('id')
                ->first();

            $periodEndDate = Carbon::parse($clipEnd)->endOfDay();
            $canGenerate = false;
            if ($view === 'monthly') {
                foreach ($this->eligiblePeriods($request) as $eligible) {
                    if ($eligible['period_start'] === $clipStart
                        && $eligible['period_end'] === $clipEnd
                        && $eligible['can_request']) {
                        $canGenerate = true;
                        break;
                    }
                }
            }

            $totalEstimated += $calc['calculated_amount'];

            $shifts[] = [
                'pickup_request_id' => $request->id,
                'student_name' => $request->student?->name,
                'shift_start' => $request->shift_start_date?->format('d M Y'),
                'shift_end' => $request->shift_end_date?->format('d M Y'),
                'monthly_rate' => $request->driver_monthly_rate,
                'period_start' => $clipStart,
                'period_end' => $clipEnd,
                'estimated_amount' => $calc['calculated_amount'],
                'stats' => $calc,
                'calendar' => $this->buildCalendar($request, $clipStart, $clipEnd),
                'existing_bill' => $existingBill,
                'can_generate' => $canGenerate && !$existingBill,
            ];
        }

        return [
            'period' => $period,
            'total_estimated' => round($totalEstimated, 2),
            'shifts' => $shifts,
        ];
    }

    /**
     * @return list<array{week: int, days: list<array<string, mixed>>}>
     */
    public function buildCalendar(PickupRequest $request, string $periodStart, string $periodEnd): array
    {
        $start = Carbon::parse($periodStart)->startOfDay();
        $end = Carbon::parse($periodEnd)->endOfDay();
        $days = $request->days ?? [];

        $attendanceMap = $request->attendances()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn (ShiftAttendance $a) => $a->date->toDateString());

        $runMap = ShiftDayRun::query()
            ->where('pickup_request_id', $request->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn (ShiftDayRun $r) => $r->date->toDateString());

        $calendarStart = $start->copy()->startOfWeek(Carbon::MONDAY);
        $calendarEnd = $end->copy()->endOfWeek(Carbon::SUNDAY);

        $weeks = [];
        $weekNum = 1;
        $currentWeek = ['week' => $weekNum, 'days' => []];

        for ($cursor = $calendarStart->copy(); $cursor->lte($calendarEnd); $cursor->addDay()) {
            $dateStr = $cursor->toDateString();
            $inPeriod = $cursor->between($start, $end);
            $isScheduled = $this->isScheduledDay($cursor, $days);

            if ($inPeriod && $isScheduled) {
                $status = $this->resolveDayStatus($request, $cursor, $attendanceMap, $runMap);
            } elseif ($inPeriod) {
                $status = 'off';
            } else {
                $status = 'outside';
            }

            $currentWeek['days'][] = [
                'date' => $dateStr,
                'day' => $cursor->day,
                'weekday' => $cursor->format('D'),
                'status' => $status,
                'label' => $this->statusShortLabel($status),
                'in_period' => $inPeriod,
            ];

            if ($cursor->dayOfWeek === Carbon::SUNDAY) {
                $weeks[] = $currentWeek;
                $weekNum++;
                $currentWeek = ['week' => $weekNum, 'days' => []];
            }
        }

        if (!empty($currentWeek['days'])) {
            $weeks[] = $currentWeek;
        }

        return $weeks;
    }

    /**
     * @return list<DriverPayrollBill>
     */
    public function generateAllReadyForDriver(User $driver, User $admin): array
    {
        $generated = [];

        foreach ($this->readyForBilling() as $row) {
            if ((int) $row['driver_id'] !== (int) $driver->id) {
                continue;
            }

            $request = PickupRequest::query()->find($row['pickup_request_id']);
            if (!$request) {
                continue;
            }

            try {
                $generated[] = $this->generateBill(
                    $request,
                    $row['period_start'],
                    $row['period_end'],
                    $admin
                );
            } catch (RuntimeException) {
                continue;
            }
        }

        if ($generated === []) {
            throw new RuntimeException('No completed shifts are ready for billing.');
        }

        return $generated;
    }

    /**
     * @return list<DriverPayrollBill>
     */
    public function generateBillsForDriverMonth(User $driver, string $month, User $admin): array
    {
        $generated = [];
        $monthKey = Carbon::parse($month . '-01')->format('Y-m');

        foreach ($this->readyForBilling() as $row) {
            if ((int) $row['driver_id'] !== (int) $driver->id) {
                continue;
            }

            $rowMonth = Carbon::parse($row['period_start'])->format('Y-m');
            if ($rowMonth !== $monthKey) {
                continue;
            }

            $request = PickupRequest::query()->find($row['pickup_request_id']);
            if (!$request) {
                continue;
            }

            try {
                $generated[] = $this->generateBill(
                    $request,
                    $row['period_start'],
                    $row['period_end'],
                    $admin
                );
            } catch (RuntimeException) {
                continue;
            }
        }

        if ($generated === []) {
            throw new RuntimeException('No completed shifts are ready for billing in this month.');
        }

        return $generated;
    }

    /**
     * @return list<DriverPayrollBill>
     */
    public function generateBillsForDriver(User $driver, string $periodStart, string $periodEnd, User $admin): array
    {
        $generated = [];
        $requests = PickupRequest::query()
            ->where('driver_id', $driver->id)
            ->where('payment_status', PickupRequest::PAYMENT_PAID)
            ->whereNotIn('status', ['cancelled', 'pending'])
            ->get();

        foreach ($requests as $request) {
            foreach ($this->eligiblePeriods($request) as $period) {
                if (!$period['can_request']) {
                    continue;
                }
                if ($period['period_start'] !== $periodStart || $period['period_end'] !== $periodEnd) {
                    continue;
                }

                $generated[] = $this->generateBill(
                    $request,
                    $period['period_start'],
                    $period['period_end'],
                    $admin
                );
            }
        }

        if ($generated === []) {
            throw new RuntimeException('No completed shifts are ready for billing in this period.');
        }

        return $generated;
    }

    public function statusShortLabel(string $status): string
    {
        return match ($status) {
            'present' => 'P',
            'absent' => 'A',
            'skipped' => 'L',
            'holiday' => 'H',
            'half_day' => 'HD',
            'off' => '—',
            'upcoming' => '·',
            default => '—',
        };
    }

    public function statusDisplayLabel(string $status): string
    {
        return match ($status) {
            'present' => 'Present',
            'absent' => 'Absent',
            'skipped' => 'Leave',
            'holiday' => 'Holiday',
            'half_day' => 'Half day',
            'off' => 'Off day',
            'upcoming' => 'Upcoming',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    public function statusCalendarStyle(string $status): string
    {
        return match ($status) {
            'present' => 'background:#d1fae5;color:#065f46;border-color:#a7f3d0;',
            'absent' => 'background:#fee2e2;color:#991b1b;border-color:#fecaca;',
            'skipped' => 'background:#f3f4f6;color:#374151;border-color:#e5e7eb;',
            'holiday' => 'background:#eef4ff;color:#3f6fd9;border-color:#c7d7fe;',
            'half_day' => 'background:#fff7e6;color:#b7791f;border-color:#fde68a;',
            'off' => 'background:#fafafa;color:#9ca3af;border-color:#f3f4f6;',
            'upcoming' => 'background:#ffffff;color:#cbd5e1;border-color:#e2e8f0;',
            default => 'background:#ffffff;color:#94a3b8;border-color:#e2e8f0;',
        };
    }

    private function isFullMonthPeriod(PickupRequest $request, string $clipStart, string $clipEnd): bool
    {
        foreach ($this->periodsForRequest($request) as $period) {
            if ($period['start'] === $clipStart && $period['end'] === $clipEnd) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  \Illuminate\Support\Collection<string, ShiftAttendance>  $attendanceMap
     * @param  \Illuminate\Support\Collection<string, ShiftDayRun>  $runMap
     */
    private function resolveDayStatus(
        PickupRequest $request,
        Carbon $cursor,
        $attendanceMap,
        $runMap
    ): string {
        $dateStr = $cursor->toDateString();

        if (Holiday::covers($dateStr, $request->city_id)) {
            return 'holiday';
        }

        $record = $attendanceMap->get($dateStr);
        if ($record) {
            if ($record->status === ShiftAttendance::SKIPPED) {
                return 'skipped';
            }

            return $record->status;
        }

        $run = $runMap->get($dateStr);
        if ($run) {
            if (in_array($run->status, [ShiftDayRun::PICKED_UP, ShiftDayRun::DROPPED], true)) {
                return 'half_day';
            }
            if ($run->status === ShiftDayRun::COMPLETED) {
                return 'present';
            }
            if ($run->status === ShiftDayRun::ABSENT) {
                return 'absent';
            }
        }

        if ($cursor->lt(now()->startOfDay())) {
            return 'absent';
        }

        return 'upcoming';
    }

    public function generateBill(PickupRequest $request, string $periodStart, string $periodEnd, User $admin): DriverPayrollBill
    {
        if (!$request->driver_id) {
            throw new RuntimeException('No driver assigned to this shift.');
        }

        if (!$request->isShiftPaid()) {
            throw new RuntimeException('Customer payment must be confirmed before generating a driver bill.');
        }

        $periodEndDate = Carbon::parse($periodEnd)->endOfDay();
        if ($periodEndDate->gte(now()->startOfDay())) {
            throw new RuntimeException('This month is not complete yet.');
        }

        $existing = DriverPayrollBill::query()
            ->where('pickup_request_id', $request->id)
            ->whereDate('period_start', $periodStart)
            ->whereDate('period_end', $periodEnd)
            ->first();

        if ($existing && $existing->status !== DriverPayrollBill::STATUS_REJECTED) {
            throw new RuntimeException('A bill already exists for this period.');
        }

        if ($existing && $existing->status === DriverPayrollBill::STATUS_REJECTED) {
            $existing->delete();
        }

        $request->loadMissing('attendances');
        $calc = $this->calculate($request, $periodStart, $periodEnd);
        $driver = $request->driver;

        $bill = DriverPayrollBill::query()->create([
            'bill_number' => $this->nextBillNumber(),
            'driver_id' => $request->driver_id,
            'pickup_request_id' => $request->id,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'scheduled_days' => $calc['scheduled_days'],
            'present_days' => $calc['present_days'],
            'absent_days' => $calc['absent_days'],
            'skipped_days' => $calc['skipped_days'],
            'holiday_days' => $calc['holiday_days'],
            'monthly_rate' => $calc['monthly_rate'],
            'calculated_amount' => $calc['calculated_amount'],
            'status' => DriverPayrollBill::STATUS_PENDING,
            'requested_at' => now(),
            'notes' => 'Generated by admin: ' . $admin->name,
        ]);

        if ($driver) {
            $this->notifier->notify(
                (int) $driver->id,
                'payroll_bill_generated',
                'Payment bill ready',
                sprintf('Your bill %s for %s is ready for review.', $bill->bill_number, $bill->periodLabel()),
                ['bill_id' => $bill->id]
            );
        }

        return $bill;
    }

    private function nextBillNumber(): string
    {
        $year = now()->format('Y');
        $last = DriverPayrollBill::query()
            ->where('bill_number', 'like', "DPB-{$year}-%")
            ->orderByDesc('id')
            ->value('bill_number');

        $seq = 1;
        if ($last && preg_match('/DPB-\d{4}-(\d+)/', $last, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return sprintf('DPB-%s-%04d', $year, $seq);
    }

    /**
     * @param  list<string>  $days
     */
    private function isScheduledDay(Carbon $date, array $days): bool
    {
        $map = [
            'monday' => Carbon::MONDAY, 'mon' => Carbon::MONDAY,
            'tuesday' => Carbon::TUESDAY, 'tue' => Carbon::TUESDAY,
            'wednesday' => Carbon::WEDNESDAY, 'wed' => Carbon::WEDNESDAY,
            'thursday' => Carbon::THURSDAY, 'thu' => Carbon::THURSDAY,
            'friday' => Carbon::FRIDAY, 'fri' => Carbon::FRIDAY,
            'saturday' => Carbon::SATURDAY, 'sat' => Carbon::SATURDAY,
            'sunday' => Carbon::SUNDAY, 'sun' => Carbon::SUNDAY,
        ];

        $selected = [];
        foreach ($days as $day) {
            $key = strtolower(trim((string) $day));
            if (isset($map[$key])) {
                $selected[$map[$key]] = true;
            }
        }

        return isset($selected[$date->dayOfWeek]);
    }
}
