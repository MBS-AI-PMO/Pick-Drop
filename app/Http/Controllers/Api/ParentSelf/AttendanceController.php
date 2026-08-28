<?php

namespace App\Http\Controllers\Api\ParentSelf;

use App\Models\Holiday;
use App\Models\PickupRequest;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class AttendanceController extends BaseApiController
{
    public function __construct(private readonly AttendanceService $attendance)
    {
    }

    public function index(Request $request, int $requestId): JsonResponse
    {
        try {
            $pickupRequest = $this->ownedRequest($request, $requestId);
            if ($pickupRequest instanceof JsonResponse) {
                return $pickupRequest;
            }

            $rows = $this->attendance->forRequest($pickupRequest)->map->toApiArray()->values();
            $today = $this->attendance->todayRecord($pickupRequest);

            return $this->successResponse([
                'today' => $today?->toApiArray(),
                'is_off_today' => $this->attendance->isOffDay($pickupRequest),
                'records' => $rows,
            ], 'Attendance');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch attendance');
        }
    }

    public function skip(Request $request, int $requestId): JsonResponse
    {
        try {
            $pickupRequest = $this->ownedRequest($request, $requestId);
            if ($pickupRequest instanceof JsonResponse) {
                return $pickupRequest;
            }

            $validated = $request->validate([
                'date' => ['required', 'date'],
                'reason' => ['nullable', 'string', 'max:255'],
            ]);

            $row = $this->attendance->skip(
                $pickupRequest,
                $validated['date'],
                $request->user(),
                $validated['reason'] ?? null
            );

            return $this->successResponse($row->toApiArray(), 'Pickup skipped for this date');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to skip day');
        }
    }

    public function unskip(Request $request, int $requestId): JsonResponse
    {
        try {
            $pickupRequest = $this->ownedRequest($request, $requestId);
            if ($pickupRequest instanceof JsonResponse) {
                return $pickupRequest;
            }

            $validated = $request->validate([
                'date' => ['required', 'date'],
            ]);

            $this->attendance->clearSkip($pickupRequest, $validated['date']);

            return $this->successResponse(null, 'Skip removed');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to undo skip');
        }
    }

    public function holidays(Request $request): JsonResponse
    {
        try {
            $cityId = $request->integer('city_id') ?: null;
            $from = $request->input('from', now()->toDateString());
            $to = $request->input('to', now()->addMonths(2)->toDateString());

            $holidays = Holiday::query()
                ->with('city')
                ->whereDate('date', '>=', $from)
                ->whereDate('date', '<=', $to)
                ->where(function ($q) use ($cityId) {
                    $q->whereNull('city_id');
                    if ($cityId) {
                        $q->orWhere('city_id', $cityId);
                    }
                })
                ->orderBy('date')
                ->get()
                ->map->toApiArray()
                ->values();

            return $this->successResponse($holidays, 'Holidays');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch holidays');
        }
    }

    private function ownedRequest(Request $request, int $requestId): PickupRequest|JsonResponse
    {
        $pickupRequest = PickupRequest::query()
            ->where('id', $requestId)
            ->where('parent_id', $request->user()->id)
            ->first();

        if (!$pickupRequest) {
            return $this->errorResponse('Not found', 404);
        }

        return $pickupRequest;
    }
}
