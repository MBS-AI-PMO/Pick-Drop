<?php

namespace App\Http\Controllers\Api\ParentSelf;

use App\Http\Controllers\Api\ParentSelf\BaseApiController;
use App\Models\LoginLog;
use App\Models\User;
use App\Services\EmailVerificationService;
use App\Services\LoginLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class AuthController extends BaseApiController
{
    public function __construct(
        private readonly LoginLogService $loginLogs,
        private readonly EmailVerificationService $emailVerification,
    ) {
    }
    public function register(Request $request): JsonResponse
    {
        try {
            $accountType = $this->expectedAccountType($request);

            $validated = $request->validate([
                'name'     => ['required', 'string', 'max:255'],
                'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
                'address'  => ['nullable', 'string', 'max:500'],
                'contact'  => ['nullable', 'string', 'max:50', 'unique:users,phone'],
                'password' => ['required', 'string', 'min:6', 'confirmed'],
                'type'     => ['required', Rule::in([$accountType])],
                'referral_code' => ['nullable', 'string', 'max:50'],
            ]);

            $user = DB::transaction(function () use ($validated, $accountType) {
                $referrerId = null;
                if (!empty($validated['referral_code'])) {
                    $referrerId = User::query()
                        ->where('referral_code', $validated['referral_code'])
                        ->value('id');
                }

                return User::create([
                    'name'     => $validated['name'],
                    'email'    => $validated['email'],
                    'phone'    => $validated['contact'] ?? null,
                    'password' => $validated['password'],
                    'role'     => $accountType,
                    'status'   => 'Pending',
                    'referred_by' => $referrerId,
                    'details'  => [
                        'address' => $validated['address'] ?? null,
                        'contact' => $validated['contact'] ?? null,
                    ],
                ]);
            });

            $emailSent = $this->emailVerification->issueAndSend($user)['sent'];

            $token = $user->createToken('parent-self-api')->plainTextToken;

            return $this->successResponse([
                'user'  => $user->toParentSelfApiArray(),
                'token' => $token,
                'email_verification_required' => true,
                'email_sent' => $emailSent,
                'kyc_required' => true,
                'next_step' => 'verify_email',
            ], $emailSent
                ? 'Registered successfully. A verification code has been sent to your email.'
                : 'Registered successfully. Verification email could not be sent — please use resend.', 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to register user');
        }
    }

    public function login(Request $request): JsonResponse
    {
        try {
            $accountType = $this->expectedAccountType($request);

            $validated = $request->validate([
                'email'    => ['required', 'string', 'email'],
                'password' => ['required', 'string'],
            ]);

            /** @var User|null $user */
            $user = User::where('email', $validated['email'])
                ->where('role', $accountType)
                ->first();

            if (!$user || !Hash::check($validated['password'], $user->password)) {
                $this->loginLogs->record(
                    $request,
                    LoginLog::CHANNEL_PARENT_API,
                    LoginLog::STATUS_FAILED,
                    $user,
                    $validated['email']
                );

                return $this->errorResponse('Invalid credentials', 401);
            }

            $user->tokens()->delete();
            $token = $user->createToken('parent-self-api')->plainTextToken;

            $this->loginLogs->record(
                $request,
                LoginLog::CHANNEL_PARENT_API,
                LoginLog::STATUS_SUCCESS,
                $user
            );

            if (is_null($user->email_verified_at)) {
                $emailSent = $this->emailVerification->issueAndSend($user)['sent'];

                return $this->successResponse([
                    'user' => $user->toParentSelfApiArray(),
                    'token' => $token,
                    'email_verification_required' => true,
                    'email_sent' => $emailSent,
                    'kyc_status' => $user->parentSelfKycStatus(),
                    'next_step' => 'verify_email',
                ], $emailSent
                    ? 'Logged in. A verification code has been sent to your email.'
                    : 'Logged in. Verification email could not be sent — please use resend.');
            }

            return $this->successResponse([
                'user'  => $user->toParentSelfApiArray(),
                'token' => $token,
                'kyc_status' => $user->parentSelfKycStatus(),
                'next_step' => $user->parentSelfNextStep(),
            ], 'Logged in successfully');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to login user');
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

            // In case mail is not configured, we still return a consistent response.
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

    public function sendEmailVerification(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $accountDenied = $this->denyUnlessAccountType($user, $request);
            if ($accountDenied) {
                return $accountDenied;
            }

            if ($user->email_verified_at) {
                return $this->successResponse([
                    'next_step' => $user->parentSelfNextStep(),
                ], 'Email already verified');
            }

            $result = $this->emailVerification->issueAndSend($user);
            if (! $result['sent']) {
                throw new RuntimeException('Unable to send verification email. Please try again in a moment.');
            }

            return $this->successResponse([
                'expires_in_minutes' => $result['expires_in_minutes'],
                'next_step' => 'verify_email',
            ], 'Verification code sent to your email');
        } catch (RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 503);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to send verification');
        }
    }

    public function verifyEmail(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'code' => ['required', 'string', 'size:6', 'regex:/^[0-9]{6}$/'],
            ]);

            $user = $request->user();
            $accountDenied = $this->denyUnlessAccountType($user, $request);
            if ($accountDenied) {
                return $accountDenied;
            }

            $row = DB::table('email_verification_tokens')
                ->where('user_id', $user->id)
                ->where('code', $validated['code'])
                ->whereNull('used_at')
                ->first();

            if (!$row) {
                return $this->errorResponse('Invalid verification code', 422);
            }
            if ($row->expires_at && now()->greaterThan($row->expires_at)) {
                return $this->errorResponse('Verification code expired', 422);
            }

            if (!$user->email_verified_at) {
                $user->email_verified_at = now();
                $user->save();
            }

            DB::table('email_verification_tokens')->where('id', $row->id)->update([
                'used_at' => now(),
                'updated_at' => now(),
            ]);

            $user = $user->fresh();

            return $this->successResponse([
                'user' => $user->toParentSelfApiArray(),
                'kyc_required' => $user->parentSelfKycStatus() !== 'approved',
                'next_step' => $user->parentSelfNextStep(),
            ], $user->needsPhoneVerification()
                ? 'Email verified successfully. Please verify your phone number.'
                : 'Email verified successfully. Please complete identity verification.');
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
            $accountDenied = $this->denyUnlessAccountType($user, $request);
            if ($accountDenied) {
                return $accountDenied;
            }

            $emailDenied = $this->denyUnlessEmailVerified($user);
            if ($emailDenied) {
                return $emailDenied;
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
            $accountDenied = $this->denyUnlessAccountType($user, $request);
            if ($accountDenied) {
                return $accountDenied;
            }

            $user = app(\App\Services\PhoneOtpService::class)->verify($user, $validated['code']);

            return $this->successResponse([
                'user' => $user->toParentSelfApiArray(),
                'next_step' => $user->parentSelfNextStep(),
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
