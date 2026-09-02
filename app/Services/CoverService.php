<?php

namespace App\Services;

use App\Models\PickupRequest;
use App\Models\ShiftAttendance;
use App\Models\ShiftReplacement;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CoverService
{
    public function __construct(
        private readonly PickupRequestMatchingService $matcher,
        private readonly AttendanceService $attendance,
        private readonly AppNotificationService $notifier
    ) {
    }

    public function open(
        PickupRequest $pickupRequest,
        string $reason,
        ?string $date = null,
        ?string $notes = null
    ): ShiftReplacement {
        if (!$pickupRequest->driver_id) {
            throw new RuntimeException('This request has no assigned driver.');
        }

        $day = $date ?: now()->toDateString();

        $row = ShiftReplacement::query()->firstOrCreate(
            [
                'pickup_request_id' => $pickupRequest->id,
                'date' => $day,
            ],
            [
                'original_driver_id' => $pickupRequest->driver_id,
                'original_vehicle_id' => $pickupRequest->vehicle_id,
                'reason' => $reason,
                'status' => ShiftReplacement::OPEN,
                'notes' => $notes,
            ]
        );

        if (!$row->isOpen() && $row->status !== ShiftReplacement::OPEN) {
            return $row;
        }

        $row->update([
            'reason' => $reason,
            'notes' => $notes ?: $row->notes,
            'status' => ShiftReplacement::OPEN,
        ]);

        $this->notifier->notify(
            (int) $pickupRequest->parent_id,
            'driver_cover_needed',
            'Alternative driver needed',
            sprintf(
                'Your driver cannot complete %s today. Nearby drivers are being offered this trip.',
                $pickupRequest->student?->name ?: 'this pickup'
            ),
            ['pickup_request_id' => $pickupRequest->id, 'date' => $day, 'reason' => $reason]
        );

        $this->broadcast($pickupRequest, $row);

        $this->notifier->notifyAdminPanel(
            'Alternative driver needed',
            sprintf('Request #%d needs cover on %s (%s).', $pickupRequest->id, $day, $reason),
            'warning'
        );

        return $row->fresh(['pickupRequest', 'originalDriver']);
    }

    public function availableForDriver(User $driver): Collection
    {
        return ShiftReplacement::query()
            ->with(['pickupRequest.parent', 'pickupRequest.student', 'pickupRequest.city', 'pickupRequest.area', 'pickupRequest.stops'])
            ->where('status', ShiftReplacement::OPEN)
            ->whereDate('date', '>=', now()->toDateString())
            ->where('original_driver_id', '!=', $driver->id)
            ->get()
            ->filter(function (ShiftReplacement $row) use ($driver) {
                $request = $row->pickupRequest;
                if (!$request) {
                    return false;
                }

                if (!$this->matcher->driverIsEligible($driver)) {
                    return false;
                }

                $cityId = $driver->driverCityId();
                if (!$cityId || (int) $request->city_id !== $cityId) {
                    return false;
                }

                $overlap = array_intersect(
                    $this->matcher->serviceAreaIds($driver),
                    $this->matcher->requestAreaIds($request)
                );

                return $overlap !== [];
            })
            ->values();
    }

    public function accept(ShiftReplacement $replacement, User $driver): ShiftReplacement
    {
        return DB::transaction(function () use ($replacement, $driver) {
            $row = ShiftReplacement::query()->lockForUpdate()->findOrFail($replacement->id);
            if (!$row->isOpen() || $row->replacement_driver_id) {
                throw new RuntimeException('This cover request is no longer available.');
            }

            $request = $row->pickupRequest;
            if (!$request || !$this->matcher->driverIsEligible($driver)) {
                throw new RuntimeException('You cannot cover this request.');
            }

            $row->update([
                'replacement_driver_id' => $driver->id,
                'replacement_vehicle_id' => $driver->assignedVehicle?->id,
                'status' => ShiftReplacement::ACCEPTED,
                'accepted_at' => now(),
            ]);

            $this->notifier->notify(
                (int) $request->parent_id,
                'driver_cover_accepted',
                'Alternative driver assigned',
                sprintf('%s will cover today\'s pickup.', $driver->name),
                ['pickup_request_id' => $request->id, 'replacement_id' => $row->id]
            );

            $this->notifier->notify(
                (int) $row->original_driver_id,
                'driver_cover_accepted',
                'Cover driver found',
                sprintf('%s will handle request #%d today.', $driver->name, $request->id),
                ['pickup_request_id' => $request->id]
            );

            $this->matcher->eligibleDrivers($request)
                ->where('id', '!=', (int) $driver->id)
                ->where('id', '!=', (int) $row->original_driver_id)
                ->each(function (User $other) use ($request) {
                    $this->notifier->notify(
                        $other->id,
                        'cover_request_taken',
                        'Cover trip taken',
                        sprintf('Request #%d cover was accepted by another driver.', $request->id),
                        ['pickup_request_id' => $request->id]
                    );
                });

            return $row->fresh(['pickupRequest', 'replacementDriver']);
        });
    }

    public function assignAdmin(ShiftReplacement $replacement, User $driver): ShiftReplacement
    {
        if (!$replacement->isOpen()) {
            throw new RuntimeException('This cover request is already filled.');
        }

        return $this->accept($replacement, $driver);
    }

    public function closeUnfilledAndRefund(): int
    {
        if (now()->format('H:i') < '20:00') {
            return 0;
        }

        $open = ShiftReplacement::query()
            ->with('pickupRequest')
            ->where('status', ShiftReplacement::OPEN)
            ->whereDate('date', '<=', now()->toDateString())
            ->get();

        $count = 0;
        foreach ($open as $row) {
            $request = $row->pickupRequest;
            if (!$request) {
                continue;
            }

            $this->refundDay($request, $row->date->toDateString(), 'No alternative driver found for this day.');
            $row->update([
                'status' => ShiftReplacement::CLOSED,
                'closed_at' => now(),
            ]);
            $count++;
        }

        return $count;
    }

    public function refundDay(PickupRequest $pickupRequest, string $date, string $reason): ShiftAttendance
    {
        $attendance = $this->attendance->todayRecord($pickupRequest, $date)
            ?: $this->attendance->markAbsent($pickupRequest, $date);

        if ($attendance->refunded_at) {
            return $attendance;
        }

        $amount = $this->dailyCustomerRate($pickupRequest);
        if ($amount <= 0 || !$pickupRequest->parent_id) {
            return $attendance;
        }

        $parent = User::query()->find($pickupRequest->parent_id);
        if (!$parent) {
            return $attendance;
        }

        $parent->increment('referral_balance', $amount);
        WalletTransaction::query()->create([
            'user_id' => $parent->id,
            'amount' => $amount,
            'type' => 'credit',
            'reason' => 'driver_absent_refund',
            'pickup_request_id' => $pickupRequest->id,
        ]);

        $attendance->update([
            'refunded_amount' => $amount,
            'refunded_at' => now(),
            'refund_reason' => $reason,
            'status' => $attendance->status === ShiftAttendance::SKIPPED
                ? ShiftAttendance::SKIPPED
                : ShiftAttendance::ABSENT,
            'reason' => $reason,
        ]);

        $this->notifier->notify(
            (int) $parent->id,
            'day_refunded',
            'Day refunded to wallet',
            sprintf(
                'PKR %s was refunded for %s because the driver could not complete the trip.',
                number_format($amount, 2),
                Carbon::parse($date)->toFormattedDateString()
            ),
            ['pickup_request_id' => $pickupRequest->id, 'date' => $date, 'amount' => $amount]
        );

        return $attendance->fresh();
    }

    public function dailyCustomerRate(PickupRequest $pickupRequest): float
    {
        $start = $pickupRequest->shift_start_date?->copy() ?: now()->startOfMonth();
        $end = $pickupRequest->shift_end_date?->copy() ?: $start->copy()->endOfMonth();
        $working = app(DriverPayrollService::class)->workingDates($start, $end, $pickupRequest->days ?? []);
        $days = max(1, count($working));

        return round(((float) $pickupRequest->estimated_amount) / $days, 2);
    }

    public function driverForDate(PickupRequest $pickupRequest, ?string $date = null): ?User
    {
        $day = $date ?: now()->toDateString();
        $cover = ShiftReplacement::query()
            ->where('pickup_request_id', $pickupRequest->id)
            ->whereDate('date', $day)
            ->where('status', ShiftReplacement::ACCEPTED)
            ->first();

        if ($cover?->replacement_driver_id) {
            $cover->loadMissing('replacementDriver');

            return $cover->replacementDriver;
        }

        return $pickupRequest->driver;
    }

    private function broadcast(PickupRequest $pickupRequest, ShiftReplacement $replacement): void
    {
        $this->matcher->eligibleDrivers($pickupRequest)
            ->where('id', '!=', (int) $replacement->original_driver_id)
            ->each(function (User $driver) use ($pickupRequest, $replacement) {
                $this->notifier->notify(
                    $driver->id,
                    'cover_request',
                    'Cover trip available',
                    sprintf(
                        'A nearby pickup needs an alternative driver today. Request #%d.',
                        $pickupRequest->id
                    ),
                    [
                        'pickup_request_id' => $pickupRequest->id,
                        'replacement_id' => $replacement->id,
                        'date' => $replacement->date?->toDateString(),
                    ]
                );
            });
    }
}
