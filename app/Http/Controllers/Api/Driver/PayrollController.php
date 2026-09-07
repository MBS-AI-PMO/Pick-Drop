<?php

namespace App\Http\Controllers\Api\Driver;

use App\Models\DriverPayrollBill;
use App\Models\PickupRequest;
use App\Services\DriverPayrollService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class PayrollController extends BaseApiController
{
    public function __construct(
        private readonly DriverPayrollService $payroll,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $driver = $request->user();
            $denied = $this->denyUnlessDriverReady($driver);
            if ($denied) {
                return $denied;
            }

            $bills = DriverPayrollBill::query()
                ->where('driver_id', $driver->id)
                ->with(['pickupRequest.student'])
                ->latest('id')
                ->get()
                ->map(fn (DriverPayrollBill $bill) => $bill->toApiArray());

            $shifts = PickupRequest::query()
                ->where('driver_id', $driver->id)
                ->whereNotIn('status', ['cancelled', 'pending'])
                ->where('payment_status', PickupRequest::PAYMENT_PAID)
                ->with(['student', 'attendances'])
                ->get()
                ->map(function (PickupRequest $shift) {
                    $periods = $this->payroll->eligiblePeriods($shift);

                    return [
                        'pickup_request_id' => $shift->id,
                        'student_name' => $shift->student?->name,
                        'shift_start' => $shift->shift_start_date?->toDateString(),
                        'shift_end' => $shift->shift_end_date?->toDateString(),
                        'monthly_rate' => $shift->driver_monthly_rate,
                        'periods' => $periods->map(fn (array $p) => [
                            'period_start' => $p['period_start'],
                            'period_end' => $p['period_end'],
                            'label' => $p['label'],
                            'can_request' => $p['can_request'],
                            'bill' => $p['bill']?->toApiArray(),
                        ])->values(),
                    ];
                });

            $pending = $bills->whereIn('status', [DriverPayrollBill::STATUS_PENDING, DriverPayrollBill::STATUS_APPROVED]);
            $paid = $bills->where('status', DriverPayrollBill::STATUS_PAID);

            return $this->successResponse([
                'currency' => 'PKR',
                'summary' => [
                    'total_requested' => round((float) $bills->sum('calculated_amount'), 2),
                    'pending_amount' => round((float) $pending->sum('calculated_amount'), 2),
                    'paid_amount' => round((float) $paid->sum('calculated_amount'), 2),
                    'pending_count' => $pending->count(),
                    'paid_count' => $paid->count(),
                ],
                'bills' => $bills->values(),
                'shifts' => $shifts->values(),
            ], 'Driver payments');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch payroll');
        }
    }

    public function show(Request $request, DriverPayrollBill $bill): JsonResponse
    {
        try {
            $driver = $request->user();
            $denied = $this->denyUnlessDriverReady($driver);
            if ($denied) {
                return $denied;
            }

            if ((int) $bill->driver_id !== (int) $driver->id) {
                return $this->errorResponse('Not found', 404);
            }

            $bill->load(['pickupRequest.student']);
            $calc = $this->payroll->calculate(
                $bill->pickupRequest,
                $bill->period_start->toDateString(),
                $bill->period_end->toDateString()
            );

            return $this->successResponse([
                'bill' => $bill->toApiArray(),
                'attendance_breakdown' => $calc['breakdown'],
            ], 'Payroll bill');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch bill');
        }
    }

    public function request(Request $request): JsonResponse
    {
        try {
            $driver = $request->user();
            $denied = $this->denyUnlessDriverReady($driver);
            if ($denied) {
                return $denied;
            }

            $validated = $request->validate([
                'pickup_request_id' => ['required', 'integer', 'exists:pickup_requests,id'],
                'period_start' => ['required', 'date'],
                'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            ]);

            $shift = PickupRequest::query()->findOrFail($validated['pickup_request_id']);
            $bill = $this->payroll->requestPayment(
                $shift,
                $driver,
                $validated['period_start'],
                $validated['period_end']
            );

            return $this->successResponse($bill->toApiArray(), 'Payment request submitted', 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to submit payment request');
        }
    }

    public function preview(Request $request): JsonResponse
    {
        try {
            $driver = $request->user();
            $denied = $this->denyUnlessDriverReady($driver);
            if ($denied) {
                return $denied;
            }

            $validated = $request->validate([
                'pickup_request_id' => ['required', 'integer', 'exists:pickup_requests,id'],
                'period_start' => ['required', 'date'],
                'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            ]);

            $shift = PickupRequest::query()
                ->with('attendances')
                ->findOrFail($validated['pickup_request_id']);

            if ((int) $shift->driver_id !== (int) $driver->id) {
                return $this->errorResponse('Not found', 404);
            }

            $calc = $this->payroll->calculate(
                $shift,
                $validated['period_start'],
                $validated['period_end']
            );

            return $this->successResponse([
                'monthly_rate' => $calc['monthly_rate'],
                'calculated_amount' => $calc['calculated_amount'],
                'formatted_amount' => 'PKR ' . number_format($calc['calculated_amount'], 2),
                'scheduled_days' => $calc['scheduled_days'],
                'present_days' => $calc['present_days'],
                'absent_days' => $calc['absent_days'],
                'skipped_days' => $calc['skipped_days'],
                'holiday_days' => $calc['holiday_days'],
                'attendance_breakdown' => $calc['breakdown'],
            ], 'Bill preview');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to preview bill');
        }
    }
}
