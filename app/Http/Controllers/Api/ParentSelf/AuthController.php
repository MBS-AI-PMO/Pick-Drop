<?php

namespace App\Http\Controllers\Api\ParentSelf;

use App\Http\Controllers\Api\ParentSelf\BaseApiController;
use App\Mail\EmailVerificationCodeMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class AuthController extends BaseApiController
{
    public function register(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name'     => ['required', 'string', 'max:255'],
                'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
                'address'  => ['nullable', 'string', 'max:500'],
                'contact'  => ['nullable', 'string', 'max:50'],
                'password' => ['required', 'string', 'min:6', 'confirmed'],
                'type'     => ['required', 'in:parent,self'],
            ]);

            $user = DB::transaction(function () use ($validated) {
                $u = User::create([
                    'name'     => $validated['name'],
                    'email'    => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'role'     => $validated['type'], // store as parent/self in role (adjust if you use another column)
                    'status'   => 'active',
                    'details'  => [
                        'address' => $validated['address'] ?? null,
                        'contact' => $validated['contact'] ?? null,
                    ],
                ]);
                $this->storeEmailVerificationCodeAndNotify($u);

                return $u;
            });

            $token = $user->createToken('parent-self-api')->plainTextToken;

            return $this->successResponse([
                'user'  => $user,
                'token' => $token,
            ], 'Registered successfully. A verification code has been sent to your email.', 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to register user');
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
            $user = User::where('email', $validated['email'])->first();

            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return $this->errorResponse('Invalid credentials', 401);
            }

            $user->tokens()->delete();
            $token = $user->createToken('parent-self-api')->plainTextToken;

            return $this->successResponse([
                'user'  => $user,
                'token' => $token,
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

            if ($user->email_verified_at) {
                return $this->successResponse(null, 'Email already verified');
            }

            $this->storeEmailVerificationCodeAndNotify($user);

            return $this->successResponse([
                'expires_in_minutes' => 30,
            ], 'Verification code sent to your email');
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

            return $this->successResponse($user, 'Email verified successfully');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to verify email');
        }
    }

    /**
     * Persist a new 6-digit code and email it to the user.
     */
    private function storeEmailVerificationCodeAndNotify(User $user): void
    {
        DB::table('email_verification_tokens')
            ->where('user_id', $user->id)
            ->whereNull('used_at')
            ->delete();

        $code = (string) random_int(100000, 999999);

        DB::table('email_verification_tokens')->insert([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => now()->addMinutes(30),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Mail::to($user->email)->send(new EmailVerificationCodeMail($code, $user->name));
    }
}

