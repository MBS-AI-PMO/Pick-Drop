<?php

namespace App\Http\Controllers\Api\Driver;

use App\Models\PickupRequest;
use App\Models\Rating;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class RatingController extends BaseApiController
{
    public function store(Request $request, PickupRequest $pickupRequest): JsonResponse
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

            if (!in_array($pickupRequest->status, ['dropped', 'completed'], true)) {
                return $this->errorResponse('You can rate after the trip is dropped or completed.', 422);
            }

            $validated = $request->validate([
                'rating' => ['required', 'integer', 'min:1', 'max:5'],
                'comment' => ['nullable', 'string', 'max:500'],
            ]);

            $row = Rating::query()->updateOrCreate(
                [
                    'pickup_request_id' => $pickupRequest->id,
                    'from_user_id' => $driver->id,
                    'to_user_id' => $pickupRequest->parent_id,
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
}
