<?php

namespace App\Services;

use App\Models\PickupRequest;
use App\Models\PickupRequestStop;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use RuntimeException;

class ShiftStopService
{
    /**
     * @param  list<array<string, mixed>>  $stops
     * @return list<array<string, mixed>>
     */
    public function normalize(array $stops, PickupRequest $request): array
    {
        $normalized = [];

        foreach (array_values($stops) as $index => $row) {
            $type = strtolower(trim((string) ($row['type'] ?? '')));
            if (!in_array($type, [PickupRequestStop::TYPE_PICKUP, PickupRequestStop::TYPE_DROP], true)) {
                throw new RuntimeException('Each stop must be pickup or drop.');
            }

            $point = trim((string) ($row['point'] ?? $row['name'] ?? ''));
            if ($point === '') {
                throw new RuntimeException('Each stop needs a location.');
            }

            $time = substr((string) ($row['time'] ?? $row['scheduled_time'] ?? ''), 0, 5);
            if (!preg_match('/^\d{2}:\d{2}$/', $time)) {
                throw new RuntimeException('Each stop needs a time in HH:MM format.');
            }

            $normalized[] = [
                'type' => $type,
                'sequence' => (int) ($row['sequence'] ?? ($index + 1)),
                'name' => trim((string) ($row['name'] ?? '')) ?: ($type === 'pickup' ? 'Pickup' : 'Drop'),
                'point' => $point,
                'lat' => (float) ($row['lat'] ?? $row['pickup_lat'] ?? $row['drop_lat'] ?? 0),
                'lng' => (float) ($row['lng'] ?? $row['pickup_lng'] ?? $row['drop_lng'] ?? 0),
                'area_id' => isset($row['area_id']) && $row['area_id'] !== '' ? (int) $row['area_id'] : null,
                'scheduled_time' => $time,
                'notes' => $row['notes'] ?? null,
            ];
        }

        usort($normalized, function ($a, $b) {
            return [$a['scheduled_time'], $a['sequence']] <=> [$b['scheduled_time'], $b['sequence']];
        });

        foreach ($normalized as $i => &$row) {
            $row['sequence'] = $i + 1;
        }
        unset($row);

        $pickups = collect($normalized)->where('type', 'pickup')->count();
        $drops = collect($normalized)->where('type', 'drop')->count();
        if ($pickups < 1 || $drops < 1) {
            throw new RuntimeException('Add at least one pickup stop and one drop stop.');
        }

        return $normalized;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function defaultsFromRequest(PickupRequest $request): array
    {
        return [
            [
                'type' => PickupRequestStop::TYPE_PICKUP,
                'sequence' => 1,
                'name' => 'Pickup',
                'point' => $request->pickup_point,
                'lat' => (float) $request->pickup_lat,
                'lng' => (float) $request->pickup_lng,
                'area_id' => $request->area_id,
                'scheduled_time' => substr((string) $request->pickup_time, 0, 5),
            ],
            [
                'type' => PickupRequestStop::TYPE_DROP,
                'sequence' => 2,
                'name' => 'Drop',
                'point' => $request->drop_point,
                'lat' => (float) $request->drop_lat,
                'lng' => (float) $request->drop_lng,
                'area_id' => $request->drop_area_id,
                'scheduled_time' => substr((string) $request->drop_time, 0, 5),
            ],
        ];
    }

    /**
     * @param  list<array<string, mixed>>|null  $stops
     */
    public function sync(PickupRequest $request, ?array $stops = null): void
    {
        $rows = $this->normalize($stops ?: $this->defaultsFromRequest($request), $request);

        $request->stops()->delete();
        foreach ($rows as $row) {
            $request->stops()->create($row + [
                'status' => PickupRequestStop::STATUS_PENDING,
            ]);
        }

        $this->mirrorEndpoints($request->fresh('stops'));
    }

    public function ensureDefaults(PickupRequest $request): void
    {
        if ($request->stops()->exists()) {
            return;
        }

        $this->sync($request, $this->defaultsFromRequest($request));
    }

    public function mirrorEndpoints(PickupRequest $request): void
    {
        $request->loadMissing('stops');
        $firstPickup = $request->stops->firstWhere('type', PickupRequestStop::TYPE_PICKUP);
        $lastDrop = $request->stops->where('type', PickupRequestStop::TYPE_DROP)->last();

        if ($firstPickup) {
            $request->pickup_point = $firstPickup->point;
            $request->pickup_lat = $firstPickup->lat;
            $request->pickup_lng = $firstPickup->lng;
            $request->pickup_time = $firstPickup->formattedTime();
            if ($firstPickup->area_id) {
                $request->area_id = $firstPickup->area_id;
            }
        }

        if ($lastDrop) {
            $request->drop_point = $lastDrop->point;
            $request->drop_lat = $lastDrop->lat;
            $request->drop_lng = $lastDrop->lng;
            $request->drop_time = $lastDrop->formattedTime();
            $request->drop_area_id = $lastDrop->area_id;
        }

        $request->save();
    }

    public function completeStop(PickupRequestStop $stop, string $action): PickupRequestStop
    {
        $request = $stop->pickupRequest;
        if (!$request) {
            throw new RuntimeException('Stop not found.');
        }

        if (!$request->isShiftPaid()) {
            throw new RuntimeException('Trip cannot start until the customer completes payment for this shift.');
        }

        if (in_array($request->status, ['cancelled', 'completed'], true)) {
            throw new RuntimeException('This shift is no longer active.');
        }

        $expected = $action === 'drop' ? PickupRequestStop::TYPE_DROP : PickupRequestStop::TYPE_PICKUP;
        if ($stop->type !== $expected) {
            throw new RuntimeException(
                $action === 'drop'
                    ? 'This stop is a pickup, not a drop.'
                    : 'This stop is a drop, not a pickup.'
            );
        }

        if (!$stop->isOpen()) {
            throw new RuntimeException('This stop is already marked.');
        }

        if ($action === 'pickup' && app(AttendanceService::class)->isOffDay($request)) {
            throw new RuntimeException('This shift is skipped or on holiday today.');
        }

        $stop->status = PickupRequestStop::STATUS_DONE;
        $stop->completed_at = now();
        $stop->save();

        if ($action === 'pickup') {
            app(AttendanceService::class)->markPresent($request, $request->driver);
        }

        $this->syncRequestStatus($request->fresh('stops'));

        return $stop->fresh(['area', 'pickupRequest.student', 'pickupRequest.parent', 'pickupRequest.city']);
    }

    public function markRequestStage(PickupRequest $request, string $stage): void
    {
        $now = now();

        if ($stage === 'picked_up') {
            $request->stops()
                ->where('type', PickupRequestStop::TYPE_PICKUP)
                ->where('status', PickupRequestStop::STATUS_PENDING)
                ->update([
                    'status' => PickupRequestStop::STATUS_DONE,
                    'completed_at' => $now,
                ]);
        }

        if (in_array($stage, ['dropped', 'completed'], true)) {
            $request->stops()
                ->where('status', PickupRequestStop::STATUS_PENDING)
                ->update([
                    'status' => PickupRequestStop::STATUS_DONE,
                    'completed_at' => $now,
                ]);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $stops
     * @return array{pickup: array<string, mixed>, drop: array<string, mixed>}
     */
    public function endpointsFromStops(array $stops): array
    {
        $normalized = $this->normalize($stops, new PickupRequest());
        $pickup = collect($normalized)->firstWhere('type', PickupRequestStop::TYPE_PICKUP);
        $drop = collect($normalized)->where('type', PickupRequestStop::TYPE_DROP)->last();

        return [
            'pickup' => $pickup,
            'drop' => $drop,
        ];
    }

    /**
     * @param  list<array<string, mixed>>|null  $stops
     * @return list<array<string, mixed>>
     */
    public function prepareInput(?array $stops): array
    {
        if (!is_array($stops)) {
            return [];
        }

        return array_map(function ($row) {
            if (!is_array($row)) {
                return [];
            }

            $time = $row['time'] ?? $row['scheduled_time'] ?? null;
            if (is_string($time) && strlen($time) >= 5) {
                $row['time'] = substr($time, 0, 5);
            }

            return $row;
        }, array_values($stops));
    }

    public function syncRequestStatus(PickupRequest $request): void
    {
        $request->loadMissing('stops');
        if ($request->stops->isEmpty() || in_array($request->status, ['cancelled', 'completed'], true)) {
            return;
        }

        $pickups = $request->stops->where('type', PickupRequestStop::TYPE_PICKUP);
        $drops = $request->stops->where('type', PickupRequestStop::TYPE_DROP);
        $pickupsFinished = $pickups->every(fn (PickupRequestStop $stop) => !$stop->isOpen());
        $dropsFinished = $drops->every(fn (PickupRequestStop $stop) => !$stop->isOpen());

        if ($dropsFinished && $pickupsFinished) {
            $request->status = 'dropped';
        } elseif ($pickupsFinished) {
            $request->status = 'picked_up';
        } elseif ($request->driver_id && $request->status === 'pending') {
            $request->status = 'accepted';
        }

        $request->save();
    }

    /**
     * @return Collection<int, PickupRequestStop>
     */
    public function todayForDriver(User $driver): Collection
    {
        $weekday = strtolower(Carbon::now()->englishDayOfWeek);
        $today = Carbon::now()->toDateString();
        $attendance = app(AttendanceService::class);

        return PickupRequestStop::query()
            ->with(['area', 'pickupRequest.student', 'pickupRequest.parent', 'pickupRequest.city'])
            ->whereHas('pickupRequest', function ($q) use ($driver, $weekday, $today) {
                $dayKeys = [
                    $weekday,
                    substr($weekday, 0, 3),
                    ucfirst($weekday),
                    ucfirst(substr($weekday, 0, 3)),
                ];

                $q->where('driver_id', $driver->id)
                    ->where('payment_status', PickupRequest::PAYMENT_PAID)
                    ->whereNotIn('status', ['cancelled', 'completed'])
                    ->where(function ($range) use ($today) {
                        $range->whereNull('shift_start_date')->orWhereDate('shift_start_date', '<=', $today);
                    })
                    ->where(function ($range) use ($today) {
                        $range->whereNull('shift_end_date')->orWhereDate('shift_end_date', '>=', $today);
                    })
                    ->where(function ($days) use ($dayKeys) {
                        foreach ($dayKeys as $key) {
                            $days->orWhereJsonContains('days', $key);
                        }
                    })
                    ->whereDoesntHave('attendances', function ($att) use ($today) {
                        $att->whereDate('date', $today)->whereIn('status', ['skipped', 'holiday']);
                    });
            })
            ->orderBy('scheduled_time')
            ->orderBy('sequence')
            ->get()
            ->filter(fn (PickupRequestStop $stop) => !$attendance->isOffDay($stop->pickupRequest, $today))
            ->values();
    }
}
