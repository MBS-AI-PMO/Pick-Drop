<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Api\Driver\BaseApiController;
use App\Models\Area;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class ProfileController extends BaseApiController
{
    public function show(Request $request): JsonResponse
    {
        return $this->successResponse($request->user()->toDriverApiArray(), 'Driver profile');
    }

    public function update(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'name'              => ['sometimes', 'string', 'max:255'],
                'phone'             => ['sometimes', 'string', 'max:50', 'unique:users,phone,' . $user->id],
                'home_address'      => ['sometimes', 'string', 'max:500'],
                'city_id'           => ['sometimes', 'integer', 'exists:cities,id'],
                'service_areas'     => ['sometimes', 'array', 'min:1'],
                'service_areas.*'   => ['integer', 'exists:areas,id'],
            ]);

            if (array_key_exists('home_address', $validated)) {
                $details = $user->details ?? [];
                $details['home_address'] = $validated['home_address'];
                $user->details = $details;
                unset($validated['home_address']);
            }

            if (array_key_exists('city_id', $validated)
                && !array_key_exists('service_areas', $validated)) {
                $newCityId = (int) $validated['city_id'];
                $existing = $user->service_areas ?? [];
                if ($existing !== []) {
                    if (Area::whereIn('id', $existing)->where('city_id', '!=', $newCityId)->exists()) {
                        throw ValidationException::withMessages([
                            'city_id' => ['Current service_areas are not in this city. Send service_areas for the new city together with city_id.'],
                        ]);
                    }
                }
            }

            $cityForAreas = (int) ($validated['city_id'] ?? $user->city_id);

            if (array_key_exists('service_areas', $validated)) {
                if ($cityForAreas === 0) {
                    throw ValidationException::withMessages([
                        'city_id' => ['Set city_id on your profile before updating service_areas, or send city_id in the same request.'],
                    ]);
                }
                $norm = array_values(array_unique(array_map('intval', $validated['service_areas'])));
                $this->assertAreaIdsBelongToCity($cityForAreas, $norm);
                $user->service_areas = $norm;
                unset($validated['service_areas']);
            }

            if (array_key_exists('city_id', $validated)) {
                $user->city_id = $validated['city_id'];
                unset($validated['city_id']);
            }

            $user->fill($validated);
            $user->save();

            return $this->successResponse($user->fresh()->toDriverApiArray(), 'Profile updated');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to update profile');
        }
    }
}
