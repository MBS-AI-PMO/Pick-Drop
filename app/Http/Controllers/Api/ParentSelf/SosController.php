<?php

namespace App\Http\Controllers\Api\ParentSelf;

use App\Models\PickupRequest;
use App\Models\SosAlert;
use App\Services\AppNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class SosController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        try {
            $alerts = SosAlert::query()
                ->where('user_id', $request->user()->id)
                ->latest()
                ->paginate(\App\Support\AppPagination::PER_PAGE);

            $alerts->getCollection()->transform(fn (SosAlert $row) => $row->toApiArray());

            return $this->successResponse($alerts, 'SOS alerts');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch SOS alerts');
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'pickup_request_id' => ['nullable', 'integer', 'exists:pickup_requests,id'],
                'lat' => ['nullable', 'numeric', 'between:-90,90'],
                'lng' => ['nullable', 'numeric', 'between:-180,180'],
                'message' => ['nullable', 'string', 'max:500'],
            ]);

            $pickupRequest = null;
            if (!empty($validated['pickup_request_id'])) {
                $pickupRequest = PickupRequest::query()
                    ->where('id', $validated['pickup_request_id'])
                    ->where('parent_id', $request->user()->id)
                    ->first();

                if (!$pickupRequest) {
                    return $this->errorResponse('Invalid pickup_request_id', 422);
                }
            }

            $alert = SosAlert::create([
                'user_id' => $request->user()->id,
                'pickup_request_id' => $pickupRequest?->id,
                'lat' => $validated['lat'] ?? $request->user()->last_lat,
                'lng' => $validated['lng'] ?? $request->user()->last_lng,
                'message' => $validated['message'] ?? 'Emergency SOS',
                'status' => SosAlert::OPEN,
            ]);

            if ($pickupRequest) {
                app(AppNotificationService::class)->notifySos(
                    $pickupRequest,
                    $request->user(),
                    (string) ($validated['message'] ?? '')
                );
            } else {
                app(AppNotificationService::class)->notifyAdminPanel(
                    'SOS alert',
                    sprintf('%s sent an SOS.', $request->user()->name ?? 'A user'),
                    'danger'
                );
            }

            return $this->successResponse($alert->toApiArray(), 'SOS sent. Admin and related users have been notified.', 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to send SOS');
        }
    }
}
