<?php

namespace App\Services;

use App\Models\DriverPayroll;
use App\Models\Holiday;
use App\Models\PickupRequest;
use App\Models\ShiftAttendance;
use App\Models\ShiftReplacement;
use App\Models\User;
use Illuminate\Support\Carbon;

class DriverPayrollService
{
    public function __construct(private readonly ShiftFareService $fare)
    {
    }

    public function generate(string $month, ?int $driverId = null): int
    {
        $cursor = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $monthStart = $cursor->copy()->startOfMonth();
        $monthEnd = $cursor->copy()->endOfMonth();

        $query = PickupRequest::query()
            ->with(['attendances', 'replacements'])
            ->whereNotNull('driver_id')
            ->whereNotIn('status', ['cancelled', 'pending'])
            ->where('payment_status', PickupRequest::PAYMENT_PAID)
            ->where(function ($range) use ($monthStart, $monthEnd) {
                $range->whereNull('shift_start_date')->orWhereDate('shift_start_date', '<=', $monthEnd);
            })
            ->where(function ($range) use ($monthStart) {
                $range->whereNull('shift_end_date')->orWhereDate('shift_end_date', '>=', $monthStart);
            });

        if ($driverId) {
            $query->where('driver_id', $driverId);
        }

        $grouped = $query->get()->groupBy('driver_id');
        $count = 0;

        foreach ($grouped as $id => $shifts) {
            $this->buildForDriver((int) $id, $month, $monthStart, $monthEnd, $shifts);
            $count++;
        }

        return $count;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, PickupRequest>  $shifts
     */
    private function buildForDriver(int $driverId, string $month, Carbon $monthStart, Carbon $monthEnd, $shifts): DriverPayroll
    {
        $existing = DriverPayroll::query()
            ->where('driver_id', $driverId)
            ->where('month', $month)
            ->first();

        if ($existing?->isLocked()) {
            return $existing;
        }

        $payroll = $existing ?: new DriverPayroll([
            'driver_id' => $driverId,
            'month' => $month,
            'status' => DriverPayroll::DRAFT,
        ]);

        $payroll->save();
        $payroll->items()->delete();

        $totals = [
            'scheduled_days' => 0,
            'worked_days' => 0,
            'leave_days' => 0,
            'absent_days' => 0,
            'holiday_days' => 0,
            'parent_skip_days' => 0,
            'upcoming_days' => 0,
            'gross' => 0.0,
            'deduction' => 0.0,
            'net' => 0.0,
            'expected_net' => 0.0,
        ];
        $dailyRates = [];

        foreach ($shifts as $shift) {
            $item = $this->itemForShift($shift, $monthStart, $monthEnd);
            $payroll->items()->create($item);
            foreach (['scheduled_days', 'worked_days', 'leave_days', 'absent_days', 'holiday_days', 'parent_skip_days', 'upcoming_days'] as $key) {
                $totals[$key] += $item[$key];
            }
            $totals['gross'] += $item['gross'];
            $totals['deduction'] += $item['deduction'];
            $totals['net'] += $item['net'];
            $totals['expected_net'] += $item['expected_net'];
            $dailyRates[] = $item['daily_rate'];
        }

        $leave = (int) $totals['leave_days'];
        $absent = (int) $totals['absent_days'];
        $note = $this->deductionNote($leave, $absent);

        $payroll->fill([
            'scheduled_days' => $totals['scheduled_days'],
            'worked_days' => $totals['worked_days'],
            'leave_days' => $leave,
            'absent_days' => $absent,
            'holiday_days' => $totals['holiday_days'],
            'parent_skip_days' => $totals['parent_skip_days'],
            'upcoming_days' => $totals['upcoming_days'],
            'daily_rate' => $dailyRates === [] ? 0 : round(array_sum($dailyRates) / count($dailyRates), 2),
            'gross' => round($totals['gross'], 2),
            'deduction' => round($totals['deduction'], 2),
            'net' => round($totals['net'], 2),
            'expected_net' => round($totals['expected_net'], 2),
            'deduction_note' => $note,
        ]);
        $payroll->save();

        return $payroll->fresh('items');
    }

    /**
     * @return array<string, mixed>
     */
    private function itemForShift(PickupRequest $shift, Carbon $monthStart, Carbon $monthEnd): array
    {
        $rangeStart = $shift->shift_start_date && $shift->shift_start_date->gt($monthStart)
            ? $shift->shift_start_date->copy()->startOfDay()
            : $monthStart->copy();
        $rangeEnd = $shift->shift_end_date && $shift->shift_end_date->lt($monthEnd)
            ? $shift->shift_end_date->copy()->endOfDay()
            : $monthEnd->copy();

        $scheduled = $this->workingDates($rangeStart, $rangeEnd, $shift->days ?? []);
        $attendance = $shift->attendances
            ->filter(fn (ShiftAttendance $row) => $row->date && $row->date->betweenIncluded($rangeStart, $rangeEnd))
            ->keyBy(fn (ShiftAttendance $row) => $row->date->toDateString());

        $worked = 0;
        $leave = 0;
        $absent = 0;
        $holiday = 0;
        $parentSkip = 0;
        $upcoming = 0;

        foreach ($scheduled as $date) {
            $kind = $this->classifyDay($shift, $date, $attendance->get($date));
            match ($kind) {
                'present' => $worked++,
                'leave' => $leave++,
                'absent' => $absent++,
                'holiday' => $holiday++,
                'parent_skip' => $parentSkip++,
                default => $upcoming++,
            };
        }

        $payableDays = max(1, count($scheduled) - $holiday - $parentSkip);
        $monthly = (float) ($shift->driver_monthly_rate ?: 0);
        $daily = round($monthly / $payableDays, 2);
        $deductionDays = $leave + $absent;
        $gross = round($daily * $payableDays, 2);
        $deduction = round($daily * $deductionDays, 2);
        $net = round($daily * $worked, 2);
        $expected = round($daily * ($worked + $upcoming), 2);

        return [
            'pickup_request_id' => $shift->id,
            'scheduled_days' => count($scheduled),
            'worked_days' => $worked,
            'leave_days' => $leave,
            'absent_days' => $absent,
            'holiday_days' => $holiday,
            'parent_skip_days' => $parentSkip,
            'upcoming_days' => $upcoming,
            'daily_rate' => $daily,
            'gross' => $gross,
            'deduction' => $deduction,
            'net' => $net,
            'expected_net' => $expected,
            'deduction_note' => $this->deductionNote($leave, $absent),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function dayBreakdown(PickupRequest $shift, string $month): array
    {
        $cursor = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $monthStart = $cursor->copy()->startOfMonth();
        $monthEnd = $cursor->copy()->endOfMonth();
        $rangeStart = $shift->shift_start_date && $shift->shift_start_date->gt($monthStart)
            ? $shift->shift_start_date->copy()->startOfDay()
            : $monthStart->copy();
        $rangeEnd = $shift->shift_end_date && $shift->shift_end_date->lt($monthEnd)
            ? $shift->shift_end_date->copy()->endOfDay()
            : $monthEnd->copy();

        $shift->loadMissing(['attendances', 'replacements']);
        $attendance = $shift->attendances
            ->filter(fn (ShiftAttendance $row) => $row->date && $row->date->betweenIncluded($rangeStart, $rangeEnd))
            ->keyBy(fn (ShiftAttendance $row) => $row->date->toDateString());

        $rows = [];
        foreach ($this->workingDates($rangeStart, $rangeEnd, $shift->days ?? []) as $date) {
            $kind = $this->classifyDay($shift, $date, $attendance->get($date));
            $day = Carbon::parse($date);
            $rows[] = [
                'date' => $date,
                'label' => $day->format('D d M'),
                'is_today' => $day->isToday(),
                'is_past' => $day->lt(now()->startOfDay()),
                'kind' => $kind,
                'kind_label' => $this->kindLabel($kind),
                'paid' => $kind === 'present',
            ];
        }

        return $rows;
    }

    public function kindLabel(string $kind): string
    {
        return match ($kind) {
            'present' => 'Present — will be paid',
            'leave' => 'Leave — deducted',
            'absent' => 'No-show — deducted',
            'holiday' => 'Holiday — skipped',
            'parent_skip' => 'Parent cancelled — skipped',
            default => 'Still left this month',
        };
    }

    private function classifyDay(PickupRequest $shift, string $date, ?ShiftAttendance $row): string
    {
        $status = $row?->status;
        $reason = strtolower((string) ($row?->reason ?? ''));
        $past = Carbon::parse($date)->lt(now()->startOfDay());
        $covered = $shift->relationLoaded('replacements')
            ? $shift->replacements->contains(fn ($row) => $row->date?->toDateString() === $date)
            : ShiftReplacement::query()
                ->where('pickup_request_id', $shift->id)
                ->whereDate('date', $date)
                ->exists();

        if ($status === ShiftAttendance::PRESENT) {
            if ($row?->marked_by && (int) $row->marked_by !== (int) $shift->driver_id) {
                return 'leave';
            }

            return 'present';
        }

        if ($status === ShiftAttendance::HOLIDAY || Holiday::covers($date, $shift->city_id)) {
            return 'holiday';
        }

        if ($status === ShiftAttendance::LEAVE) {
            return 'leave';
        }

        if ($status === ShiftAttendance::SKIPPED) {
            if (str_contains($reason, 'driver') || str_contains($reason, 'leave') || str_contains($reason, 'unavailable')) {
                return 'leave';
            }

            return 'parent_skip';
        }

        if (str_contains($reason, 'unavailable') || str_contains($reason, 'leave')) {
            return 'leave';
        }

        if ($status === ShiftAttendance::ABSENT) {
            return 'absent';
        }

        if ($covered && $past) {
            return 'leave';
        }

        if (! $past) {
            return 'upcoming';
        }

        return 'absent';
    }

    /**
     * @param  list<string>  $days
     * @return list<string>
     */
    public function workingDates(Carbon $start, Carbon $end, array $days): array
    {
        $selected = [];
        $map = [
            'monday' => Carbon::MONDAY, 'mon' => Carbon::MONDAY,
            'tuesday' => Carbon::TUESDAY, 'tue' => Carbon::TUESDAY,
            'wednesday' => Carbon::WEDNESDAY, 'wed' => Carbon::WEDNESDAY,
            'thursday' => Carbon::THURSDAY, 'thu' => Carbon::THURSDAY,
            'friday' => Carbon::FRIDAY, 'fri' => Carbon::FRIDAY,
            'saturday' => Carbon::SATURDAY, 'sat' => Carbon::SATURDAY,
            'sunday' => Carbon::SUNDAY, 'sun' => Carbon::SUNDAY,
        ];
        foreach ($days as $day) {
            $key = strtolower(trim((string) $day));
            if (isset($map[$key])) {
                $selected[$map[$key]] = true;
            }
        }

        $dates = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            if (isset($selected[$d->dayOfWeek])) {
                $dates[] = $d->toDateString();
            }
        }

        return $dates;
    }

    public function approve(DriverPayroll $payroll, User $by): DriverPayroll
    {
        if ($payroll->isLocked()) {
            return $payroll;
        }

        $payroll->update([
            'status' => DriverPayroll::APPROVED,
            'approved_at' => now(),
            'processed_by' => $by->id,
        ]);

        return $payroll->fresh('items');
    }

    public function markPaid(DriverPayroll $payroll, User $by): DriverPayroll
    {
        $payroll->update([
            'status' => DriverPayroll::PAID,
            'paid_at' => now(),
            'processed_by' => $by->id,
        ]);

        PickupRequest::query()
            ->where('driver_id', $payroll->driver_id)
            ->where('driver_payout_status', '!=', PickupRequest::DRIVER_PAYOUT_PAID)
            ->whereNotNull('driver_payout_due_on')
            ->whereMonth('driver_payout_due_on', Carbon::createFromFormat('Y-m', $payroll->month)->month)
            ->whereYear('driver_payout_due_on', Carbon::createFromFormat('Y-m', $payroll->month)->year)
            ->update([
                'driver_payout_status' => PickupRequest::DRIVER_PAYOUT_PAID,
                'driver_payout_paid_at' => now(),
            ]);

        return $payroll->fresh('items');
    }

    private function deductionNote(int $leave, int $absent): ?string
    {
        $total = $leave + $absent;
        if ($total < 1) {
            return null;
        }

        $parts = [];
        if ($leave > 0) {
            $parts[] = $leave . ' leave';
        }
        if ($absent > 0) {
            $parts[] = $absent . ' absent';
        }

        return $total . ' days deduction (' . implode(', ', $parts) . ')';
    }
}
