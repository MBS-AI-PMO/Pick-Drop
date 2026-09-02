<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Api\Driver\BaseApiController;
use App\Models\IssueReport;
use App\Models\PickupRequest;
use App\Services\AppNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class IssueController extends BaseApiController
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
                'route_id' => ['nullable', 'integer'],
                'type' => ['required', 'string', 'max:50'],
                'reason' => ['nullable', 'string', 'max:1000'],
                'eta_change' => ['nullable', 'integer', 'min:1', 'max:180'],
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

            $type = strtolower($validated['type']);
            $issue = IssueReport::create([
                'user_id' => $driver->id,
                'pickup_request_id' => $pickupRequest?->id,
                'type' => $type,
                'eta_minutes' => $validated['eta_change'] ?? null,
                'reporter_role' => 'driver',
                'subject' => ucfirst($type),
                'description' => $validated['reason'] ?? null,
                'status' => 'open',
            ]);

            if ($pickupRequest && $type === 'delay') {
                $eta = (int) ($validated['eta_change'] ?? 10);
                app(AppNotificationService::class)->notifyDelay(
                    $pickupRequest,
                    $eta,
                    (string) ($validated['reason'] ?? '')
                );
                $pickupRequest->update(['last_delay_notified_on' => now()->toDateString()]);
            } elseif ($pickupRequest && in_array($type, ['breakdown', 'unavailable'], true)) {
                $cover = app(\App\Services\CoverService::class)->open(
                    $pickupRequest,
                    $type,
                    now()->toDateString(),
                    $validated['reason'] ?? null
                );
                $issue->pickup_request_id = $pickupRequest->id;
                $issue->save();

                return $this->successResponse([
                    'issue' => $issue->fresh()->toApiArray(),
                    'cover' => $cover->toApiArray(),
                ], 'Issue submitted. Nearby drivers are being offered this trip.', 201);
            } elseif ($pickupRequest) {
                app(AppNotificationService::class)->notify(
                    (int) $pickupRequest->parent_id,
                    'driver_issue',
                    'Driver reported an issue',
                    sprintf('%s: %s', ucfirst($type), $validated['reason'] ?? 'Please check the app.'),
                    ['pickup_request_id' => $pickupRequest->id, 'issue_id' => $issue->id]
                );
            }

            app(AppNotificationService::class)->notifyAdminPanel(
                'Driver issue',
                sprintf('%s reported %s on request #%s.', $driver->name, $type, $pickupRequest?->id ?? 'n/a'),
                'warning'
            );

            return $this->successResponse($issue->toApiArray(), 'Issue submitted', 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to submit issue');
        }
    }

    public function today(Request $request): JsonResponse
    {
        try {
            $driver = $request->user();
            $denied = $this->denyUnlessDriverReady($driver);
            if ($denied) {
                return $denied;
            }

            $issues = IssueReport::query()
                ->where('user_id', $driver->id)
                ->whereDate('created_at', now()->toDateString())
                ->latest()
                ->get()
                ->map->toApiArray()
                ->values();

            return $this->successResponse($issues, "Today's issues");
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch issues');
        }
    }
}
