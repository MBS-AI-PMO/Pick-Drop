<?php

namespace App\Http\Controllers\Api\Driver;

use App\Models\DriverPickupRequestRejection;
use App\Models\PickupRequest;
use App\Services\AppNotificationService;
use App\Services\InvoiceService;
use App\Services\PickupRequestMatchingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class RequestController extends BaseApiController
{
    public function __construct(private readonly PickupRequestMatchingService $matcher)
    {
    }

    public function available(Request $request): JsonResponse
    {
        try {
            $driver = $request->user();
            $denied = $this->denyUnlessDriverReady($driver);
            if ($denied) {
                return $denied;
            }

            $requests = $this->matcher
                ->constrainAvailableQuery(
                    PickupRequest::query()->with(['parent', 'student', 'city', 'area', 'dropArea', 'stops']),
                    $driver
                )
                ->latest()
                ->get()
                ->map(function (PickupRequest $row) use ($driver) {
                    $payload = $row->toApiArray('driver');
                    $km = $this->matcher->distanceKm($driver, $row);
                    $payload['distance_km'] = $km !== null ? round($km, 2) : null;
                    $payload['nearby'] = $km !== null ? $km <= PickupRequestMatchingService::NEARBY_KM : false;
                    $payload['journey'] = $row->journeyApiArray();

                    return $payload;
                })
                ->sortBy(fn ($row) => $row['distance_km'] ?? 9999)
                ->values();

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
            $denied = $this->denyUnlessDriverReady($driver);
            if ($denied) {
                return $denied;
            }

            $q = PickupRequest::with(['parent', 'student', 'city', 'area', 'dropArea', 'vehicle', 'latestInvoice'])
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

            $requests = $q->latest()
                ->get()
                ->map(fn (PickupRequest $row) => $row->toApiArray('driver'))
                ->values();

            return $this->successResponse($requests, 'Accepted requests');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch accepted requests');
        }
    }

    public function accept(Request $request, PickupRequest $pickupRequest): JsonResponse
    {
        try {
            $driver = $request->user();
            $denied = $this->denyUnlessDriverReady($driver);
            if ($denied) {
                return $denied;
            }

            $updated = app(\App\Services\PickupRequestAssignmentService::class)
                ->assign($pickupRequest, $driver, 'driver');

            return $this->successResponse($updated->toApiArray('driver'), 'Request accepted. Customer must pay the advance fee before the shift can start.');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to accept request');
        }
    }

    public function reject(Request $request, PickupRequest $pickupRequest): JsonResponse
    {
        try {
            $driver = $request->user();
            $denied = $this->denyUnlessDriverReady($driver);
            if ($denied) {
                return $denied;
            }

            if ($pickupRequest->status !== 'pending' || $pickupRequest->driver_id !== null) {
                return $this->errorResponse('This request is no longer open for rejection', 422);
            }

            if (!$this->matcher->driverCanServe($driver, $pickupRequest)) {
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
            $denied = $this->denyUnlessDriverReady($driver);
            if ($denied) {
                return $denied;
            }

            if ((int) $pickupRequest->driver_id !== (int) $driver->id) {
                return $this->errorResponse('Not found', 404);
            }

            if (!$pickupRequest->isShiftPaid()) {
                return $this->errorResponse('Trip cannot start until the customer completes payment for this shift.', 422);
            }

            $validated = $request->validate([
                'status' => ['required', 'in:picked_up,dropped,completed'],
            ]);

            $run = app(\App\Services\ShiftDayService::class)->markRequestStage($pickupRequest, $validated['status']);

            return $this->successResponse([
                'request' => $pickupRequest->fresh(['parent', 'student', 'city', 'area', 'dropArea'])->toApiArray('driver'),
                'today' => $run->toApiArray(false),
            ], 'Today status updated');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to update request status');
        }
    }
}
