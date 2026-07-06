<?php

namespace App\Http\Controllers\Api\Driver;

use App\Models\DriverMessage;
use App\Models\PickupRequest;
use App\Services\AppNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class MessageController extends BaseApiController
{
    public function index(Request $request, PickupRequest $pickupRequest): JsonResponse
    {
        try {
            if ((int) $pickupRequest->driver_id !== (int) $request->user()->id) {
                return $this->errorResponse('Not found', 404);
            }

            $messages = DriverMessage::with(['sender', 'receiver'])
                ->where('pickup_request_id', $pickupRequest->id)
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
            $driver = $request->user();

            if ((int) $pickupRequest->driver_id !== (int) $driver->id) {
                return $this->errorResponse('Not found', 404);
            }

            $validated = $request->validate([
                'message' => ['required', 'string', 'max:2000'],
            ]);

            $msg = DriverMessage::create([
                'pickup_request_id' => $pickupRequest->id,
                'sender_id' => $driver->id,
                'receiver_id' => $pickupRequest->parent_id,
                'message' => $validated['message'],
            ]);

            app(AppNotificationService::class)->notifyNewMessage(
                (int) $pickupRequest->parent_id,
                $pickupRequest->id,
                $driver->name ?? 'Driver',
                $validated['message']
            );

            return $this->successResponse($msg->load(['sender', 'receiver']), 'Message sent', 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to send message');
        }
    }
}
