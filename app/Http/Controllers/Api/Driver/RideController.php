<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Api\Driver\BaseApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class RideController extends BaseApiController
{
    public function today(Request $request): JsonResponse
    {
        try {
            // TODO: Replace with real "today's rides" query
            $rides = [];

            return $this->successResponse($rides, "Today's rides");
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch today rides');
        }
    }

    public function index(Request $request): JsonResponse
    {
        try {
            // TODO: Replace with history query / pagination
            $rides = [];

            return $this->successResponse($rides, 'Rides history');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch rides');
        }
    }

    public function markPickup(Request $request, int $rideId): JsonResponse
    {
        try {
            // TODO: mark specific student/stop pickup done

            return $this->successResponse([
                'ride_id' => $rideId,
            ], 'Pickup marked as done');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to mark pickup');
        }
    }

    public function markDrop(Request $request, int $rideId): JsonResponse
    {
        try {
            // TODO: mark drop done

            return $this->successResponse([
                'ride_id' => $rideId,
            ], 'Drop marked as done');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to mark drop');
        }
    }

    public function updateLocation(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'lat' => ['required', 'numeric', 'between:-90,90'],
                'lng' => ['required', 'numeric', 'between:-180,180'],
            ]);

            // TODO: Persist driver current location (e.g., in drivers table or separate table)

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
            $validated = $request->validate([
                'status' => ['required', 'string'], // e.g. on_the_way, picked_all, dropped_all
            ]);

            // TODO: Save driver/route status

            return $this->successResponse($validated, 'Status updated');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to update status');
        }
    }
}

