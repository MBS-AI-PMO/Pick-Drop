<?php

namespace App\Services;

use App\Models\PickupRequest;
use App\Models\PickupRequestStop;
use App\Models\ShiftAttendance;
use App\Models\ShiftDayRun;
use App\Models\ShiftDayStopLog;
use App\Models\SosAlert;
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
            $stop->setAttribute('today_reason', $log?->reason);
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

        // OTP and photo are optional. If OTP is sent on pickup, it must match today's code.
        if ($action === 'pickup' && filled($otp)) {
            if ($otp !== $run->pickup_otp) {
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

    public function startTrip(PickupRequest $request, User $driver): ShiftDayRun
    {
        $this->assertDriverCanOperate($request, $driver);

        $run = $this->ensureToday($request);
        if ($run->status === ShiftDayRun::SKIPPED) {
            throw new RuntimeException('This shift is skipped today.');
        }

        if (in_array($run->status, [ShiftDayRun::DROPPED, ShiftDayRun::COMPLETED], true)) {
            throw new RuntimeException('This trip is already finished for today.');
        }

        if ($run->status === ShiftDayRun::SCHEDULED || !$run->started_at) {
            $run->status = ShiftDayRun::STARTED;
            $run->started_at = $run->started_at ?: now();
            $run->save();

            app(AppNotificationService::class)->notifyPickupRequestStatus($request, 'started');
        }

        return $run->fresh();
    }

    public function markArrived(PickupRequest $request, User $driver): ShiftDayRun
    {
        $this->assertDriverCanOperate($request, $driver);

        $run = $this->ensureToday($request);
        if ($run->status === ShiftDayRun::SKIPPED) {
            throw new RuntimeException('This shift is skipped today.');
        }

        if (in_array($run->status, [ShiftDayRun::DROPPED, ShiftDayRun::COMPLETED], true)) {
            throw new RuntimeException('This trip is already finished for today.');
        }

        if ($run->status === ShiftDayRun::SCHEDULED || !$run->started_at) {
            $run->status = ShiftDayRun::STARTED;
            $run->started_at = $run->started_at ?: now();
        }

        if (! in_array($run->status, [ShiftDayRun::PICKED_UP, ShiftDayRun::DROPPED, ShiftDayRun::COMPLETED], true)) {
            $run->status = ShiftDayRun::ARRIVED;
        }

        $run->arrived_at = $run->arrived_at ?: now();
        $run->save();

        app(AppNotificationService::class)->notifyPickupRequestStatus($request, 'arrived');

        return $run->fresh();
    }

    private function assertDriverCanOperate(PickupRequest $request, User $driver): void
    {
        if (!$request->isShiftPaid()) {
            throw new RuntimeException('Trip cannot start until the customer completes payment for this shift.');
        }

        if (in_array($request->status, ['cancelled'], true)) {
            throw new RuntimeException('This shift is no longer active.');
        }

        $todayDriver = app(CoverService::class)->driverForDate($request, now()->toDateString());
        if (!$todayDriver || (int) $todayDriver->id !== (int) $driver->id) {
            throw new RuntimeException('This trip is not assigned to you today.');
        }

        if ($this->attendance->isOffDay($request)) {
            throw new RuntimeException('This shift is skipped or on holiday today.');
        }
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
        $settled = [PickupRequestStop::STATUS_DONE, PickupRequestStop::STATUS_SKIPPED];

        $pickupsDone = $request->stops->where('type', PickupRequestStop::TYPE_PICKUP)
            ->every(fn (PickupRequestStop $stop) => in_array($logs[$stop->id]->status ?? null, $settled, true));
        $dropsDone = $request->stops->where('type', PickupRequestStop::TYPE_DROP)
            ->every(fn (PickupRequestStop $stop) => in_array($logs[$stop->id]->status ?? null, $settled, true));
        $anyDone = $request->stops->contains(
            fn (PickupRequestStop $stop) => ($logs[$stop->id]->status ?? null) === PickupRequestStop::STATUS_DONE
        );

        if ($request->stops->isNotEmpty() && $pickupsDone && $dropsDone && ! $anyDone && $forced === null) {
            $run->status = ShiftDayRun::ABSENT;
        } elseif ($forced === 'completed' || ($dropsDone && $pickupsDone)) {
            $run->status = ShiftDayRun::COMPLETED;
        } elseif ($forced === 'dropped' || $dropsDone) {
            $run->status = ShiftDayRun::DROPPED;
        } elseif ($forced === 'picked_up' || $pickupsDone) {
            $run->status = ShiftDayRun::PICKED_UP;
        }

        if ($run->status === ShiftDayRun::PICKED_UP && !$run->started_at) {
            $run->started_at = now();
        }

        $run->save();

        if (! in_array($request->status, ['cancelled', 'completed'], true)) {
            $bookingStatus = $request->status;

            if (in_array($run->status, [ShiftDayRun::DROPPED, ShiftDayRun::COMPLETED], true)) {
                $bookingStatus = 'dropped';
            } elseif ($run->status === ShiftDayRun::PICKED_UP) {
                $bookingStatus = 'picked_up';
            } elseif ($request->status === 'pending' && $request->driver_id) {
                $bookingStatus = 'accepted';
            }

            if ($bookingStatus !== $request->status) {
                $request->update(['status' => $bookingStatus]);
            }
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
     * @return array<string, mixed>
     */
    public function passengerProgress(Collection $stops): array
    {
        $statusOf = fn (PickupRequestStop $stop) => $stop->getAttribute('today_status') ?: PickupRequestStop::STATUS_PENDING;
        $pickups = $stops->where('type', PickupRequestStop::TYPE_PICKUP);
        $drops = $stops->where('type', PickupRequestStop::TYPE_DROP);
        $allSkipped = $stops->isNotEmpty() && $stops->every(
            fn (PickupRequestStop $stop) => $statusOf($stop) === PickupRequestStop::STATUS_SKIPPED
        );
        $pickupsDone = $pickups->isEmpty() || $pickups->every(
            fn (PickupRequestStop $stop) => $statusOf($stop) === PickupRequestStop::STATUS_DONE
        );
        $dropsDone = $drops->isEmpty() || $drops->every(
            fn (PickupRequestStop $stop) => $statusOf($stop) === PickupRequestStop::STATUS_DONE
        );
        $anyDone = $stops->contains(
            fn (PickupRequestStop $stop) => $statusOf($stop) === PickupRequestStop::STATUS_DONE
        );
        $run = $stops->first()?->getAttribute('today_run');
        $started = $run && (
            $run->started_at
            || in_array($run->status, [ShiftDayRun::STARTED, ShiftDayRun::ARRIVED, ShiftDayRun::PICKED_UP], true)
        );

        $passengerStatus = 'waiting';
        if ($allSkipped) {
            $passengerStatus = 'not_going';
        } elseif ($pickupsDone && $dropsDone) {
            $passengerStatus = 'dropped';
        } elseif ($pickupsDone && $pickups->isNotEmpty()) {
            $passengerStatus = 'picked';
        }

        return [
            'passenger_status' => $passengerStatus,
            'not_going_reason' => $allSkipped
                ? $stops->map(fn (PickupRequestStop $stop) => $stop->getAttribute('today_reason'))->filter()->first()
                : null,
            'can_mark_not_going' => (bool) $started && ! $allSkipped && ! $anyDone,
        ];
    }

    /**
     * @param  Collection<int, PickupRequestStop>  $stops
     * @return list<string>
     */
    public function unsettledPassengerNames(Collection $stops): array
    {
        return $stops->groupBy('pickup_request_id')->map(function (Collection $group) {
            $progress = $this->passengerProgress($group);
            if (in_array($progress['passenger_status'], ['dropped', 'not_going'], true)) {
                return null;
            }

            $request = $group->first()?->pickupRequest;

            return $request?->student?->name ?: $request?->requesterName();
        })->filter()->unique()->values()->all();
    }

    /**
     * @param  Collection<int, PickupRequestStop>  $stops
     * @return array<string, mixed>
     */
    public function todayTripSummary(User $driver, Collection $stops): array
    {
        $groups = $stops->groupBy('pickup_request_id');
        $progress = $groups->map(fn (Collection $group) => $this->passengerProgress($group));
        $pending = $this->unsettledPassengerNames($stops);
        $emergency = $this->driverHasOpenEmergency($driver);
        $runs = $stops->map(fn (PickupRequestStop $stop) => $stop->getAttribute('today_run'))->filter()->unique('id');
        $started = $runs->contains(fn (ShiftDayRun $run) => $run->started_at || in_array($run->status, [
            ShiftDayRun::STARTED,
            ShiftDayRun::ARRIVED,
            ShiftDayRun::PICKED_UP,
        ], true));
        $ended = $runs->isNotEmpty() && $runs->every(fn (ShiftDayRun $run) => (bool) $run->ended_at);

        $reason = null;
        if ($emergency) {
            $reason = 'An emergency is open. Admin will end this trip.';
        } elseif ($pending !== []) {
            $reason = 'Drop all passengers before ending the trip.';
        } elseif ($ended) {
            $reason = 'This trip is already ended.';
        }

        return [
            'can_end' => $stops->isNotEmpty() && $started && ! $emergency && $pending === [] && ! $ended,
            'end_blocked_reason' => $reason,
            'emergency_open' => $emergency,
            'ended' => $ended,
            'passengers_total' => $groups->count(),
            'passengers_picked' => $progress->whereIn('passenger_status', ['picked', 'dropped'])->count(),
            'passengers_dropped' => $progress->where('passenger_status', 'dropped')->count(),
            'passengers_not_going' => $progress->where('passenger_status', 'not_going')->count(),
            'pending_passengers' => $pending,
        ];
    }

    public function driverHasOpenEmergency(User $driver): bool
    {
        return SosAlert::query()
            ->where('user_id', $driver->id)
            ->whereIn('status', [SosAlert::OPEN, SosAlert::ACKNOWLEDGED])
            ->whereDate('created_at', now()->toDateString())
            ->exists();
    }

    public function lockTripForEmergency(User $driver): void
    {
        $stops = $this->todayStopsForDriver($driver);

        foreach ($stops->groupBy('pickup_request_id') as $group) {
            $request = $group->first()?->pickupRequest;
            if (! $request) {
                continue;
            }

            $run = $this->ensureToday($request);
            if ($run->ended_at || $run->status === ShiftDayRun::SKIPPED) {
                continue;
            }

            $run->emergency_locked = true;
            $run->save();
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function endTripByDriver(User $driver): array
    {
        if ($this->driverHasOpenEmergency($driver)) {
            throw new RuntimeException('An emergency is open. Admin will end this trip.');
        }

        $stops = $this->todayStopsForDriver($driver);
        if ($stops->isEmpty()) {
            throw new RuntimeException('No passengers are assigned for today.');
        }

        $summary = $this->todayTripSummary($driver, $stops);
        if ($summary['ended']) {
            throw new RuntimeException('This trip is already ended.');
        }

        $started = $stops->map(fn (PickupRequestStop $stop) => $stop->getAttribute('today_run'))
            ->filter()
            ->contains(fn (ShiftDayRun $run) => $run->started_at || in_array($run->status, [
                ShiftDayRun::STARTED,
                ShiftDayRun::ARRIVED,
                ShiftDayRun::PICKED_UP,
                ShiftDayRun::DROPPED,
                ShiftDayRun::COMPLETED,
                ShiftDayRun::ABSENT,
            ], true));

        if (! $started) {
            throw new RuntimeException('Start the trip before ending it.');
        }

        $pending = $summary['pending_passengers'];
        if ($pending !== []) {
            throw new RuntimeException('Drop these passengers before ending the trip: ' . implode(', ', $pending) . '.');
        }

        $this->closeTodayRuns($driver, 'driver');

        return $this->todayTripSummary($driver, $this->todayStopsForDriver($driver));
    }

    public function endTripByAdmin(User $driver): void
    {
        $closed = $this->closeTodayRuns($driver, 'admin');
        if ($closed === 0) {
            throw new RuntimeException('No active trip to end.');
        }
    }

    public function markPassengerNotGoing(PickupRequest $request, User $driver, string $reason): PickupRequest
    {
        $this->assertDriverCanOperate($request, $driver);

        $run = $this->ensureToday($request);
        if ($run->status === ShiftDayRun::SKIPPED) {
            throw new RuntimeException('This shift is skipped today.');
        }

        if ($run->ended_at) {
            throw new RuntimeException('This trip is already ended.');
        }

        $started = $run->started_at || in_array($run->status, [
            ShiftDayRun::STARTED,
            ShiftDayRun::ARRIVED,
            ShiftDayRun::PICKED_UP,
        ], true);

        if (! $started) {
            throw new RuntimeException('Start the trip before marking a passenger.');
        }

        $request->loadMissing('stops');
        if ($request->stops->isEmpty()) {
            throw new RuntimeException('This passenger has no pickup or drop for today.');
        }

        $logs = ShiftDayStopLog::query()
            ->where('shift_day_run_id', $run->id)
            ->get()
            ->keyBy('pickup_request_stop_id');

        foreach ($request->stops as $stop) {
            if (($logs[$stop->id]->status ?? null) === PickupRequestStop::STATUS_DONE) {
                throw new RuntimeException('This passenger is already picked up. Drop them to finish.');
            }
        }

        foreach ($request->stops as $stop) {
            ShiftDayStopLog::query()->updateOrCreate(
                [
                    'shift_day_run_id' => $run->id,
                    'pickup_request_stop_id' => $stop->id,
                ],
                [
                    'status' => PickupRequestStop::STATUS_SKIPPED,
                    'reason' => $reason,
                    'completed_at' => now(),
                ]
            );
        }

        ShiftAttendance::query()->updateOrCreate(
            [
                'pickup_request_id' => $request->id,
                'date' => now()->toDateString(),
            ],
            [
                'status' => ShiftAttendance::ABSENT,
                'reason' => $reason,
                'marked_by' => $driver->id,
            ]
        );

        $this->syncDayStatus($run->fresh('stopLogs'), $request->fresh('stops'));

        $name = $request->student?->name ?: $request->requesterName();
        if ($request->parent_id) {
            app(AppNotificationService::class)->notify(
                (int) $request->parent_id,
                'passenger_not_going',
                'Passenger not going',
                sprintf('%s is not going today. %s', $name, $reason),
                ['pickup_request_id' => $request->id]
            );
        }

        app(AppNotificationService::class)->notifyAdminPanel(
            'Passenger not going',
            sprintf('%s marked %s as not going. %s', $driver->name ?? 'Driver', $name, $reason),
            'warning'
        );

        return $request->fresh(['parent', 'student', 'city', 'area', 'dropArea', 'stops.area']);
    }

    private function closeTodayRuns(User $driver, string $endedBy): int
    {
        $requestIds = $this->todayStopsForDriver($driver)->pluck('pickup_request_id')->unique()->filter()->values();

        $runs = ShiftDayRun::query()
            ->whereDate('date', now()->toDateString())
            ->whereNull('ended_at')
            ->where('status', '!=', ShiftDayRun::SKIPPED)
            ->where(function ($query) use ($driver, $requestIds) {
                $query->whereIn('pickup_request_id', $requestIds->all())
                    ->orWhere(function ($locked) use ($driver) {
                        $locked->where('emergency_locked', true)
                            ->whereHas('pickupRequest', fn ($q) => $q->where('driver_id', $driver->id));
                    });
            })
            ->get();

        foreach ($runs as $run) {
            $run->ended_at = now();
            $run->ended_by = $endedBy;
            $run->emergency_locked = false;
            if (! in_array($run->status, [ShiftDayRun::COMPLETED, ShiftDayRun::DROPPED, ShiftDayRun::ABSENT], true)) {
                $run->status = ShiftDayRun::COMPLETED;
            }
            $run->save();

            $request = $run->pickupRequest;
            if ($request && ! in_array($request->status, ['cancelled', 'completed'], true)) {
                $request->update(['status' => 'dropped']);
            }
        }

        return $runs->count();
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
