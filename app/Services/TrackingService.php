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
        $base = $pickupRequest->trackingApiArray();
        $driver = $pickupRequest->driver;
        $meters = $this->distanceMeters(
            (float) ($driver?->last_lat ?? 0),
            (float) ($driver?->last_lng ?? 0),
            (float) $pickupRequest->pickup_lat,
            (float) $pickupRequest->pickup_lng
        );

        $geofence = (int) PlatformSetting::current()->geofence_meters;
        $eta = $meters > 0 ? max(1, (int) round(($meters / 1000) / 25 * 60)) : null;

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

        return array_merge($base, [
            'pickup' => [
                'lat' => $pickupRequest->pickup_lat,
                'lng' => $pickupRequest->pickup_lng,
            ],
            'drop' => [
                'lat' => $pickupRequest->drop_lat,
                'lng' => $pickupRequest->drop_lng,
            ],
            'distance_meters' => $driver?->last_lat ? (int) round($meters) : null,
            'eta_minutes' => $driver?->last_lat ? $eta : null,
            'arriving' => $driver?->last_lat ? $meters <= $geofence : false,
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
