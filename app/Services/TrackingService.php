<?php

namespace App\Services;

use App\Models\DriverLocationLog;
use App\Models\PickupRequest;
use App\Models\PlatformSetting;
use App\Models\ShiftDayRun;
use App\Models\User;

class TrackingService
{
    public function record(User $driver, float $lat, float $lng): User
    {
        $driver->update([
            'last_lat' => $lat,
            'last_lng' => $lng,
            'last_location_at' => now(),
        ]);

        DriverLocationLog::query()->create([
            'user_id' => $driver->id,
            'lat' => $lat,
            'lng' => $lng,
            'recorded_at' => now(),
        ]);

        $keepIds = DriverLocationLog::query()
            ->where('user_id', $driver->id)
            ->latest('id')
            ->limit(50)
            ->pluck('id');

        DriverLocationLog::query()
            ->where('user_id', $driver->id)
            ->whereNotIn('id', $keepIds)
            ->delete();

        $this->maybeNotifyArrival($driver);

        return $driver->fresh();
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(PickupRequest $pickupRequest): array
    {
        $pickupRequest->loadMissing(['driver', 'stops', 'student']);
        $assigned = $pickupRequest->driver;
        $driver = app(CoverService::class)->driverForDate($pickupRequest) ?: $assigned;
        $isCover = $driver && $assigned && (int) $driver->id !== (int) $assigned->id;
        $base = $pickupRequest->trackingApiArray();
        $geofence = (int) PlatformSetting::current()->geofence_meters;

        $trail = [];
        if ($driver) {
            $trail = DriverLocationLog::query()
                ->where('user_id', $driver->id)
                ->latest('id')
                ->limit(20)
                ->get(['lat', 'lng', 'recorded_at'])
                ->reverse()
                ->values()
                ->map(fn (DriverLocationLog $row) => [
                    'lat' => $row->lat,
                    'lng' => $row->lng,
                    'at' => $row->recorded_at?->toIso8601String(),
                ])
                ->all();
        }

        $nextStop = $pickupRequest->stops
            ->first(fn ($stop) => $stop->status === \App\Models\PickupRequestStop::STATUS_PENDING);

        $heading = $nextStop
            ? [
                'type' => $nextStop->type,
                'point' => $nextStop->point,
                'lat' => $nextStop->lat,
                'lng' => $nextStop->lng,
                'action' => $nextStop->isPickup()
                    ? 'Going to pick up at ' . $nextStop->point
                    : 'Going to drop at ' . $nextStop->point,
            ]
            : [
                'type' => 'drop',
                'point' => $pickupRequest->drop_point,
                'lat' => $pickupRequest->drop_lat,
                'lng' => $pickupRequest->drop_lng,
                'action' => 'Going to ' . $pickupRequest->drop_point,
            ];

        $fromMeters = $this->distanceMeters(
            (float) ($driver?->last_lat ?? 0),
            (float) ($driver?->last_lng ?? 0),
            (float) ($heading['lat'] ?? 0),
            (float) ($heading['lng'] ?? 0)
        );

        return array_merge($base, [
            'driver_id' => $driver?->id,
            'lat' => $driver?->last_lat !== null ? (float) $driver->last_lat : null,
            'lng' => $driver?->last_lng !== null ? (float) $driver->last_lng : null,
            'updated_at' => $driver?->last_location_at?->toIso8601String(),
            'driver_status' => $driver?->last_ride_status,
            'visible' => (bool) $driver?->last_lat,
            'is_cover_driver' => $isCover,
            'active_driver' => $driver ? [
                'id' => $driver->id,
                'name' => $driver->name,
                'phone' => $driver->phone,
                'is_cover' => $isCover,
            ] : null,
            'passenger' => $pickupRequest->student?->name ?: $pickupRequest->requesterName(),
            'coming_from' => [
                'point' => $pickupRequest->pickup_point,
                'lat' => $pickupRequest->pickup_lat,
                'lng' => $pickupRequest->pickup_lng,
            ],
            'going_to' => $heading,
            'pickup' => [
                'lat' => $pickupRequest->pickup_lat,
                'lng' => $pickupRequest->pickup_lng,
                'point' => $pickupRequest->pickup_point,
            ],
            'drop' => [
                'lat' => $pickupRequest->drop_lat,
                'lng' => $pickupRequest->drop_lng,
                'point' => $pickupRequest->drop_point,
            ],
            'journey' => $pickupRequest->journeyApiArray(),
            'distance_meters' => $driver?->last_lat ? (int) round($fromMeters) : null,
            'eta_minutes' => $driver?->last_lat ? max(1, (int) round(($fromMeters / 1000) / 25 * 60)) : null,
            'arriving' => $driver?->last_lat ? $fromMeters <= $geofence : false,
            'geofence_meters' => $geofence,
            'trail' => $trail,
        ]);
    }

    public function distanceMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        if ($lat1 === 0.0 && $lng1 === 0.0) {
            return 0;
        }

        $earth = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 2 * $earth * asin(min(1, sqrt($a)));
    }

    private function maybeNotifyArrival(User $driver): void
    {
        $today = now()->toDateString();
        $geofence = (int) PlatformSetting::current()->geofence_meters;
        $requests = PickupRequest::query()
            ->where('driver_id', $driver->id)
            ->where('payment_status', PickupRequest::PAYMENT_PAID)
            ->whereNotIn('status', ['cancelled'])
            ->get();

        foreach ($requests as $request) {
            $meters = $this->distanceMeters(
                (float) $driver->last_lat,
                (float) $driver->last_lng,
                (float) $request->pickup_lat,
                (float) $request->pickup_lng
            );
            if ($meters > $geofence) {
                continue;
            }

            $run = ShiftDayRun::query()
                ->where('pickup_request_id', $request->id)
                ->whereDate('date', $today)
                ->first();

            if (!$run || $run->arrival_notified_at || in_array($run->status, ['skipped', 'picked_up', 'dropped', 'completed'], true)) {
                continue;
            }

            $run->update(['arrival_notified_at' => now()]);
            app(AppNotificationService::class)->notify(
                (int) $request->parent_id,
                'driver_arriving',
                'Driver arriving',
                'Your driver is near the pickup point.',
                ['pickup_request_id' => $request->id]
            );
        }
    }
}
