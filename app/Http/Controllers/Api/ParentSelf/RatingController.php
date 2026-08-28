<?php

namespace App\Http\Controllers\Api\ParentSelf;

use App\Models\PickupRequest;
use App\Models\Rating;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class RatingController extends BaseApiController
{
    public function index(Request $request, int $requestId): JsonResponse
    {
        try {
            $pickupRequest = $this->ownedRequest($request, $requestId);
            if ($pickupRequest instanceof JsonResponse) {
                return $pickupRequest;
            }

            $ratings = Rating::query()
                ->with(['fromUser', 'toUser'])
                ->where('pickup_request_id', $pickupRequest->id)
                ->latest()
                ->get()
                ->map->toApiArray()
                ->values();

            return $this->successResponse($ratings, 'Ratings');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch ratings');
        }
    }

    public function store(Request $request, int $requestId): JsonResponse
    {
        try {
            $pickupRequest = $this->ownedRequest($request, $requestId);
            if ($pickupRequest instanceof JsonResponse) {
                return $pickupRequest;
            }

            if (!in_array($pickupRequest->status, ['dropped', 'completed'], true)) {
                return $this->errorResponse('You can rate after the trip is dropped or completed.', 422);
            }

            if (!$pickupRequest->driver_id) {
                return $this->errorResponse('No driver assigned to rate.', 422);
            }

            $validated = $request->validate([
                'rating' => ['required', 'integer', 'min:1', 'max:5'],
                'comment' => ['nullable', 'string', 'max:500'],
            ]);

            $row = Rating::query()->updateOrCreate(
                [
                    'pickup_request_id' => $pickupRequest->id,
                    'from_user_id' => $request->user()->id,
                    'to_user_id' => $pickupRequest->driver_id,
                ],
                [
                    'rating' => $validated['rating'],
                    'comment' => $validated['comment'] ?? null,
                ]
            );

            return $this->successResponse($row->fresh(['fromUser', 'toUser'])->toApiArray(), 'Rating saved');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to save rating');
        }
    }

    private function ownedRequest(Request $request, int $requestId): PickupRequest|JsonResponse
    {
        $pickupRequest = PickupRequest::query()
            ->where('id', $requestId)
            ->where('parent_id', $request->user()->id)
            ->first();

        if (!$pickupRequest) {
            return $this->errorResponse('Not found', 404);
        }

        return $pickupRequest;
    }
}
