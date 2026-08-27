<?php

namespace App\Http\Controllers\Api\ParentSelf;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ValidatesCityAreas;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

    protected function expectedAccountType(Request $request): string
    {
        $name = (string) $request->route()?->getName();

        return str_starts_with($name, 'api.self.') ? 'self' : 'parent';
    }

    protected function denyUnlessAccountType(User $user, Request $request): ?JsonResponse
    {
        $expected = $this->expectedAccountType($request);

        if (strcasecmp(trim((string) $user->role), $expected) !== 0) {
            return $this->errorResponse('This account cannot use this app.', 403);
        }

        return null;
    }

    protected function denyUnlessEmailVerified(User $user): ?JsonResponse
    {
        if (is_null($user->email_verified_at)) {
            return $this->errorResponse('Please verify your email first.', 403, null, [
                'next_step' => 'verify_email',
            ]);
        }

        return null;
    }

    protected function denyUnlessKycApproved(User $user): ?JsonResponse
    {
        $emailDenied = $this->denyUnlessEmailVerified($user);
        if ($emailDenied) {
            return $emailDenied;
        }

        $status = $user->parentSelfKycStatus();
        if ($status !== 'approved') {
            return $this->errorResponse('Please complete identity verification first.', 403, null, [
                'kyc_status' => $status,
                'next_step' => $user->parentSelfNextStep(),
            ]);
        }

        return null;
    }

    protected function denyUnlessOnboardingComplete(User $user): ?JsonResponse
    {
        $kycDenied = $this->denyUnlessKycApproved($user);
        if ($kycDenied) {
            return $kycDenied;
        }

        if ($user->isParentSelfOnboardingComplete()) {
            return null;
        }

        $message = $user->isSelfAccount()
            ? 'Please add your pickup, drop location and office timing first.'
            : 'Please add your children information first.';

        return $this->errorResponse($message, 403, null, [
            'next_step' => $user->parentSelfNextStep(),
        ]);
    }

    protected function handleException(Throwable $e, string $defaultMessage = 'Server error'): JsonResponse
    {
        report($e);

        return $this->errorResponse(
            app()->environment('local') ? $e->getMessage() : $defaultMessage,
            500
        );
    }
}

