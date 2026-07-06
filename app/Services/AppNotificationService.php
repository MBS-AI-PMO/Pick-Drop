<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\NotificationPreference;
use App\Models\PickupRequest;
use App\Models\User;
use Illuminate\Support\Collection;

class AppNotificationService
{
    public function notify(
        int $userId,
        string $type,
        string $title,
        string $body,
        ?array $data = null,
        ?string $preferenceKey = null
    ): ?AppNotification {
        if ($preferenceKey !== null && !$this->userAllows($userId, $preferenceKey)) {
            return null;
        }

        return AppNotification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'data' => $data,
        ]);
    }

    public function notifyParentRequestSubmitted(PickupRequest $pickupRequest): void
    {
        $pickupRequest->loadMissing('area');

        $this->notify(
            (int) $pickupRequest->parent_id,
            'pickup_request_submitted',
            'Request submitted',
            sprintf(
                'Your pickup request for %s is waiting for a driver.',
                $pickupRequest->area?->name ?? 'the selected area'
            ),
            $this->requestData($pickupRequest)
        );
    }

    public function notifyDriversOfNewPickupRequest(PickupRequest $pickupRequest): void
    {
        $pickupRequest->loadMissing(['area']);

        $areaName = $pickupRequest->area?->name ?? 'your area';

        $this->notifyEligibleDrivers(
            $pickupRequest,
            'new_pickup_request',
            'New pickup request',
            sprintf('A new pickup request is available in %s.', $areaName)
        );
    }

    public function notifyParentRequestUpdated(PickupRequest $pickupRequest): void
    {
        $this->notify(
            (int) $pickupRequest->parent_id,
            'pickup_request_updated',
            'Request updated',
            'Your pickup request details were updated.',
            $this->requestData($pickupRequest)
        );

        if ($pickupRequest->driver_id) {
            $this->notify(
                (int) $pickupRequest->driver_id,
                'pickup_request_updated',
                'Request updated',
                'The parent updated a pickup request assigned to you.',
                $this->requestData($pickupRequest)
            );
        }
    }

    public function notifyParentRequestAccepted(PickupRequest $pickupRequest): void
    {
        $pickupRequest->loadMissing('driver');

        $driverName = $pickupRequest->driver?->name ?? 'A driver';

        $this->notify(
            (int) $pickupRequest->parent_id,
            'pickup_request_accepted',
            'Driver assigned',
            sprintf('%s accepted your pickup request.', $driverName),
            $this->requestData($pickupRequest, ['driver_id' => $pickupRequest->driver_id])
        );

        $this->notifyEligibleDrivers(
            $pickupRequest,
            'pickup_request_taken',
            'Request no longer available',
            'This pickup request was accepted by another driver.',
            $pickupRequest->driver_id
        );
    }

    public function notifyParentDriverRejected(User $driver, PickupRequest $pickupRequest): void
    {
        $this->notify(
            (int) $pickupRequest->parent_id,
            'pickup_request_rejected',
            'Driver declined',
            sprintf('%s declined your pickup request.', $driver->name ?? 'A driver'),
            $this->requestData($pickupRequest, ['driver_id' => $driver->id])
        );
    }

    public function notifyDriverRejectedConfirmation(User $driver, PickupRequest $pickupRequest): void
    {
        $this->notify(
            $driver->id,
            'pickup_request_reject_confirmed',
            'Request declined',
            'You declined this pickup request. It will no longer appear in your available list.',
            $this->requestData($pickupRequest)
        );
    }

    public function notifyPickupRequestCancelled(PickupRequest $pickupRequest): void
    {
        $pickupRequest->loadMissing('parent');

        $this->notify(
            (int) $pickupRequest->parent_id,
            'pickup_request_cancelled',
            'Request cancelled',
            'Your pickup request has been cancelled.',
            $this->requestData($pickupRequest)
        );

        if ($pickupRequest->driver_id) {
            $this->notify(
                (int) $pickupRequest->driver_id,
                'pickup_request_cancelled',
                'Request cancelled',
                sprintf(
                    '%s cancelled a pickup request assigned to you.',
                    $pickupRequest->parent?->name ?? 'The parent'
                ),
                $this->requestData($pickupRequest)
            );

            return;
        }

        $this->notifyEligibleDrivers(
            $pickupRequest,
            'pickup_request_cancelled',
            'Request cancelled',
            'A pickup request in your service area was cancelled by the parent.'
        );
    }

    public function notifyPickupRequestStatus(PickupRequest $pickupRequest, string $status): void
    {
        $messages = [
            'picked_up' => [
                'title' => 'Student picked up',
                'parent_body' => 'Your child has been picked up.',
                'driver_body' => 'Pickup marked as completed for this request.',
            ],
            'dropped' => [
                'title' => 'Student dropped off',
                'parent_body' => 'Your child has been dropped off.',
                'driver_body' => 'Drop-off marked as completed for this request.',
            ],
            'completed' => [
                'title' => 'Trip completed',
                'parent_body' => 'The pickup trip has been completed.',
                'driver_body' => 'You completed this pickup trip.',
            ],
        ];

        if (!isset($messages[$status])) {
            return;
        }

        $info = $messages[$status];
        $data = $this->requestData($pickupRequest, ['status' => $status]);

        $this->notify(
            (int) $pickupRequest->parent_id,
            'pickup_request_status',
            $info['title'],
            $info['parent_body'],
            $data
        );

        if ($pickupRequest->driver_id) {
            $this->notify(
                (int) $pickupRequest->driver_id,
                'pickup_request_status',
                $info['title'],
                $info['driver_body'],
                $data
            );
        }
    }

    public function notifyNewMessage(int $receiverId, int $pickupRequestId, string $senderName, string $preview): void
    {
        $preview = mb_strlen($preview) > 120 ? mb_substr($preview, 0, 117) . '...' : $preview;

        $this->notify(
            $receiverId,
            'new_message',
            'New message',
            sprintf('%s: %s', $senderName, $preview),
            ['pickup_request_id' => $pickupRequestId],
            'new_messages'
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function requestData(PickupRequest $pickupRequest, array $extra = []): array
    {
        return array_merge([
            'pickup_request_id' => $pickupRequest->id,
            'status' => $pickupRequest->status,
            'area_id' => $pickupRequest->area_id,
            'city_id' => $pickupRequest->city_id,
        ], $extra);
    }

    /**
     * @return Collection<int, User>
     */
    private function eligibleDriversForRequest(PickupRequest $pickupRequest): Collection
    {
        $areaId = (int) $pickupRequest->area_id;

        return User::query()
            ->where('role', 'driver')
            ->when($pickupRequest->city_id, fn ($q) => $q->where('city_id', $pickupRequest->city_id))
            ->whereNotNull('service_areas')
            ->get()
            ->filter(function (User $driver) use ($areaId) {
                $areas = collect($driver->service_areas ?? [])
                    ->map(fn ($id) => (int) $id)
                    ->all();

                return in_array($areaId, $areas, true);
            })
            ->values();
    }

    private function notifyEligibleDrivers(
        PickupRequest $pickupRequest,
        string $type,
        string $title,
        string $body,
        ?int $exceptDriverId = null
    ): void {
        $this->eligibleDriversForRequest($pickupRequest)
            ->when($exceptDriverId !== null, fn (Collection $drivers) => $drivers->where('id', '!=', $exceptDriverId))
            ->each(function (User $driver) use ($pickupRequest, $type, $title, $body) {
                $this->notify(
                    $driver->id,
                    $type,
                    $title,
                    $body,
                    $this->requestData($pickupRequest)
                );
            });
    }

    private function userAllows(int $userId, string $preferenceKey): bool
    {
        $prefs = NotificationPreference::query()->where('user_id', $userId)->first();

        if (!$prefs) {
            return true;
        }

        $flag = $prefs->{$preferenceKey} ?? true;

        return (bool) $flag;
    }
}
