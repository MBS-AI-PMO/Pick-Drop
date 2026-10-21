<?php

namespace App\Http\Controllers\Api\Driver;

use App\Models\PickupRequest;
use App\Models\SosAlert;
use App\Services\AppNotificationService;
use App\Services\ShiftDayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class SosController extends BaseApiController
{
    public function store(Request $request): JsonResponse
    {
        try {
            $driver = $request->user();
            $denied = $this->denyUnlessDriverReady($driver);
            if ($denied) {
                return $denied;
            }

            $validated = $request->validate([
                'pickup_request_id' => ['nullable', 'integer', 'exists:pickup_requests,id'],
                'type' => ['nullable', 'in:accident,medical,breakdown,security'],
                'lat' => ['nullable', 'numeric', 'between:-90,90'],
                'lng' => ['nullable', 'numeric', 'between:-180,180'],
                'message' => ['nullable', 'string', 'max:500'],
            ]);

            $pickupRequest = null;
            if (!empty($validated['pickup_request_id'])) {
                $pickupRequest = PickupRequest::query()
                    ->where('id', $validated['pickup_request_id'])
                    ->where('driver_id', $driver->id)
                    ->first();

                if (!$pickupRequest) {
                    return $this->errorResponse('Invalid pickup_request_id', 422);
                }
            }

            $type = $validated['type'] ?? null;
            $details = trim((string) ($validated['message'] ?? ''));
            $message = $type
                ? trim(ucfirst($type) . ($details !== '' ? ': ' . $details : ''))
                : ($details !== '' ? $details : 'Driver SOS');

            $alert = SosAlert::create([
                'user_id' => $driver->id,
                'pickup_request_id' => $pickupRequest?->id,
                'type' => $type,
                'lat' => $validated['lat'] ?? $driver->last_lat,
                'lng' => $validated['lng'] ?? $driver->last_lng,
                'message' => $message,
                'status' => SosAlert::OPEN,
            ]);

            app(ShiftDayService::class)->lockTripForEmergency($driver);

            $notice = $message . ' Admin needs to end this trip. The driver cannot end it.';

            if ($pickupRequest) {
                app(AppNotificationService::class)->notifySos($pickupRequest, $driver, $notice);
            } else {
                app(AppNotificationService::class)->notifyAdminPanel(
                    'Driver SOS',
                    sprintf('%s sent an SOS. %s', $driver->name ?? 'A driver', $notice),
                    'danger'
                );
            }

            return $this->successResponse($alert->toApiArray(), 'SOS sent', 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to send SOS');
        }
    }
}
