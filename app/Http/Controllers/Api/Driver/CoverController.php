<?php

namespace App\Http\Controllers\Api\Driver;

use App\Models\DriverPayroll;
use App\Models\PickupRequest;
use App\Models\ShiftReplacement;
use App\Services\CoverService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class CoverController extends BaseApiController
{
    public function __construct(private readonly CoverService $cover)
    {
    }

    public function available(Request $request): JsonResponse
    {
        try {
            $driver = $request->user();
            $denied = $this->denyUnlessDriverReady($driver);
            if ($denied) {
                return $denied;
            }

            $rows = $this->cover->availableForDriver($driver)
                ->map->toApiArray()
                ->values();

            return $this->successResponse($rows, 'Cover trips in your area');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch cover trips');
        }
    }

    public function unavailable(Request $request, PickupRequest $pickupRequest): JsonResponse
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

            $validated = $request->validate([
                'date' => ['nullable', 'date'],
                'reason' => ['nullable', 'string', 'max:1000'],
            ]);

            $row = $this->cover->open(
                $pickupRequest,
                ShiftReplacement::REASON_UNAVAILABLE,
                $validated['date'] ?? now()->toDateString(),
                $validated['reason'] ?? 'Driver cannot come today'
            );

            return $this->successResponse($row->toApiArray(), 'Nearby drivers are being offered this trip today.');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to mark unavailable');
        }
    }

    public function accept(Request $request, ShiftReplacement $replacement): JsonResponse
    {
        try {
            $driver = $request->user();
            $denied = $this->denyUnlessDriverReady($driver);
            if ($denied) {
                return $denied;
            }

            $row = $this->cover->accept($replacement, $driver);

            return $this->successResponse($row->toApiArray(), 'Cover trip accepted. It is now hidden from other drivers.');
        } catch (RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to accept cover trip');
        }
    }

    public function payrolls(Request $request): JsonResponse
    {
        try {
            $driver = $request->user();
            $denied = $this->denyUnlessDriverReady($driver);
            if ($denied) {
                return $denied;
            }

            $rows = DriverPayroll::query()
                ->with('items')
                ->where('driver_id', $driver->id)
                ->orderByDesc('month')
                ->limit(12)
                ->get()
                ->map->toApiArray()
                ->values();

            return $this->successResponse($rows, 'Payroll');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch payroll');
        }
    }

    public function payroll(Request $request, DriverPayroll $payroll): JsonResponse
    {
        try {
            $driver = $request->user();
            $denied = $this->denyUnlessDriverReady($driver);
            if ($denied) {
                return $denied;
            }

            if ((int) $payroll->driver_id !== (int) $driver->id) {
                return $this->errorResponse('Not found', 404);
            }

            return $this->successResponse($payroll->load('items')->toApiArray(), 'Payroll detail');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch payroll');
        }
    }
}
