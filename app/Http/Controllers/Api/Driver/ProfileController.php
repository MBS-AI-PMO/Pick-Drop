<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Api\Driver\BaseApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class ProfileController extends BaseApiController
{
    public function show(Request $request): JsonResponse
    {
        return $this->successResponse($request->user(), 'Driver profile');
    }

    public function update(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'name'  => ['sometimes', 'string', 'max:255'],
                'phone' => ['sometimes', 'string', 'max:50', 'unique:users,phone,' . $user->id],
            ]);

            $user->fill($validated);
            $user->save();

            return $this->successResponse($user, 'Profile updated');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to update profile');
        }
    }
}

