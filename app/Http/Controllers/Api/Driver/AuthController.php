<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Api\Driver\BaseApiController;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailVerificationCodeMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Throwable;

class AuthController extends BaseApiController
{
    public function register(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name'            => ['required', 'string', 'max:255'],
                'email'           => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
                'home_address'    => ['required', 'string', 'max:500'],
                'password'        => ['required', 'string', 'min:6'],
                'phone'           => ['required', 'string', 'max:50', 'unique:users,phone'],
                'city_id'         => ['required', 'integer', 'exists:cities,id'],
                'service_areas'   => ['required', 'array', 'min:1'],
                'service_areas.*' => ['integer', 'exists:areas,id'],
            ]);

            $cityId = (int) $validated['city_id'];
            $serviceNorm = array_values(array_unique(array_map('intval', $validated['service_areas'])));
            $this->assertAreaIdsBelongToCity($cityId, $serviceNorm);

            $otp = rand(100000, 999999);
            $user = User::create([
                'name'           => $validated['name'],
                'email'          => $validated['email'],
                'password'       => $validated['password'],
                'role'           => 'driver',
                'phone'          => $validated['phone'],
                'city_id'        => $cityId,
                'service_areas'  => $serviceNorm,
                  'otp'            => $otp,
                'details'        => [
                    'home_address' => $validated['home_address'],
                ],
            ]);

            $user->load('city');

            Mail::to($user->email)->send(
    new EmailVerificationCodeMail($otp, $user->name)
);
            $token = $user->createToken('driver-api')->plainTextToken;

             return $this->successResponse([
    'user'  => $user->toDriverApiArray(),
    'token' => $token,
    'email_verification_required' => true,
], 'Registered successfully. Verification code has been sent to your email.', 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to register driver');
        }
    }

    public function login(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email'    => ['required', 'string', 'email'],
                'password' => ['required', 'string'],
            ]);

            /** @var User|null $user */
            $user = User::where('email', $validated['email'])
                ->where('role', 'driver')
                ->first();

            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return $this->errorResponse('Invalid credentials', 401);
            }

            $user->tokens()->delete();
            $token = $user->createToken('driver-api')->plainTextToken;

            return $this->successResponse([
                'user'  => $user->toDriverApiArray(),
                'token' => $token,
            ], 'Logged in successfully');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to login driver');
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()?->delete();

            return $this->successResponse(null, 'Logged out successfully');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to logout');
        }
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => ['required', 'email'],
            ]);

            $status = Password::sendResetLink(['email' => $validated['email']]);

            if ($status === Password::RESET_LINK_SENT) {
                return $this->successResponse(null, __($status));
            }

            return $this->errorResponse(__($status), 422);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to send reset link');
        }
    }

    public function resetPassword(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => ['required', 'email'],
                'token' => ['required', 'string'],
                'password' => ['required', 'string', 'min:6', 'confirmed'],
            ]);

            $status = Password::reset(
                [
                    'email' => $validated['email'],
                    'token' => $validated['token'],
                    'password' => $validated['password'],
                    'password_confirmation' => $request->input('password_confirmation'),
                ],
                function (User $user, string $password) {
                    $user->password = $password;
                    $user->save();
                    $user->tokens()->delete();
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return $this->successResponse(null, __($status));
            }

            return $this->errorResponse(__($status), 422);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to reset password');
        }
    }
 public function verifyOtp(Request $request): JsonResponse
{
    try {

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'otp'   => ['required', 'digits:6'],
        ]);

        $user = User::where('email', $validated['email'])
                    ->where('otp', $validated['otp'])
                    ->first();

        if (!$user) {
            return $this->errorResponse('Invalid verification code.', 422);
        }

        $user->update([
            'otp' => null,
            'email_verified_at' => now(),
        ]);

        return $this->successResponse([], 'Email verified successfully.');

    } catch (ValidationException $e) {

        return $this->errorResponse('Validation failed', 422, $e->errors());

    } catch (Throwable $e) {

        return $this->handleException($e, 'Unable to verify email');

    }
}
}

