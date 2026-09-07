<?php

namespace App\Http\Controllers\Api\Driver;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Throwable;

class AccountController extends BaseApiController
{
    public function changePassword(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'current_password' => ['required', 'string'],
                'password' => ['required', 'string', 'min:6', 'confirmed'],
            ]);

            $user = $request->user();

            if (!Hash::check($validated['current_password'], $user->password)) {
                return $this->errorResponse('Current password is incorrect', 422);
            }

            $user->password = $validated['password'];
            $user->save();

            $user->tokens()->delete();

            return $this->successResponse(null, 'Password changed successfully');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to change password');
        }
    }

    public function deleteAccount(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'password' => ['required', 'string'],
            ]);

            $user = $request->user();

            if (!Hash::check($validated['password'], $user->password)) {
                return $this->errorResponse('Password is incorrect', 422);
            }

            $user->tokens()->delete();
            $user->delete();

            return $this->successResponse(null, 'Account deleted successfully');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to delete account');
        }
    }

    public function showPaymentDetails(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->successResponse([
            'payment_account' => $user->paymentAccountDetails(),
            'has_payment_account' => $user->hasPaymentAccount(),
            'banks' => \App\Support\PakistaniBanks::names(),
        ], 'Payment account details');
    }

    public function updatePaymentDetails(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'bank_name' => ['required', 'string', 'max:255'],
                'bank_account_title' => ['required', 'string', 'max:255'],
                'bank_account_number' => ['required', 'string', 'max:100'],
                'bank_iban' => ['nullable', 'string', 'max:50'],
            ]);

            $user = $request->user();
            $user->fill($validated);
            $user->save();

            return $this->successResponse([
                'payment_account' => $user->fresh()->paymentAccountDetails(),
                'has_payment_account' => $user->hasPaymentAccount(),
            ], 'Payment account updated');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to update payment account');
        }
    }
}
