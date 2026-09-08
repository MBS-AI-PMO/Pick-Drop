<?php

namespace App\Services;

use App\Models\DriverPickupRequestRejection;
use App\Models\Invoice;
use App\Models\PickupRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PickupRequestAssignmentService
{
    public const MATCH_TIMEOUT_MINUTES = 20;

    public function __construct(
        private readonly PickupRequestMatchingService $matcher,
        private readonly InvoiceService $invoices,
        private readonly AppNotificationService $notifier
    ) {
    }

    public function markWaiting(PickupRequest $pickupRequest): void
    {
        $pickupRequest->update([
            'match_expires_at' => now()->addMinutes(self::MATCH_TIMEOUT_MINUTES),
            'auto_assign_attempts' => 0,
        ]);
    }

    public function assign(PickupRequest $pickupRequest, User $driver, string $source = 'driver'): PickupRequest
    {
        $updated = null;

        DB::transaction(function () use ($pickupRequest, $driver, $source, &$updated) {
            /** @var PickupRequest|null $row */
            $row = PickupRequest::query()
                ->lockForUpdate()
                ->whereKey($pickupRequest->id)
                ->first();

            if (!$row || $row->status !== 'pending' || $row->driver_id !== null) {
                throw new RuntimeException('This request is no longer available.');
            }

            if (!$this->matcher->driverCanServe($driver, $row)) {
                throw new RuntimeException('This driver cannot serve this request (city or area mismatch).');
            }

            $row->driver_id = $driver->id;
            $row->vehicle_id = $driver->assignedVehicle?->id;
            $row->status = 'accepted';
            $row->assignment_source = $source;
            $row->match_expires_at = null;
            $row->save();

            DriverPickupRequestRejection::query()
                ->where('driver_id', $driver->id)
                ->where('pickup_request_id', $row->id)
                ->delete();

            $updated = $row->fresh(['parent', 'student', 'city', 'area', 'dropArea', 'driver', 'vehicle']);
        });

        $invoice = $this->invoices->createForAcceptedShift($updated);
        $updated = $updated->fresh([
            'parent', 'student', 'city', 'area', 'dropArea', 'driver', 'vehicle',
            'latestInvoice.items', 'latestInvoice.payments',
        ]);

        $this->notifier->notifyParentRequestAccepted($updated);
        $this->notifier->notifyShiftPaymentRequired($updated, $invoice);

        if ($source === 'auto') {
            $this->notifier->notify(
                (int) $updated->driver_id,
                'pickup_request_auto_assigned',
                'Request auto-assigned',
                sprintf('Pickup request #%d was assigned to you because no driver accepted in time.', $updated->id),
                ['pickup_request_id' => $updated->id]
            );
        }

        if ($source === 'admin') {
            $this->notifier->notify(
                (int) $updated->driver_id,
                'pickup_request_admin_assigned',
                'Request assigned by admin',
                sprintf('Admin assigned pickup request #%d to you.', $updated->id),
                ['pickup_request_id' => $updated->id]
            );
        }

        return $updated;
    }

    /**
     * Auto-assign is disabled: requests stay visible to matching drivers
     * until a driver explicitly accepts.
     */
    public function autoAssignExpired(): int
    {
        return 0;
    }

    private function notifyOthersTaken(PickupRequest $pickupRequest, User $acceptedBy): void
    {
        $this->matcher->eligibleDrivers($pickupRequest, false)
            ->where('id', '!=', (int) $acceptedBy->id)
            ->each(function (User $driver) use ($pickupRequest) {
                $this->notifier->notify(
                    $driver->id,
                    'pickup_request_taken',
                    'Request taken',
                    sprintf('Pickup request #%d was accepted by another driver.', $pickupRequest->id),
                    ['pickup_request_id' => $pickupRequest->id]
                );
            });
    }

    private function extendWait(PickupRequest $request, string $reason): void
    {
        $attempts = (int) $request->auto_assign_attempts + 1;

        $request->update([
            'auto_assign_attempts' => $attempts,
            'match_expires_at' => now()->addMinutes(self::MATCH_TIMEOUT_MINUTES),
        ]);

        $this->notifier->notifyAdminPanel(
            'Pickup request still waiting',
            sprintf('Request #%d is still unassigned (%s). Attempt %d.', $request->id, $reason, $attempts),
            'warning'
        );
    }
}
