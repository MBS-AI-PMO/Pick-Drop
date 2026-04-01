<?php

namespace App\Http\Controllers\Api\ParentSelf;

use App\Models\PickupRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class TripController extends BaseApiController
{
    public function recent(Request $request): JsonResponse
    {
        try {
            $trips = PickupRequest::with(['student', 'city', 'area', 'driver', 'vehicle'])
                ->where('parent_id', $request->user()->id)
                ->orderByDesc('id')
                ->paginate(20);

            return $this->successResponse($trips, 'Recent trips');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch trips');
        }
    }

    public function todayStatus(Request $request): JsonResponse
    {
        try {
            $today = now()->toDateString();
            $trip = PickupRequest::with(['student', 'driver', 'vehicle'])
                ->where('parent_id', $request->user()->id)
                ->whereDate('created_at', $today)
                ->orderByDesc('id')
                ->first();

            return $this->successResponse([
                'trip' => $trip,
                'status' => $trip?->status,
            ], 'Today status');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch today status');
        }
    }
}

