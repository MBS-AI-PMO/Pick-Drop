<?php

namespace App\Http\Controllers\Api\Driver;

use App\Models\Area;
use App\Models\City;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Throwable;

class ServiceAreaController extends BaseApiController
{
    /**
     * Optional later update: view city + service areas.
     * Onboarding mein service areas KYC (personal info) ke sath select hote hain.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validated = Validator::make($request->all(), [
                'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            ])->validate();

            $requestedCityId = isset($validated['city_id']) ? (int) $validated['city_id'] : null;
            if ($requestedCityId) {
                $this->assertCityIsActive($requestedCityId);
            }

            $cityId = $requestedCityId ?: $user->driverCityId();

            $cities = City::dropdownWithAreas();

            $city = $cityId
                ? City::query()->active()->select('id', 'name', 'latitude', 'longitude', 'status')->find($cityId)
                : null;

            // Areas only after a city is chosen — never mix areas from other cities.
            $availableAreas = $city
                ? Area::query()
                    ->active()
                    ->where('city_id', $city->id)
                    ->orderBy('name')
                    ->get()
                : collect();

            $selectedIds = array_values(array_unique(array_map('intval', $user->service_areas ?? [])));

            $selectedAreas = $availableAreas
                ->whereIn('id', $selectedIds)
                ->values()
                ->all();

            $selectedIdsForCity = collect($selectedAreas)->pluck('id')->map(fn ($id) => (int) $id)->values()->all();

            return $this->successResponse([
                'cities' => $cities,
                'city_id' => $city?->id,
                'city' => $city,
                'available_areas' => $availableAreas,
                'selected_area_ids' => $selectedIdsForCity,
                'service_areas' => $selectedAreas,
                'select_city_first' => $city === null,
                'service_areas_setup' => $user->hasServiceAreas(),
                'onboarding_complete' => $user->isOnboardingComplete(),
                'next_step' => $user->driverNextStep(),
            ], $city ? 'Service areas' : 'Select a city first to load its areas');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to load service areas');
        }
    }

    /**
     * Optional later update of city + service areas (not an onboarding step).
     */
    public function sync(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if ($user->kycStatus() !== 'approved') {
                return $this->errorResponse('Please complete and get approval for driver KYC first.', 403);
            }

            $raw = $request->input('service_areas', $request->input('area_ids'));
            if (!is_array($raw)) {
                $raw = [];
            }

            $validated = Validator::make(
                [
                    'city_id' => $request->input('city_id'),
                    'area_ids' => $raw,
                ],
                [
                    'city_id' => ['required', 'integer', 'exists:cities,id'],
                    'area_ids' => ['required', 'array', 'min:1'],
                    'area_ids.*' => ['integer', 'exists:areas,id'],
                ]
            )->validate();

            $cityId = (int) $validated['city_id'];
            $ids = array_values(array_unique(array_map('intval', $validated['area_ids'])));

            $this->assertAreaIdsBelongToCity($cityId, $ids);

            $user->city_id = $cityId;
            $user->service_areas = $ids;
            $user->save();

            $user->loadMissing('city');

            $selectedAreas = Area::whereIn('id', $ids)->orderBy('name')->get()->values()->all();

            return $this->successResponse([
                'city_id' => $cityId,
                'city' => $user->city,
                'selected_area_ids' => $ids,
                'service_areas' => $selectedAreas,
                'service_areas_setup' => true,
                'onboarding_complete' => $user->fresh()->isOnboardingComplete(),
                'next_step' => $user->fresh()->driverNextStep(),
            ], 'City and service areas updated successfully.');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to save service areas');
        }
    }
}
