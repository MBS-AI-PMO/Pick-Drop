<?php

namespace App\Services;

use App\Models\PickupRequest;
use App\Models\PlatformSetting;
use App\Models\ShiftAttendance;
use App\Models\User;
use RuntimeException;

class CancellationService
{
    /**
     * @return array<string, mixed>
     */
    public function preview(PickupRequest $pickupRequest): array
    {
        $settings = PlatformSetting::current();
        $hours = (int) $settings->cancel_hours;
        $percent = (float) $settings->cancel_fee_percent;
        $starts = $pickupRequest->shift_start_date?->copy()->setTimeFromTimeString(substr((string) $pickupRequest->pickup_time, 0, 5) . ':00');
        $hoursLeft = $starts ? now()->diffInHours($starts, false) : $hours;
        $withinWindow = $pickupRequest->isShiftPaid()
            && in_array($pickupRequest->status, ['accepted', 'pending'], true)
            && $hoursLeft < $hours
            && $hoursLeft >= 0;

        $base = (float) ($pickupRequest->latestInvoice?->total ?? $pickupRequest->estimated_amount ?? 0);
        $fee = $withinWindow ? round($base * ($percent / 100), 2) : 0.0;

        $completedDays = (int) $pickupRequest->attendances()->where('status', ShiftAttendance::PRESENT)->count();
        $skippedDays = (int) $pickupRequest->attendances()->where('status', ShiftAttendance::SKIPPED)->count();
        $holidayDays = (int) $pickupRequest->attendances()->where('status', ShiftAttendance::HOLIDAY)->count();
        $expectedDays = max(0, (int) ($pickupRequest->trip_count ?: 0));
        if (($pickupRequest->round_trip !== false) && $expectedDays > 0) {
            $expectedDays = (int) ceil($expectedDays / 2);
        }
        $remainingDays = max(0, $expectedDays - $completedDays - $skippedDays - $holidayDays);

        return [
            'allowed' => !in_array($pickupRequest->status, ['picked_up', 'dropped', 'completed'], true),
            'booking_status' => $pickupRequest->status,
            'payment_status' => $pickupRequest->payment_status ?: PickupRequest::PAYMENT_UNPAID,
            'cancel_hours' => $hours,
            'fee_percent' => $percent,
            'fee' => $fee,
            'cancellation_charge' => $fee,
            'within_window' => $withinWindow,
            'hours_left' => (int) $hoursLeft,
            'completed_days' => $completedDays,
            'skipped_days' => $skippedDays,
            'holiday_days' => $holidayDays,
            'remaining_days' => $remainingDays,
            'refund_amount' => 0,
            'cancelled_by_role' => $pickupRequest->cancelled_by_role,
        ];
    }

    public function cancel(PickupRequest $pickupRequest, User $by): PickupRequest
    {
        if (in_array($pickupRequest->status, ['picked_up', 'dropped', 'completed'], true)) {
            throw new RuntimeException('Request cannot be cancelled after today\'s trip started.');
        }

        $preview = $this->preview($pickupRequest);
        $pickupRequest->status = 'cancelled';
        $pickupRequest->cancelled_at = now();
        $pickupRequest->cancelled_by = $by->id;
        $pickupRequest->cancelled_by_role = $this->actorRole($by);
        $pickupRequest->cancellation_fee = $preview['fee'];
        $pickupRequest->save();

        app(InvoiceService::class)->cancelOpenShiftInvoice($pickupRequest);

        if ($preview['fee'] > 0) {
            app(InvoiceService::class)->create([
                'user_id' => $pickupRequest->parent_id,
                'student_id' => $pickupRequest->student_id,
                'pickup_request_id' => $pickupRequest->id,
                'kind' => 'cancellation',
                'notes' => 'Cancellation fee for request #' . $pickupRequest->id,
            ], [
                [
                    'description' => 'Late cancellation fee',
                    'quantity' => 1,
                    'unit_price' => $preview['fee'],
                ],
            ]);
        }

        app(AppNotificationService::class)->notifyPickupRequestCancelled($pickupRequest);

        return $pickupRequest;
    }

    private function actorRole(User $by): string
    {
        $role = strtolower(trim((string) $by->role));

        if (in_array($role, ['parent', 'self', 'driver'], true)) {
            return $role;
        }

        if ($by->isPanelAdmin()) {
            return 'admin';
        }

        return $role !== '' ? $role : 'user';
    }
}
