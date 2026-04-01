<?php

namespace App\Http\Controllers\Api\ParentSelf;

use App\Http\Controllers\Api\ParentSelf\BaseApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class ProfileController extends BaseApiController
{
    public function show(Request $request): JsonResponse
    {
        return $this->successResponse($request->user(), 'Profile');
    }

    public function update(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'name'  => ['sometimes', 'string', 'max:255'],
                'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
                'address' => ['sometimes', 'nullable', 'string', 'max:500'],
                'contact' => ['sometimes', 'nullable', 'string', 'max:50'],
            ]);

            $details = is_array($user->details) ? $user->details : [];
            if (array_key_exists('address', $validated)) {
                $details['address'] = $validated['address'];
            }
            if (array_key_exists('contact', $validated)) {
                $details['contact'] = $validated['contact'];
            }

            unset($validated['address'], $validated['contact']);

            $user->fill($validated);
            $user->details = $details;
            $user->save();

            return $this->successResponse($user, 'Profile updated');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to update profile');
        }
    }
}

