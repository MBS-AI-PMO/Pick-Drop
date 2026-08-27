<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Api\Driver\BaseApiController;
use App\Models\PickupRequest;
use App\Models\PickupRequestStop;
use App\Services\AppNotificationService;
use App\Services\ShiftStopService;
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

            $stops = app(ShiftStopService::class)->todayForDriver($driver);
            $payload = $stops->map(fn (PickupRequestStop $stop) => $stop->toApiArray())->values();

            return $this->successResponse([
                'date' => Carbon::now()->toDateString(),
                'weekday' => strtolower(Carbon::now()->englishDayOfWeek),
                'summary' => [
                    'total' => $payload->count(),
                    'pending' => $payload->where('status', PickupRequestStop::STATUS_PENDING)->count(),
                    'done' => $payload->where('status', PickupRequestStop::STATUS_DONE)->count(),
                    'pickups' => $payload->where('type', PickupRequestStop::TYPE_PICKUP)->count(),
                    'drops' => $payload->where('type', PickupRequestStop::TYPE_DROP)->count(),
                ],
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

            $rides = PickupRequest::with(['parent', 'student', 'city', 'area', 'dropArea', 'stops.area'])
                ->where('driver_id', $driver->id)
                ->whereIn('status', ['dropped', 'completed'])
                ->latest('completed_at')
                ->latest('id')
                ->paginate(AppPagination::PER_PAGE);

            $rides->getCollection()->transform(fn (PickupRequest $row) => $row->toApiArray('driver'));

            return $this->successResponse($rides, 'Rides history');
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

            return $this->successResponse($validated, 'Location updated');
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

            return $this->successResponse($validated, 'Status updated');
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

            $updated = app(ShiftStopService::class)->completeStop($stop, $action);
            $shift = $updated->pickupRequest?->fresh(['parent', 'student', 'city', 'area', 'dropArea', 'stops.area']);

            if ($shift) {
                app(AppNotificationService::class)->notifyStopCompleted($shift, $updated);
            }

            return $this->successResponse([
                'stop' => $updated->toApiArray(),
                'request' => $shift?->toApiArray('driver'),
            ], $action === 'drop' ? 'Drop marked as done' : 'Pickup marked as done');
        } catch (RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to mark ' . $action);
        }
    }
}
