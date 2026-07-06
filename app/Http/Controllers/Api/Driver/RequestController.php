<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Api\Driver\BaseApiController;
use App\Models\DriverPickupRequestRejection;
use App\Models\PickupRequest;
use App\Models\User;
use App\Services\AppNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class RequestController extends BaseApiController
{
    public function available(Request $request): JsonResponse
    {
        try {
            $driver = $request->user();
            $serviceAreaIds = $this->normalizedServiceAreaIds($driver);

            if ($serviceAreaIds === []) {
                return $this->successResponse([], 'No service areas selected');
            }

            $requests = PickupRequest::with(['parent', 'student', 'city', 'area'])
                ->where('status', 'pending')
                ->whereNull('driver_id')
                ->when($driver->city_id, fn ($query) => $query->where('city_id', $driver->city_id))
                ->whereIn('area_id', $serviceAreaIds)
                ->whereDoesntHave('driverRejections', function ($q) use ($driver) {
                    $q->where('driver_id', $driver->id);
                })
                ->latest()
                ->get();

            return $this->successResponse($requests, 'Available requests');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch available requests');
        }
    }

    /**
     * Pickup requests assigned to this driver (accepted and in-progress).
     * Optional: ?status=accepted|picked_up|dropped|completed (single status filter).
     */
    public function accepted(Request $request): JsonResponse
    {
        try {
            $driver = $request->user();

            $q = PickupRequest::with(['parent', 'student', 'city', 'area', 'vehicle'])
                ->where('driver_id', $driver->id)
                ->whereNotIn('status', ['pending', 'cancelled']);

            if ($request->filled('status')) {
                $status = $request->string('status')->toString();
                $allowed = ['accepted', 'picked_up', 'dropped', 'completed'];
                if (!in_array($status, $allowed, true)) {
                    return $this->errorResponse('Invalid status filter', 422, [
                        'status' => ['Must be one of: ' . implode(', ', $allowed)],
                    ]);
                }
                $q->where('status', $status);
            } else {
                $q->whereIn('status', ['accepted', 'picked_up', 'dropped']);
            }

            $requests = $q->latest()->get();

            return $this->successResponse($requests, 'Accepted requests');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch accepted requests');
        }
    }

    public function accept(Request $request, PickupRequest $pickupRequest): JsonResponse
    {
        try {
            $driver = $request->user();

            if ($driver->role !== 'driver') {
                return $this->errorResponse('Forbidden', 403);
            }

            $serviceAreaIds = $this->normalizedServiceAreaIds($driver);
            if ($serviceAreaIds === []) {
                return $this->errorResponse('Set your service areas before accepting requests', 422);
            }

            $updated = null;

            DB::transaction(function () use ($driver, $pickupRequest, $serviceAreaIds, &$updated) {
                /** @var PickupRequest|null $row */
                $row = PickupRequest::query()
                    ->lockForUpdate()
                    ->whereKey($pickupRequest->id)
                    ->first();

                if (!$row || $row->status !== 'pending' || $row->driver_id !== null) {
                    throw ValidationException::withMessages([
                        'pickup_request' => ['This request is no longer available.'],
                    ]);
                }

                if (!$this->driverCanServeRequest($driver, $row, $serviceAreaIds)) {
                    throw ValidationException::withMessages([
                        'pickup_request' => ['You cannot accept this request (city or area mismatch).'],
                    ]);
                }

                $row->driver_id = $driver->id;
                $row->status = 'accepted';
                $row->save();

                DriverPickupRequestRejection::query()
                    ->where('driver_id', $driver->id)
                    ->where('pickup_request_id', $row->id)
                    ->delete();

                $updated = $row->fresh(['parent', 'student', 'city', 'area', 'driver']);
            });

            if ($updated) {
                app(AppNotificationService::class)->notifyParentRequestAccepted($updated);
            }

            return $this->successResponse($updated, 'Request accepted');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to accept request');
        }
    }

    public function reject(Request $request, PickupRequest $pickupRequest): JsonResponse
    {
        try {
            $driver = $request->user();

            if ($driver->role !== 'driver') {
                return $this->errorResponse('Forbidden', 403);
            }

            $serviceAreaIds = $this->normalizedServiceAreaIds($driver);
            if ($serviceAreaIds === []) {
                return $this->errorResponse('Set your service areas first', 422);
            }

            if ($pickupRequest->status !== 'pending' || $pickupRequest->driver_id !== null) {
                return $this->errorResponse('This request is no longer open for rejection', 422);
            }

            if (!$this->driverCanServeRequest($driver, $pickupRequest, $serviceAreaIds)) {
                return $this->errorResponse('You cannot reject this request (city or area mismatch)', 422);
            }

            DriverPickupRequestRejection::query()->firstOrCreate([
                'driver_id' => $driver->id,
                'pickup_request_id' => $pickupRequest->id,
            ]);

            $notifier = app(AppNotificationService::class);
            $notifier->notifyParentDriverRejected($driver, $pickupRequest);
            $notifier->notifyDriverRejectedConfirmation($driver, $pickupRequest);

            return $this->successResponse([
                'pickup_request_id' => $pickupRequest->id,
            ], 'Request rejected');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to reject request');
        }
    }

    public function updateStatus(Request $request, PickupRequest $pickupRequest): JsonResponse
    {
        try {
            $driver = $request->user();

            if ($driver->role !== 'driver') {
                return $this->errorResponse('Forbidden', 403);
            }

            if ((int) $pickupRequest->driver_id !== (int) $driver->id) {
                return $this->errorResponse('Not found', 404);
            }

            $validated = $request->validate([
                'status' => ['required', 'in:picked_up,dropped,completed'],
            ]);

            $next = $validated['status'];
            $allowed = match ($pickupRequest->status) {
                'accepted' => ['picked_up'],
                'picked_up' => ['dropped'],
                'dropped' => ['completed'],
                default => [],
            };

            if (!in_array($next, $allowed, true)) {
                return $this->errorResponse(
                    sprintf('Cannot change status from %s to %s', $pickupRequest->status, $next),
                    422
                );
            }

            $pickupRequest->status = $next;
            if ($next === 'completed') {
                $pickupRequest->completed_at = now();
            }
            $pickupRequest->save();

            app(AppNotificationService::class)->notifyPickupRequestStatus($pickupRequest, $next);

            return $this->successResponse($pickupRequest->fresh(['parent', 'student', 'city', 'area']), 'Status updated');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to update request status');
        }
    }

    /**
     * @return list<int>
     */
    private function normalizedServiceAreaIds(User $driver): array
    {
        return collect($driver->service_areas ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  list<int>  $serviceAreaIds
     */
    private function driverCanServeRequest(User $driver, PickupRequest $pickupRequest, array $serviceAreaIds): bool
    {
        if ($serviceAreaIds === []) {
            return false;
        }

        if ($driver->city_id && (int) $pickupRequest->city_id !== (int) $driver->city_id) {
            return false;
        }

        return in_array((int) $pickupRequest->area_id, $serviceAreaIds, true);
    }
}
