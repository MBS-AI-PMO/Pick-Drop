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
     * Optional later update: view city + service areas + seats/hours.
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
                'available_seats' => $user->available_seats,
                'vehicle_seat_capacity' => $user->vehicleSeatCapacity(),
                'effective_available_seats' => $user->effectiveAvailableSeats(),
                'availability_hours' => $user->normalizedAvailabilityHours(),
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
     * Update city, service areas, available seats, and availability hours.
     */
    public function sync(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if ($user->kycStatus() !== 'approved') {
                return $this->errorResponse('Please complete and get approval for driver KYC first.', 403);
            }

            $raw = $request->input('service_areas', $request->input('area_ids'));
            if (! is_array($raw)) {
                $raw = [];
            }

            $vehicleCap = $user->vehicleSeatCapacity();

            $validated = Validator::make(
                [
                    'city_id' => $request->input('city_id'),
                    'area_ids' => $raw,
                    'available_seats' => $request->input('available_seats'),
                    'availability_hours' => $request->input('availability_hours'),
                ],
                [
                    'city_id' => ['required', 'integer', 'exists:cities,id'],
                    'area_ids' => ['required', 'array', 'min:1'],
                    'area_ids.*' => ['integer', 'exists:areas,id'],
                    'available_seats' => [
                        'nullable',
                        'integer',
                        'min:1',
                        $vehicleCap ? 'max:' . $vehicleCap : 'max:20',
                    ],
                    'availability_hours' => ['nullable', 'array', 'max:28'],
                    'availability_hours.*.day' => ['required_with:availability_hours', 'string'],
                    'availability_hours.*.start' => ['required_with:availability_hours', 'date_format:H:i'],
                    'availability_hours.*.end' => ['required_with:availability_hours', 'date_format:H:i', 'after:availability_hours.*.start'],
                ],
                [
                    'available_seats.max' => $vehicleCap
                        ? 'Available seats cannot exceed your vehicle capacity (' . $vehicleCap . ').'
                        : 'Available seats cannot exceed 20.',
                ]
            )->validate();

            $cityId = (int) $validated['city_id'];
            $ids = array_values(array_unique(array_map('intval', $validated['area_ids'])));

            $this->assertAreaIdsBelongToCity($cityId, $ids);

            $user->city_id = $cityId;
            $user->service_areas = $ids;

            if (array_key_exists('available_seats', $validated)) {
                $user->available_seats = $validated['available_seats'];
            }

            if (array_key_exists('availability_hours', $validated)) {
                $user->availability_hours = $this->normalizeHoursInput($validated['availability_hours'] ?? []);
            }

            $user->save();
            $user->loadMissing('city');

            $selectedAreas = Area::whereIn('id', $ids)->orderBy('name')->get()->values()->all();

            return $this->successResponse([
                'city_id' => $cityId,
                'city' => $user->city,
                'selected_area_ids' => $ids,
                'service_areas' => $selectedAreas,
                'available_seats' => $user->available_seats,
                'vehicle_seat_capacity' => $user->vehicleSeatCapacity(),
                'effective_available_seats' => $user->effectiveAvailableSeats(),
                'availability_hours' => $user->normalizedAvailabilityHours(),
                'service_areas_setup' => true,
                'onboarding_complete' => $user->fresh()->isOnboardingComplete(),
                'next_step' => $user->fresh()->driverNextStep(),
            ], 'City, service areas, seats and availability updated successfully.');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to save service areas');
        }
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array{day: string, start: string, end: string}>
     */
    private function normalizeHoursInput(array $rows): array
    {
        $dayMap = [
            'mon' => 'monday', 'monday' => 'monday',
            'tue' => 'tuesday', 'tuesday' => 'tuesday',
            'wed' => 'wednesday', 'wednesday' => 'wednesday',
            'thu' => 'thursday', 'thursday' => 'thursday',
            'fri' => 'friday', 'friday' => 'friday',
            'sat' => 'saturday', 'saturday' => 'saturday',
            'sun' => 'sunday', 'sunday' => 'sunday',
        ];

        $normalized = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $dayKey = strtolower(trim((string) ($row['day'] ?? '')));
            $day = $dayMap[$dayKey] ?? null;
            $start = substr((string) ($row['start'] ?? ''), 0, 5);
            $end = substr((string) ($row['end'] ?? ''), 0, 5);
            if (! $day || ! preg_match('/^\d{2}:\d{2}$/', $start) || ! preg_match('/^\d{2}:\d{2}$/', $end) || $start >= $end) {
                continue;
            }
            $normalized[] = compact('day', 'start', 'end');
        }

        return array_values($normalized);
    }
}
