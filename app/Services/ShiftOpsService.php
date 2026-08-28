<?php

namespace App\Services;

use App\Models\Holiday;
use App\Models\Invoice;
use App\Models\PickupRequest;
use App\Models\ShiftAttendance;
use Illuminate\Support\Carbon;

class ShiftOpsService
{
    public function __construct(
        private readonly PickupRequestAssignmentService $assigner,
        private readonly InvoiceService $invoices,
        private readonly AppNotificationService $notifier,
        private readonly AttendanceService $attendance
    ) {
    }

    /**
     * @return array<string, int>
     */
    public function run(): array
    {
        return [
            'auto_assigned' => $this->assigner->autoAssignExpired(),
            'overdue_invoices' => $this->invoices->markOverdueInvoices(),
            'payment_reminders' => $this->sendPaymentReminders(),
            'delays' => $this->notifyDelays(),
            'renewals' => $this->notifyRenewals(),
            'auto_renewed' => $this->processAutoRenewals(),
            'absents' => $this->markAbsents(),
            'ended_shifts' => $this->completeEndedShifts(),
            'expiring_docs' => $this->notifyExpiringDocuments(),
        ];
    }

    public function sendPaymentReminders(): int
    {
        $sent = 0;

        $invoices = Invoice::query()
            ->whereIn('status', [Invoice::STATUS_UNPAID, Invoice::STATUS_OVERDUE])
            ->whereColumn('amount_paid', '<', 'total')
            ->where(function ($q) {
                $q->whereNull('reminder_sent_at')
                    ->orWhere('reminder_sent_at', '<=', now()->subDay());
            })
            ->get();

        foreach ($invoices as $invoice) {
            $this->notifier->notify(
                (int) $invoice->user_id,
                'payment_reminder',
                'Payment reminder',
                sprintf(
                    'Invoice %s of %s is %s. Please pay to keep your shift active.',
                    $invoice->invoice_number,
                    $invoice->formattedTotal(),
                    $invoice->status === Invoice::STATUS_OVERDUE ? 'overdue' : 'unpaid'
                ),
                ['invoice_id' => $invoice->id, 'invoice_number' => $invoice->invoice_number],
                'payment_reminders'
            );

            $invoice->update(['reminder_sent_at' => now()]);
            $sent++;
        }

        return $sent;
    }

