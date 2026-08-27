<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\Invoice;
use App\Models\Notification;
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
        $pickupRequest->loadMissing(['area', 'parent']);

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

        $this->notifyAdminPanel(
            $pickupRequest->typeLabel() . ' request pending',
            sprintf(
                '%s submitted pickup request #%d. Waiting for a driver.',
                $pickupRequest->requesterName(),
                $pickupRequest->id
            ),
            'info'
        );
    }

    public function notifyDriversOfNewPickupRequest(PickupRequest $pickupRequest): void
    {
        $pickupRequest->loadMissing(['area', 'city']);

        $areaName = $pickupRequest->area?->name ?? 'your area';
        $cityName = $pickupRequest->city?->name;

        $this->notifyEligibleDrivers(
            $pickupRequest,
            'new_pickup_request',
            'New pickup request',
            $cityName
                ? sprintf('A new pickup request is available in %s, %s.', $areaName, $cityName)
                : sprintf('A new pickup request is available in %s.', $areaName)
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

        $this->notifyAdminPanel(
            'Pickup request accepted',
            sprintf(
                '%s accepted request #%d from %s.',
                $driverName,
                $pickupRequest->id,
                $pickupRequest->requesterName()
            ),
            'success'
        );
    }

    public function notifyShiftPaymentRequired(PickupRequest $pickupRequest, Invoice $invoice): void
    {
        $this->notify(
            (int) $pickupRequest->parent_id,
            'shift_payment_required',
            'Payment required',
            sprintf(
                'Pay %s in advance to start your %d-month pick-drop service. Bank details are in the invoice.',
                $invoice->formattedTotal(),
                (int) ($pickupRequest->duration_months ?: 1)
            ),
            $this->requestData($pickupRequest, [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'next_step' => 'pay_invoice',
            ])
        );

        if ($pickupRequest->driver_id) {
            $this->notify(
                (int) $pickupRequest->driver_id,
                'shift_awaiting_payment',
                'Waiting for payment',
                'The customer must pay the advance monthly fee before this shift can start.',
                $this->requestData($pickupRequest, ['invoice_id' => $invoice->id])
            );
        }

        $this->notifyAdminPanel(
            'Shift payment pending',
            sprintf(
                '%s must pay %s for request #%d before the shift starts.',
                $pickupRequest->requesterName(),
                $invoice->formattedTotal(),
                $pickupRequest->id
            ),
            'warning'
        );
    }

    public function notifyShiftPaymentReceived(PickupRequest $pickupRequest, Invoice $invoice): void
    {
        $this->notify(
            (int) $pickupRequest->parent_id,
            'shift_payment_received',
            'Payment received',
            'Your monthly service is now active. The driver can start pickup and drop.',
            $this->requestData($pickupRequest, [
                'invoice_id' => $invoice->id,
                'next_step' => 'shift_active',
            ])
        );

        if ($pickupRequest->driver_id) {
            $this->notify(
                (int) $pickupRequest->driver_id,
                'shift_payment_received',
                'Payment received',
                sprintf(
                    '%s paid for request #%d. You can start the shift.',
                    $pickupRequest->requesterName(),
                    $pickupRequest->id
                ),
                $this->requestData($pickupRequest, ['invoice_id' => $invoice->id])
            );
        }

        $this->notifyAdminPanel(
            'Shift payment received',
            sprintf(
                'Request #%d is paid. Shift can start.',
                $pickupRequest->id
            ),
            'success'
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
        } else {
            $this->notifyEligibleDrivers(
                $pickupRequest,
                'pickup_request_cancelled',
                'Request cancelled',
                'A pickup request in your service area was cancelled by the parent.'
            );
        }

        $this->notifyAdminPanel(
            'Pickup request cancelled',
            sprintf(
                '%s cancelled pickup request #%d.',
                $pickupRequest->requesterName(),
                $pickupRequest->id
            ),
            'warning'
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

        if ($status === 'completed') {
            $this->notifyAdminPanel(
                'Trip completed',
                sprintf(
                    'Request #%d from %s was completed.',
                    $pickupRequest->id,
                    $pickupRequest->requesterName()
                ),
                'success'
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
            'drop_area_id' => $pickupRequest->drop_area_id,
            'city_id' => $pickupRequest->city_id,
        ], $extra);
    }

    /**
     * @return Collection<int, User>
     */
    private function eligibleDriversForRequest(PickupRequest $pickupRequest): Collection
    {
        return app(PickupRequestMatchingService::class)->eligibleDrivers($pickupRequest);
    }

    private function notifyAdminPanel(string $title, string $message, string $type = 'info'): void
    {
        Notification::create([
            'title' => $title,
            'message' => $message,
            'type' => $type,
        ]);
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
