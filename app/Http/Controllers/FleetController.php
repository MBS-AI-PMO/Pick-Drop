<?php

namespace App\Http\Controllers;

use App\Models\PickupRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FleetController extends Controller
{
    public function index()
    {
        return view('pickdrop.fleet.live');
    }

    public function live(Request $request): JsonResponse
    {
        $activeStatuses = ['accepted', 'picked_up', 'dropped'];

        $trips = PickupRequest::query()
            ->with(['driver.driverVerification', 'vehicle', 'student', 'parent', 'city'])
            ->where('payment_status', PickupRequest::PAYMENT_PAID)
            ->whereIn('status', $activeStatuses)
            ->whereNotNull('driver_id')
            ->latest()
            ->limit(80)
            ->get()
            ->map(function (PickupRequest $trip) {
                $driver = $trip->driver;

                return [
                    'id' => $trip->id,
                    'status' => $trip->status,
                    'passenger' => $trip->student?->name ?: $trip->requesterName(),
                    'parent' => $trip->parent?->name,
                    'city' => $trip->city?->name,
                    'pickup' => $trip->pickup_point,
                    'drop' => $trip->drop_point,
                    'pickup_time' => substr((string) $trip->pickup_time, 0, 5),
                    'driver' => $driver ? [
                        'id' => $driver->id,
                        'name' => $driver->name,
                        'phone' => $driver->phone,
                        'duty_status' => $driver->duty_status ?: 'on_duty',
                        'photo' => $driver->driverVerification?->documentUrl($driver->driverVerification->selfie_photo),
                        'lat' => $driver->last_lat !== null ? (float) $driver->last_lat : null,
                        'lng' => $driver->last_lng !== null ? (float) $driver->last_lng : null,
                        'updated_at' => $driver->last_location_at?->toIso8601String(),
                        'ride_status' => $driver->last_ride_status,
                    ] : null,
                    'vehicle' => $trip->vehicle ? [
                        'id' => $trip->vehicle->id,
                        'name' => $trip->vehicle->name,
                        'plate' => $trip->vehicle->license_plate,
                    ] : null,
                ];
            })
            ->values();

        $driversOnDuty = User::query()
            ->whereRaw('LOWER(role) = ?', ['driver'])
            ->whereRaw('LOWER(TRIM(status)) = ?', ['active'])
            ->where(function ($q) {
                $q->whereNull('duty_status')->orWhere('duty_status', 'on_duty');
            })
            ->whereNotNull('last_lat')
            ->count();

        return response()->json([
            'generated_at' => now()->toIso8601String(),
            'summary' => [
                'active_trips' => $trips->count(),
                'drivers_on_map' => $trips->filter(fn ($t) => $t['driver']['lat'] ?? null)->count(),
                'drivers_on_duty' => $driversOnDuty,
            ],
            'trips' => $trips,
        ]);
    }
}
