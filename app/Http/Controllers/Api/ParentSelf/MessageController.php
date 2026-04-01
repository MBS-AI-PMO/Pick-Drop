<?php

namespace App\Http\Controllers\Api\ParentSelf;

use App\Models\DriverMessage;
use App\Models\PickupRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class MessageController extends BaseApiController
{
    public function index(Request $request, PickupRequest $pickupRequest): JsonResponse
    {
        try {
            if ($pickupRequest->parent_id !== $request->user()->id) {
                return $this->errorResponse('Not found', 404);
            }

            $messages = DriverMessage::where('pickup_request_id', $pickupRequest->id)
                ->orderBy('id')
                ->paginate(50);

            return $this->successResponse($messages, 'Messages');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch messages');
        }
    }

    public function send(Request $request, PickupRequest $pickupRequest): JsonResponse
    {
        try {
            if ($pickupRequest->parent_id !== $request->user()->id) {
                return $this->errorResponse('Not found', 404);
            }
            if (!$pickupRequest->driver_id) {
                return $this->errorResponse('Driver not assigned yet', 422);
            }

            $validated = $request->validate([
                'message' => ['required', 'string', 'max:2000'],
            ]);

            $msg = DriverMessage::create([
                'pickup_request_id' => $pickupRequest->id,
                'sender_id' => $request->user()->id,
                'receiver_id' => $pickupRequest->driver_id,
                'message' => $validated['message'],
            ]);

            return $this->successResponse($msg, 'Message sent', 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to send message');
        }
    }
}

