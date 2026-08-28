<?php

namespace App\Http\Controllers\Api\Driver;

use App\Models\DeviceToken;
use App\Models\PickupRequest;
use App\Models\ShiftDayRun;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class EarningsController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        try {
            $driver = $request->user();
            $denied = $this->denyUnlessDriverReady($driver);
            if ($denied) {
                return $denied;
            }

            $rows = PickupRequest::query()
                ->where('driver_id', $driver->id)
                ->whereNotIn('status', ['cancelled', 'pending'])
                ->get();

            $month = $rows->filter(fn (PickupRequest $r) => $r->driver_payout_due_on?->isSameMonth(now()));

            return $this->successResponse([
                'currency' => 'PKR',
                'this_month' => [
                    'total' => round((float) $month->sum('driver_payout_amount'), 2),
                    'paid' => round((float) $month->where('driver_payout_status', PickupRequest::DRIVER_PAYOUT_PAID)->sum('driver_payout_amount'), 2),
                    'unpaid' => round((float) $month->where('driver_payout_status', '!=', PickupRequest::DRIVER_PAYOUT_PAID)->sum('driver_payout_amount'), 2),
                ],
                'all_time' => [
                    'total' => round((float) $rows->sum('driver_payout_amount'), 2),
                    'paid' => round((float) $rows->where('driver_payout_status', PickupRequest::DRIVER_PAYOUT_PAID)->sum('driver_payout_amount'), 2),
                    'unpaid' => round((float) $rows->where('driver_payout_status', '!=', PickupRequest::DRIVER_PAYOUT_PAID)->sum('driver_payout_amount'), 2),
                ],
                'completed_days' => ShiftDayRun::query()
                    ->whereHas('pickupRequest', fn ($q) => $q->where('driver_id', $driver->id))
                    ->where('status', ShiftDayRun::COMPLETED)
                    ->count(),
                'shifts' => $rows->map(fn (PickupRequest $r) => [
                    'id' => $r->id,
                    'amount' => $r->driver_payout_amount,
                    'status' => $r->driver_payout_status,
                    'due_on' => $r->driver_payout_due_on?->toDateString(),
                ])->values(),
            ], 'Earnings');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch earnings');
        }
    }

    public function registerDevice(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'token' => ['required', 'string', 'max:512'],
                'platform' => ['nullable', 'string', 'max:30'],
            ]);

            $row = DeviceToken::query()->updateOrCreate(
                [
                    'user_id' => $request->user()->id,
                    'token' => $validated['token'],
                ],
                ['platform' => $validated['platform'] ?? null]
            );

            return $this->successResponse($row, 'Device registered for push');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to register device');
        }
    }

    public function contact(Request $request, PickupRequest $pickupRequest): JsonResponse
    {
        try {
            $driver = $request->user();
            $denied = $this->denyUnlessDriverReady($driver);
            if ($denied) {
                return $denied;
            }

            if ((int) $pickupRequest->driver_id !== (int) $driver->id) {
                return $this->errorResponse('Not found', 404);
            }

            $pickupRequest->loadMissing('parent');

            return $this->successResponse([
                'type' => 'parent',
                'name' => $pickupRequest->parent?->name,
                'phone' => $pickupRequest->parent?->phone,
                'emergency_name' => $pickupRequest->parent?->emergency_contact_name,
                'emergency_phone' => $pickupRequest->parent?->emergency_contact_phone,
            ], 'Call contact');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch contact');
        }
    }
}
