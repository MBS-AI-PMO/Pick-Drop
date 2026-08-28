<?php

namespace App\Http\Controllers\Api\ParentSelf;

use App\Models\DeviceToken;
use App\Models\PickupRequest;
use App\Models\School;
use App\Models\ShiftDayRun;
use App\Models\WalletTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class PlatformController extends BaseApiController
{
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

    public function schools(): JsonResponse
    {
        try {
            $schools = School::query()
                ->with('city')
                ->where('status', 'Active')
                ->orderBy('name')
                ->get()
                ->map->toApiArray()
                ->values();

            return $this->successResponse($schools, 'Schools');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch schools');
        }
    }

    public function wallet(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $rows = WalletTransaction::query()
                ->where('user_id', $user->id)
                ->latest('id')
                ->limit(50)
                ->get();

            return $this->successResponse([
                'balance' => (float) $user->referral_balance,
                'transactions' => $rows,
            ], 'Wallet');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch wallet');
        }
    }

    public function contact(Request $request, int $requestId): JsonResponse
    {
        try {
            $pickupRequest = PickupRequest::with('driver')
                ->where('id', $requestId)
                ->where('parent_id', $request->user()->id)
                ->first();

            if (!$pickupRequest) {
                return $this->errorResponse('Not found', 404);
            }

            return $this->successResponse([
                'type' => 'driver',
                'name' => $pickupRequest->driver?->name,
                'phone' => $pickupRequest->driver?->phone,
                'emergency_name' => $pickupRequest->driver?->emergency_contact_name,
                'emergency_phone' => $pickupRequest->driver?->emergency_contact_phone,
            ], 'Call contact');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch contact');
        }
    }

    public function cancellationPreview(Request $request, int $requestId): JsonResponse
    {
        try {
            $pickupRequest = PickupRequest::query()
                ->where('id', $requestId)
                ->where('parent_id', $request->user()->id)
                ->first();

            if (!$pickupRequest) {
                return $this->errorResponse('Not found', 404);
            }

            return $this->successResponse(
                app(\App\Services\CancellationService::class)->preview($pickupRequest),
                'Cancellation preview'
            );
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to preview cancellation');
        }
    }

    public function todayOtp(Request $request, int $requestId): JsonResponse
    {
        try {
            $pickupRequest = PickupRequest::query()
                ->where('id', $requestId)
                ->where('parent_id', $request->user()->id)
                ->first();

            if (!$pickupRequest) {
                return $this->errorResponse('Not found', 404);
            }

            $run = app(\App\Services\ShiftDayService::class)->ensureToday($pickupRequest);

            return $this->successResponse($run->toApiArray(true), 'Today pickup OTP');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch OTP');
        }
    }
}
