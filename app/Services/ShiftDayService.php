<?php

namespace App\Services;

use App\Models\PickupRequest;
use App\Models\PickupRequestStop;
use App\Models\PlatformSetting;
use App\Models\ShiftDayRun;
use App\Models\ShiftDayStopLog;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use RuntimeException;

class ShiftDayService
{
    public function __construct(
        private readonly AttendanceService $attendance,
        private readonly TrackingService $tracking
    ) {
    }

    public function ensureToday(PickupRequest $request, ?string $date = null): ShiftDayRun
    {
        $day = $date ?: now()->toDateString();

        $run = ShiftDayRun::query()->firstOrCreate(
            [
                'pickup_request_id' => $request->id,
                'date' => $day,
            ],
            [
                'status' => ShiftDayRun::SCHEDULED,
                'pickup_otp' => (string) random_int(1000, 9999),
            ]
        );

        if (!$run->pickup_otp) {
            $run->update(['pickup_otp' => (string) random_int(1000, 9999)]);
        }

        if ($this->attendance->isOffDay($request, $day) && $run->status === ShiftDayRun::SCHEDULED) {
            $run->update(['status' => ShiftDayRun::SKIPPED]);
        }

        return $run->fresh();
    }

    /**
     * @return Collection<int, PickupRequestStop>
     */
    public function todayStopsForDriver(User $driver): Collection
    {
        $stops = app(ShiftStopService::class)->todayForDriver($driver);
        $today = now()->toDateString();

        $ordered = $this->orderStops($stops, $driver);

        return $ordered->map(function (PickupRequestStop $stop) use ($today) {
            $run = $this->ensureToday($stop->pickupRequest, $today);
            $log = ShiftDayStopLog::query()
                ->where('shift_day_run_id', $run->id)
                ->where('pickup_request_stop_id', $stop->id)
                ->first();

            $stop->setAttribute('today_status', $log?->status ?? PickupRequestStop::STATUS_PENDING);
            $stop->setAttribute('today_run', $run);
            $stop->setAttribute('today_completed_at', $log?->completed_at);

            return $stop;
        });
    }

    public function completeStop(
        PickupRequestStop $stop,
        string $action,
        User $driver,
        ?string $otp = null,
        ?UploadedFile $photo = null
    ): PickupRequestStop {
        $request = $stop->pickupRequest;
        if (!$request || !$request->isShiftPaid()) {
            throw new RuntimeException('Trip cannot start until the customer completes payment for this shift.');
        }

        if (in_array($request->status, ['cancelled'], true)) {
            throw new RuntimeException('This shift is no longer active.');
        }

        if ($this->attendance->isOffDay($request)) {
            throw new RuntimeException('This shift is skipped or on holiday today.');
        }

        $expected = $action === 'drop' ? PickupRequestStop::TYPE_DROP : PickupRequestStop::TYPE_PICKUP;
        if ($stop->type !== $expected) {
            throw new RuntimeException(
                $action === 'drop'
                    ? 'This stop is a pickup, not a drop.'
                    : 'This stop is a drop, not a pickup.'
            );
        }

        $run = $this->ensureToday($request);
        if ($run->status === ShiftDayRun::SKIPPED) {
            throw new RuntimeException('This shift is skipped today.');
        }

        $settings = PlatformSetting::current();
        if ($action === 'pickup' && $settings->pickup_otp_enabled) {
            if (!$otp || $otp !== $run->pickup_otp) {
                throw new RuntimeException('Invalid pickup OTP. Ask the parent for today\'s code.');
            }
            $run->pickup_verified_at = now();
        }

        $existing = ShiftDayStopLog::query()
            ->where('shift_day_run_id', $run->id)
            ->where('pickup_request_stop_id', $stop->id)
            ->first();

        if ($existing && $existing->status === PickupRequestStop::STATUS_DONE) {
            throw new RuntimeException('This stop is already marked for today.');
        }

        $photoPath = $photo?->store('pickup-proofs/' . $request->id, 'public');

        ShiftDayStopLog::query()->updateOrCreate(
            [
                'shift_day_run_id' => $run->id,
                'pickup_request_stop_id' => $stop->id,
            ],
            [
                'status' => PickupRequestStop::STATUS_DONE,
                'completed_at' => now(),
                'photo_path' => $photoPath,
            ]
        );

        if ($action === 'pickup') {
            if ($photoPath) {
                $run->pickup_photo_path = $photoPath;
            }
            app(AttendanceService::class)->markPresent($request, $driver);
        }

        $run->save();
        $this->syncDayStatus($run->fresh('stopLogs'), $request);

        $stop->setAttribute('today_status', PickupRequestStop::STATUS_DONE);
        $stop->setAttribute('today_run', $run->fresh());

        return $stop->fresh(['area', 'pickupRequest.student', 'pickupRequest.parent', 'pickupRequest.city']);
    }

