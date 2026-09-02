<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Api\Driver\BaseApiController;
use App\Models\PickupRequest;
use App\Models\PickupRequestStop;
use App\Models\ShiftDayRun;
use App\Services\ShiftDayService;
use App\Services\TrackingService;
use App\Support\AppPagination;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class RideController extends BaseApiController
{
    public function today(Request $request): JsonResponse
    {
        try {
            $driver = $request->user();
            $denied = $this->denyUnlessDriverReady($driver);
            if ($denied) {
                return $denied;
            }

            $stops = app(ShiftDayService::class)->todayStopsForDriver($driver);
            $stops->loadMissing('pickupRequest.stops.area');
            $payload = $stops->map(fn (PickupRequestStop $stop) => $stop->toApiArray())->values();
            $trips = $stops->groupBy('pickup_request_id')->map(function ($group) {
                $pickupRequest = $group->first()?->pickupRequest;

                return [
                    'pickup_request_id' => $pickupRequest?->id,
                    'passenger' => $pickupRequest?->student?->name ?: $pickupRequest?->requesterName(),
                    'round_trip' => $pickupRequest?->round_trip !== false,
                    'journey' => $pickupRequest?->journeyApiArray(),
                    'stop_ids' => $group->pluck('id')->values()->all(),
                ];
            })->values();

            return $this->successResponse([
                'date' => Carbon::now()->toDateString(),
                'weekday' => strtolower(Carbon::now()->englishDayOfWeek),
                'optimized' => true,
                'round_trip_rule' => 'Drop every passenger back at the same pickup point.',
                'summary' => [
                    'total' => $payload->count(),
                    'pending' => $payload->where('status', PickupRequestStop::STATUS_PENDING)->count(),
                    'done' => $payload->where('status', PickupRequestStop::STATUS_DONE)->count(),
                    'pickups' => $payload->where('type', PickupRequestStop::TYPE_PICKUP)->count(),
                    'drops' => $payload->where('type', PickupRequestStop::TYPE_DROP)->count(),
                ],
                'trips' => $trips,
                'stops' => $payload,
            ], "Today's rides");
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch today rides');
        }
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $driver = $request->user();
            $denied = $this->denyUnlessDriverReady($driver);
            if ($denied) {
                return $denied;
            }

            $runs = ShiftDayRun::query()
                ->with(['pickupRequest.parent', 'pickupRequest.student', 'pickupRequest.city'])
                ->whereHas('pickupRequest', fn ($q) => $q->where('driver_id', $driver->id))
                ->whereIn('status', [ShiftDayRun::DROPPED, ShiftDayRun::COMPLETED])
                ->latest('date')
                ->paginate(AppPagination::PER_PAGE);

            $runs->getCollection()->transform(fn (ShiftDayRun $row) => array_merge(
                $row->toApiArray(),
                ['request' => $row->pickupRequest?->toApiArray('driver')]
            ));

            return $this->successResponse($runs, 'Rides history');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch rides');
        }
    }

    public function markPickup(Request $request, int $rideId): JsonResponse
    {
        return $this->markStop($request, $rideId, 'pickup');
    }

    public function markDrop(Request $request, int $rideId): JsonResponse
    {
        return $this->markStop($request, $rideId, 'drop');
    }

    public function updateLocation(Request $request): JsonResponse
    {
        try {
            $driver = $request->user();
            $denied = $this->denyUnlessDriverReady($driver);
            if ($denied) {
                return $denied;
            }

            $validated = $request->validate([
                'lat' => ['required', 'numeric', 'between:-90,90'],
                'lng' => ['required', 'numeric', 'between:-180,180'],
            ]);

            $driver = app(TrackingService::class)->record($driver, (float) $validated['lat'], (float) $validated['lng']);

            return $this->successResponse([
                'lat' => (float) $driver->last_lat,
                'lng' => (float) $driver->last_lng,
                'updated_at' => $driver->last_location_at?->toIso8601String(),
            ], 'Location updated');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to update location');
        }
    }

    public function updateStatus(Request $request): JsonResponse
    {
        try {
            $driver = $request->user();
            $denied = $this->denyUnlessDriverReady($driver);
            if ($denied) {
                return $denied;
            }

            $validated = $request->validate([
                'status' => ['required', 'string'],
            ]);

            $driver->update([
                'last_ride_status' => $validated['status'],
            ]);

            return $this->successResponse([
                'status' => $driver->last_ride_status,
            ], 'Status updated');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to update status');
        }
    }

    private function markStop(Request $request, int $stopId, string $action): JsonResponse
    {
        try {
            $driver = $request->user();
            $denied = $this->denyUnlessDriverReady($driver);
            if ($denied) {
                return $denied;
            }

            $stop = PickupRequestStop::query()
                ->with(['pickupRequest', 'area'])
                ->find($stopId);

            if (!$stop || (int) $stop->pickupRequest?->driver_id !== (int) $driver->id) {
                return $this->errorResponse('Not found', 404);
            }

            $validated = $request->validate([
                'otp' => ['nullable', 'string', 'max:6'],
                'photo' => ['nullable', 'image', 'max:5120'],
            ]);

            $updated = app(ShiftDayService::class)->completeStop(
                $stop,
                $action,
                $driver,
                $validated['otp'] ?? null,
                $request->file('photo')
            );
            $shift = $updated->pickupRequest?->fresh(['parent', 'student', 'city', 'area', 'dropArea', 'stops.area']);

            return $this->successResponse([
                'stop' => $updated->toApiArray(),
                'request' => $shift?->toApiArray('driver'),
                'today' => $updated->getAttribute('today_run')?->toApiArray(false),
            ], $action === 'drop' ? 'Drop marked as done' : 'Pickup marked as done');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to mark ' . $action);
        }
    }
}
