<?php

namespace App\Http\Controllers\Api\Driver;

use App\Mail\EmailVerificationCodeMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class AuthController extends BaseApiController
{
    /**
     * Step 1: Basic signup (no vehicle / no KYC docs).
     * Fields: name, phone, email, password, referral_code (optional)
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'phone' => ['required', 'string', 'max:50', 'unique:users,phone'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'string', 'min:6'],
                'referral_code' => ['nullable', 'string', 'max:50'],
            ]);

            $otp = (string) random_int(100000, 999999);

            $referrerId = null;
            if (!empty($validated['referral_code'])) {
                $referrerId = User::query()
                    ->where('referral_code', $validated['referral_code'])
                    ->where('id', '!=', 0)
                    ->value('id');
            }

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => $validated['password'],
                'role' => 'driver',
                'status' => 'Pending',
                'otp' => $otp,
                'referral_code' => $validated['referral_code'] ?? null,
                'referred_by' => $referrerId,
            ]);

            Mail::to($user->email)->send(
                new EmailVerificationCodeMail($otp, $user->name)
            );

            $token = $user->createToken('driver-api')->plainTextToken;

            return $this->successResponse([
                'user' => $user->toDriverApiArray(),
                'token' => $token,
                'email_verification_required' => true,
                'kyc_required' => true,
                'vehicle_verification_required' => true,
                'next_step' => 'verify_email',
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
                'email' => ['required', 'string', 'email'],
                'password' => ['required', 'string'],
            ]);

            /** @var User|null $user */
            $user = User::where('email', $validated['email'])
                ->where('role', 'driver')
                ->first();

            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return $this->errorResponse('Invalid credentials', 401);
            }

            if (is_null($user->email_verified_at)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please verify your email address before logging in.',
                    'redirect_to' => 'verify-email',
                    'data' => [
                        'kyc_status' => $user->kycStatus(),
                        'vehicle_verification_status' => $user->vehicleVerificationStatus(),
                        'next_step' => 'verify_email',
                    ],
                ], 403);
            }

            $user->tokens()->delete();
            $token = $user->createToken('driver-api')->plainTextToken;

            $kycStatus = $user->kycStatus();
            $vehicleVerificationStatus = $user->vehicleVerificationStatus();
            $nextStep = $user->driverNextStep();

            return $this->successResponse([
                'user' => $user->toDriverApiArray(),
                'token' => $token,
                'kyc_status' => $kycStatus,
                'vehicle_verification_status' => $vehicleVerificationStatus,
                'next_step' => $nextStep,
            ], 'Logged in successfully');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to login driver');
        }
    }

    public function resendOtp(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => ['required', 'email'],
            ]);

            $user = User::where('email', $validated['email'])
                ->where('role', 'driver')
                ->first();

            if (!$user) {
                return $this->errorResponse('User not found.', 404);
            }

            if ($user->email_verified_at) {
                return $this->errorResponse('Email is already verified.', 422);
            }

            $otp = (string) random_int(100000, 999999);

            $user->update([
                'otp' => $otp,
            ]);

            Mail::to($user->email)->send(
                new EmailVerificationCodeMail($otp, $user->name)
            );

            return $this->successResponse([], 'A new verification code has been sent to your email.');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to resend OTP');
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
                'otp' => ['required', 'digits:6'],
            ]);

            $user = User::where('email', $validated['email'])
                ->where('role', 'driver')
                ->where('otp', $validated['otp'])
                ->first();

            if (!$user) {
                return $this->errorResponse('Invalid verification code.', 422);
            }

            $user->update([
                'otp' => null,
                'email_verified_at' => now(),
            ]);

            $kycStatus = $user->kycStatus();
            $vehicleVerificationStatus = $user->vehicleVerificationStatus();

            return $this->successResponse([
                'kyc_status' => $kycStatus,
                'kyc_required' => $kycStatus !== 'approved',
                'vehicle_verification_status' => $vehicleVerificationStatus,
                'vehicle_verification_required' => $vehicleVerificationStatus !== 'approved',
                'next_step' => $user->fresh()->driverNextStep(),
            ], $user->needsPhoneVerification()
                ? 'Email verified successfully. Please verify your phone number.'
                : 'Email verified successfully. Please complete driver verification (KYC).');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to verify email');
        }
    }

    public function sendPhoneVerification(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            if (strcasecmp(trim((string) $user->role), 'driver') !== 0) {
                return $this->errorResponse('Forbidden', 403);
            }

            if (is_null($user->email_verified_at)) {
                return $this->errorResponse('Please verify your email first.', 403, null, [
                    'next_step' => 'verify_email',
                ]);
            }

            app(\App\Services\PhoneOtpService::class)->send($user);

            return $this->successResponse([
                'expires_in_minutes' => 10,
                'next_step' => 'verify_phone',
            ], 'Verification code sent to your phone. A copy was also emailed.');
        } catch (RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to send phone verification');
        }
    }

    public function verifyPhone(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'code' => ['required', 'string', 'size:6', 'regex:/^[0-9]{6}$/'],
            ]);

            $user = $request->user();
            if (strcasecmp(trim((string) $user->role), 'driver') !== 0) {
                return $this->errorResponse('Forbidden', 403);
            }

            $user = app(\App\Services\PhoneOtpService::class)->verify($user, $validated['code']);

            return $this->successResponse([
                'user' => $user->toDriverApiArray(),
                'next_step' => $user->driverNextStep(),
            ], 'Phone verified successfully.');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to verify phone');
        }
    }
}