    public function markRequestStage(PickupRequest $request, string $stage): ShiftDayRun
    {
        $run = $this->ensureToday($request);
        $now = now();

        $stopIds = $request->stops()->pluck('id');
        if ($stage === 'picked_up') {
            $ids = $request->stops()->where('type', PickupRequestStop::TYPE_PICKUP)->pluck('id');
        } else {
            $ids = $stopIds;
        }

        foreach ($ids as $stopId) {
            ShiftDayStopLog::query()->updateOrCreate(
                [
                    'shift_day_run_id' => $run->id,
                    'pickup_request_stop_id' => $stopId,
                ],
                [
                    'status' => PickupRequestStop::STATUS_DONE,
                    'completed_at' => $now,
                ]
            );
        }

        if ($stage === 'picked_up') {
            app(AttendanceService::class)->markPresent($request, $request->driver);
        }

        $this->syncDayStatus($run->fresh('stopLogs'), $request, $stage);

        return $run->fresh();
    }

    public function syncDayStatus(ShiftDayRun $run, PickupRequest $request, ?string $forced = null): void
    {
        $request->loadMissing('stops');
        $logs = $run->stopLogs->keyBy('pickup_request_stop_id');

        $pickupsDone = $request->stops->where('type', PickupRequestStop::TYPE_PICKUP)
            ->every(fn (PickupRequestStop $stop) => ($logs[$stop->id]->status ?? null) === PickupRequestStop::STATUS_DONE);
        $dropsDone = $request->stops->where('type', PickupRequestStop::TYPE_DROP)
            ->every(fn (PickupRequestStop $stop) => ($logs[$stop->id]->status ?? null) === PickupRequestStop::STATUS_DONE);

        if ($forced === 'completed' || ($dropsDone && $pickupsDone)) {
            $run->status = ShiftDayRun::COMPLETED;
        } elseif ($forced === 'dropped' || $dropsDone) {
            $run->status = ShiftDayRun::DROPPED;
        } elseif ($forced === 'picked_up' || $pickupsDone) {
            $run->status = ShiftDayRun::PICKED_UP;
        }

        $run->save();

        if ($request->status === 'pending' && $request->driver_id) {
            $request->update(['status' => 'accepted']);
        }

        $notify = match ($run->status) {
            ShiftDayRun::PICKED_UP => 'picked_up',
            ShiftDayRun::DROPPED, ShiftDayRun::COMPLETED => $run->status === ShiftDayRun::COMPLETED ? 'completed' : 'dropped',
            default => null,
        };

        if ($notify) {
            app(AppNotificationService::class)->notifyPickupRequestStatus($request, $notify);
        }
    }

    /**
     * @param  Collection<int, PickupRequestStop>  $stops
     * @return Collection<int, PickupRequestStop>
     */
    public function orderStops(Collection $stops, User $driver): Collection
    {
        if ($stops->count() < 3 || !$driver->last_lat) {
            return $stops->values();
        }

        $remaining = $stops->values();
        $ordered = collect();
        $lat = (float) $driver->last_lat;
        $lng = (float) $driver->last_lng;

        while ($remaining->isNotEmpty()) {
            $next = $remaining->sortBy(function (PickupRequestStop $stop) use ($lat, $lng) {
                return $this->tracking->distanceMeters($lat, $lng, (float) $stop->lat, (float) $stop->lng);
            })->first();

            $ordered->push($next);
            $remaining = $remaining->reject(fn ($s) => $s->id === $next->id)->values();
            $lat = (float) $next->lat;
            $lng = (float) $next->lng;
        }

        return $ordered->values();
    }
}
