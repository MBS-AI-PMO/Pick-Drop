<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ValidatesCityAreas;
use Illuminate\Http\JsonResponse;
use Throwable;

abstract class BaseApiController extends Controller
{
    use ValidatesCityAreas;

    protected function successResponse(mixed $data = null, string $message = 'OK', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    protected function errorResponse(string $message = 'Something went wrong', int $code = 500, mixed $errors = null, mixed $data = null): JsonResponse
    {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        if ($data !== null) {
            $payload['data'] = $data;
        }

        return response()->json($payload, $code);
    }

    protected function denyUnlessDriverReady(User $user): ?JsonResponse
    {
        if (strcasecmp(trim((string) $user->role), 'driver') !== 0) {
            return $this->errorResponse('Forbidden', 403);
        }

        if (strcasecmp(trim((string) $user->status), 'Active') !== 0 || !$user->isOnboardingComplete()) {
            return $this->errorResponse('Please complete driver verification first.', 403, null, [
                'next_step' => $user->driverNextStep(),
                'kyc_status' => $user->kycStatus(),
                'vehicle_verification_status' => $user->vehicleVerificationStatus(),
            ]);
        }

        return null;
    }

    protected function handleException(Throwable $e, string $defaultMessage = 'Server error'): JsonResponse
    {
        // In production you might want to hide $e->getMessage() and just log it.
        report($e);

        return $this->errorResponse(
            app()->environment('local') ? $e->getMessage() : $defaultMessage,
            500
        );
    }
}

