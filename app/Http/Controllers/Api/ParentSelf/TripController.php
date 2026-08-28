<?php

namespace App\Http\Controllers\Api\ParentSelf;

use App\Models\PickupRequest;
use App\Models\ShiftDayRun;
use App\Services\ShiftDayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class TripController extends BaseApiController
{
    public function recent(Request $request): JsonResponse
    {
        try {
            $trips = ShiftDayRun::query()
                ->with(['pickupRequest.student', 'pickupRequest.driver', 'pickupRequest.vehicle'])
                ->whereHas('pickupRequest', fn ($q) => $q->where('parent_id', $request->user()->id))
                ->latest('date')
                ->paginate(\App\Support\AppPagination::PER_PAGE);

            $trips->getCollection()->transform(fn (ShiftDayRun $row) => array_merge(
                $row->toApiArray(false),
                ['request' => $row->pickupRequest?->toApiArray()]
            ));

            return $this->successResponse($trips, 'Recent trips');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch trips');
        }
    }

    public function todayStatus(Request $request): JsonResponse
    {
        try {
            $requests = PickupRequest::with(['student', 'driver', 'vehicle'])
                ->where('parent_id', $request->user()->id)
                ->whereNotIn('status', ['cancelled', 'pending'])
                ->where('payment_status', PickupRequest::PAYMENT_PAID)
                ->get();

            $days = $requests->map(function (PickupRequest $row) {
                $run = app(ShiftDayService::class)->ensureToday($row);

                return [
                    'request' => $row->toApiArray(),
                    'today' => $run->toApiArray(true),
                ];
            })->values();

            return $this->successResponse([
                'date' => now()->toDateString(),
                'shifts' => $days,
            ], 'Today status');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch today status');
        }
    }
}
