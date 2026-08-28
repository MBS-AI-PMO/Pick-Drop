<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\PickupRequest;
use App\Models\PlatformSetting;
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

        return [
            'allowed' => !in_array($pickupRequest->status, ['picked_up', 'dropped', 'completed'], true),
            'cancel_hours' => $hours,
            'fee_percent' => $percent,
            'fee' => $fee,
            'within_window' => $withinWindow,
            'hours_left' => (int) $hoursLeft,
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
}
