<?php

namespace App\Services;

use App\Models\Holiday;
use App\Models\PickupRequest;
use App\Models\ShiftAttendance;
use App\Models\User;
use Illuminate\Support\Carbon;
use RuntimeException;

class AttendanceService
{
    public function forRequest(PickupRequest $pickupRequest)
    {
        return $pickupRequest->attendances()->orderByDesc('date')->get();
    }

    public function todayRecord(PickupRequest $pickupRequest, ?string $date = null): ?ShiftAttendance
    {
        $day = $date ?: now()->toDateString();

        return $pickupRequest->attendances()->whereDate('date', $day)->first();
    }

    public function isOffDay(PickupRequest $pickupRequest, ?string $date = null): bool
    {
        $day = $date ?: now()->toDateString();
        $holiday = Holiday::covers($day, $pickupRequest->city_id);
        if ($holiday) {
            return true;
        }

        $row = $this->todayRecord($pickupRequest, $day);

        return $row?->isOffDay() ?? false;
    }

    public function skip(PickupRequest $pickupRequest, string $date, User $by, ?string $reason = null): ShiftAttendance
    {
        $day = Carbon::parse($date)->startOfDay();
        $this->assertDateInShift($pickupRequest, $day);

        if ($day->lt(now()->startOfDay())) {
            throw new RuntimeException('Cannot skip a past date.');
        }

        $existing = $this->todayRecord($pickupRequest, $day->toDateString());
        if ($existing && $existing->status === ShiftAttendance::PRESENT) {
            throw new RuntimeException('This day is already marked present.');
        }

        $row = ShiftAttendance::query()->updateOrCreate(
            [
                'pickup_request_id' => $pickupRequest->id,
                'date' => $day->toDateString(),
            ],
            [
                'status' => ShiftAttendance::SKIPPED,
                'reason' => $reason,
                'marked_by' => $by->id,
            ]
        );

        if ($pickupRequest->driver_id) {
            app(AppNotificationService::class)->notify(
                (int) $pickupRequest->driver_id,
                'attendance_skipped',
                'Pickup skipped today',
                sprintf(
                    '%s will not need pickup on %s.',
                    $pickupRequest->student?->name ?: $pickupRequest->requesterName(),
                    $day->toFormattedDateString()
                ),
                ['pickup_request_id' => $pickupRequest->id, 'date' => $day->toDateString()]
            );
        }

        return $row;
    }

    public function clearSkip(PickupRequest $pickupRequest, string $date): void
    {
        $row = $this->todayRecord($pickupRequest, Carbon::parse($date)->toDateString());
        if (!$row || $row->status !== ShiftAttendance::SKIPPED) {
            throw new RuntimeException('No skip found for this date.');
        }

        $row->delete();
    }

    public function markPresent(PickupRequest $pickupRequest, ?User $by = null, ?string $date = null): ShiftAttendance
    {
        $day = $date ?: now()->toDateString();

        return ShiftAttendance::query()->updateOrCreate(
            [
                'pickup_request_id' => $pickupRequest->id,
                'date' => $day,
            ],
            [
                'status' => ShiftAttendance::PRESENT,
                'reason' => null,
                'marked_by' => $by?->id,
            ]
        );
    }

    public function markHoliday(PickupRequest $pickupRequest, string $date, string $name): ShiftAttendance
    {
        return ShiftAttendance::query()->updateOrCreate(
            [
                'pickup_request_id' => $pickupRequest->id,
                'date' => $date,
            ],
            [
                'status' => ShiftAttendance::HOLIDAY,
                'reason' => $name,
            ]
        );
    }

    public function markAbsent(PickupRequest $pickupRequest, string $date): ShiftAttendance
    {
        $existing = $this->todayRecord($pickupRequest, $date);
        if ($existing) {
            return $existing;
        }

        return ShiftAttendance::query()->create([
            'pickup_request_id' => $pickupRequest->id,
            'date' => $date,
            'status' => ShiftAttendance::ABSENT,
            'reason' => 'No pickup recorded',
        ]);
    }

    private function assertDateInShift(PickupRequest $pickupRequest, Carbon $day): void
    {
        if ($pickupRequest->shift_start_date && $day->lt($pickupRequest->shift_start_date->startOfDay())) {
            throw new RuntimeException('Date is before the shift start.');
        }

        if ($pickupRequest->shift_end_date && $day->gt($pickupRequest->shift_end_date->endOfDay())) {
            throw new RuntimeException('Date is after the shift end.');
        }
    }
}
