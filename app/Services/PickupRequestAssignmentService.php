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
        $this->notifyOthersTaken($updated, $driver);

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

    public function autoAssignExpired(): int
    {
        PickupRequest::query()
            ->where('status', 'pending')
            ->whereNull('driver_id')
            ->whereNull('match_expires_at')
            ->update([
                'match_expires_at' => now()->addMinutes(self::MATCH_TIMEOUT_MINUTES),
            ]);

        $assigned = 0;

        $expired = PickupRequest::query()
            ->where('status', 'pending')
            ->whereNull('driver_id')
            ->whereNotNull('match_expires_at')
            ->where('match_expires_at', '<=', now())
            ->get();

        foreach ($expired as $request) {
            $driver = $this->matcher->eligibleDrivers($request)->first();

            if ($driver) {
                try {
                    $this->assign($request, $driver, 'auto');
                    $assigned++;
                } catch (RuntimeException $e) {
                    $this->extendWait($request, 'Auto-assign failed: ' . $e->getMessage());
                }

                continue;
            }

            $this->extendWait($request, 'No eligible driver found after timeout.');
        }

        return $assigned;
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