    public function notifyDelays(): int
    {
        $count = 0;
        $today = now()->toDateString();
        $weekday = strtolower(now()->englishDayOfWeek);

        $requests = PickupRequest::query()
            ->where('payment_status', PickupRequest::PAYMENT_PAID)
            ->whereIn('status', ['accepted'])
            ->whereNotNull('driver_id')
            ->where(function ($range) use ($today) {
                $range->whereNull('shift_start_date')->orWhereDate('shift_start_date', '<=', $today);
            })
            ->where(function ($range) use ($today) {
                $range->whereNull('shift_end_date')->orWhereDate('shift_end_date', '>=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('last_delay_notified_on')
                    ->orWhereDate('last_delay_notified_on', '<', $today);
            })
            ->get();

        foreach ($requests as $request) {
            if (!$this->runsToday($request, $weekday) || $this->attendance->isOffDay($request, $today)) {
                continue;
            }

            $pickup = substr((string) $request->pickup_time, 0, 5);
            if (!preg_match('/^\d{2}:\d{2}$/', $pickup)) {
                continue;
            }

            $due = Carbon::parse($today . ' ' . $pickup . ':00')->addMinutes(10);
            if (now()->lt($due)) {
                continue;
            }

            $this->notifier->notifyDelay($request, 10, 'Pickup time has passed and the stop is not marked yet.');
            $request->update(['last_delay_notified_on' => $today]);
            $count++;
        }

        return $count;
    }

    public function notifyRenewals(): int
    {
        $count = 0;
        $from = now()->toDateString();
        $to = now()->addDays(7)->toDateString();

        $requests = PickupRequest::query()
            ->whereNotIn('status', ['cancelled', 'pending'])
            ->where('payment_status', PickupRequest::PAYMENT_PAID)
            ->whereDate('shift_end_date', '>=', $from)
            ->whereDate('shift_end_date', '<=', $to)
            ->whereIn('renewal_status', ['none', 'notified'])
            ->whereNull('renewal_notified_at')
            ->get();

        foreach ($requests as $request) {
            $this->notifier->notifyRenewalDue($request);
            $request->update([
                'renewal_status' => 'notified',
                'renewal_notified_at' => now(),
            ]);
            $count++;
        }

        return $count;
    }

    public function processAutoRenewals(): int
    {
        $count = 0;
        $from = now()->toDateString();
        $to = now()->addDays(7)->toDateString();

        $requests = PickupRequest::query()
            ->where('auto_renew', true)
            ->whereNotIn('status', ['cancelled', 'pending'])
            ->whereDate('shift_end_date', '>=', $from)
            ->whereDate('shift_end_date', '<=', $to)
            ->where('renewal_status', '!=', 'renewed')
            ->get();

        foreach ($requests as $request) {
            $existing = Invoice::query()
                ->where('pickup_request_id', $request->id)
                ->where('kind', 'renewal')
                ->whereNotIn('status', [Invoice::STATUS_CANCELLED, Invoice::STATUS_PAID])
                ->exists();

            if ($existing) {
                continue;
            }

            try {
                $this->invoices->createRenewalInvoice($request, 1);
                $count++;
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return $count;
    }

    public function markAbsents(): int
    {
        if (now()->format('H:i') < '20:00') {
            return 0;
        }

        $count = 0;
        $today = now()->toDateString();
        $weekday = strtolower(now()->englishDayOfWeek);

        $requests = PickupRequest::query()
            ->where('payment_status', PickupRequest::PAYMENT_PAID)
            ->whereIn('status', ['accepted'])
            ->where(function ($range) use ($today) {
                $range->whereNull('shift_start_date')->orWhereDate('shift_start_date', '<=', $today);
            })
            ->where(function ($range) use ($today) {
                $range->whereNull('shift_end_date')->orWhereDate('shift_end_date', '>=', $today);
            })
            ->get();

        foreach ($requests as $request) {
            if (!$this->runsToday($request, $weekday) || $this->attendance->isOffDay($request, $today)) {
                continue;
            }

            if ($this->attendance->todayRecord($request, $today)) {
                continue;
            }

            $this->attendance->markAbsent($request, $today);
            $count++;
        }

        return $count;
    }

    public function applyHolidayToShifts(Holiday $holiday): int
    {
        $query = PickupRequest::query()
            ->whereNotIn('status', ['cancelled', 'pending'])
            ->where('payment_status', PickupRequest::PAYMENT_PAID)
            ->where(function ($range) use ($holiday) {
                $range->whereNull('shift_start_date')->orWhereDate('shift_start_date', '<=', $holiday->date);
            })
            ->where(function ($range) use ($holiday) {
                $range->whereNull('shift_end_date')->orWhereDate('shift_end_date', '>=', $holiday->date);
            });

        if ($holiday->city_id) {
            $query->where('city_id', $holiday->city_id);
        }

        $count = 0;
        foreach ($query->get() as $request) {
            $this->attendance->markHoliday($request, $holiday->date->toDateString(), $holiday->name);
            $count++;
        }

        return $count;
    }

    private function runsToday(PickupRequest $request, string $weekday): bool
    {
        $days = collect($request->days ?? [])->map(fn ($d) => strtolower((string) $d));

        return $days->contains($weekday) || $days->contains(substr($weekday, 0, 3));
    }

    public function completeEndedShifts(): int
    {
        return PickupRequest::query()
            ->whereNotIn('status', ['cancelled', 'completed', 'pending'])
            ->whereNotNull('shift_end_date')
            ->whereDate('shift_end_date', '<', now()->toDateString())
            ->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
    }

    public function notifyExpiringDocuments(): int
    {
        $sent = 0;
        $limit = now()->addDays(14)->toDateString();

        $licenses = \App\Models\DriverVerification::query()
            ->where('status', 'approved')
            ->whereNotNull('license_expiry')
            ->whereDate('license_expiry', '<=', $limit)
            ->whereDate('license_expiry', '>=', now()->toDateString())
            ->get();

        foreach ($licenses as $row) {
            $already = \App\Models\AppNotification::query()
                ->where('user_id', $row->user_id)
                ->where('type', 'document_expiring')
                ->whereDate('created_at', now()->toDateString())
                ->exists();
            if ($already) {
                continue;
            }
            $this->notifier->notify(
                (int) $row->user_id,
                'document_expiring',
                'License expiring',
                'Your driving license expires on ' . $row->license_expiry?->toFormattedDateString(),
                ['license_expiry' => $row->license_expiry?->toDateString()]
            );
            $sent++;
        }

        $vehicles = \App\Models\DriverVehicleVerification::query()
            ->where('status', 'approved')
            ->where(function ($q) use ($limit) {
                $q->whereDate('insurance_expiry', '<=', $limit)
                    ->orWhereDate('registration_expiry', '<=', $limit);
            })
            ->get();

        foreach ($vehicles as $row) {
            $this->notifier->notify(
                (int) $row->user_id,
                'document_expiring',
                'Vehicle document expiring',
                'A vehicle document is expiring soon. Please update your papers.',
                ['vehicle_verification_id' => $row->id]
            );
            $sent++;
        }

        if ($sent > 0) {
            $this->notifier->notifyAdminPanel(
                'Documents expiring',
                $sent . ' driver/vehicle documents expire within 14 days.',
                'warning'
            );
        }

        return $sent;
    }
}
