<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Api\Driver\BaseApiController;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
                'home_address' => ['required', 'string', 'max:500'],
                'password' => ['required', 'string', 'min:6'],
                'phone'    => ['required', 'string', 'max:50', 'unique:users,phone'],
            ]);

            // Assuming "driver" is a role/flag on users table; adjust to your schema.
            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role'     => 'driver',
                'phone'    => $validated['phone'],
                'details'  => [
                    'home_address' => $validated['home_address'],
                ],
            ]);

            $token = $user->createToken('driver-api')->plainTextToken;

            return $this->successResponse([
                'user'  => $user,
                'token' => $token,
            ], 'Registered successfully', 201);
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
                'user'  => $user,
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
}

