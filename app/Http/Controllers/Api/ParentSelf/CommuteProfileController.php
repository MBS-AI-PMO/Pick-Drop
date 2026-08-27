<?php

namespace App\Http\Controllers\Api\ParentSelf;

use App\Models\City;
use App\Models\SelfCommuteProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class CommuteProfileController extends BaseApiController
{
    public function show(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $accountDenied = $this->denyUnlessAccountType($user, $request);
            if ($accountDenied) {
                return $accountDenied;
            }

            if (!$user->isSelfAccount()) {
                return $this->errorResponse('Only self accounts can manage commute locations.', 403);
            }

            $kycDenied = $this->denyUnlessKycApproved($user);
            if ($kycDenied) {
                return $kycDenied;
            }

            $profile = $user->commuteProfile;

            return $this->successResponse([
                'next_step' => $user->parentSelfNextStep(),
                'cities' => City::dropdownWithAreas(),
                'commute_profile' => $profile?->toApiArray(),
            ], 'Commute profile');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to load commute profile');
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $accountDenied = $this->denyUnlessAccountType($user, $request);
            if ($accountDenied) {
                return $accountDenied;
            }

            if (!$user->isSelfAccount()) {
                return $this->errorResponse('Only self accounts can manage commute locations.', 403);
            }

            $kycDenied = $this->denyUnlessKycApproved($user);
            if ($kycDenied) {
                return $kycDenied;
            }

            $validated = $request->validate([
                'city_id' => ['required', 'integer', 'exists:cities,id'],
                'pickup_area_id' => ['required', 'integer', 'exists:areas,id'],
                'pickup_point' => ['required', 'string', 'max:255'],
                'pickup_lat' => ['required', 'numeric', 'between:-90,90'],
                'pickup_lng' => ['required', 'numeric', 'between:-180,180'],
                'office_name' => ['nullable', 'string', 'max:255'],
                'drop_area_id' => ['required', 'integer', 'exists:areas,id'],
                'drop_point' => ['required', 'string', 'max:255'],
                'drop_lat' => ['required', 'numeric', 'between:-90,90'],
                'drop_lng' => ['required', 'numeric', 'between:-180,180'],
                'pickup_time' => ['required', 'date_format:H:i'],
                'drop_time' => ['required', 'date_format:H:i'],
                'days' => ['nullable', 'array', 'min:1'],
                'days.*' => ['string', 'max:20'],
            ]);

            $cityId = (int) $validated['city_id'];
            $this->assertAreaBelongsToCity($cityId, (int) $validated['pickup_area_id'], 'pickup_area_id');
            $this->assertAreaBelongsToCity($cityId, (int) $validated['drop_area_id'], 'drop_area_id');

            $days = $validated['days'] ?? ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

            $profile = SelfCommuteProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'city_id' => $cityId,
                    'pickup_area_id' => (int) $validated['pickup_area_id'],
                    'pickup_point' => $validated['pickup_point'],
                    'pickup_lat' => $validated['pickup_lat'],
                    'pickup_lng' => $validated['pickup_lng'],
                    'office_name' => $validated['office_name'] ?? null,
                    'drop_area_id' => (int) $validated['drop_area_id'],
                    'drop_point' => $validated['drop_point'],
                    'drop_lat' => $validated['drop_lat'],
                    'drop_lng' => $validated['drop_lng'],
                    'pickup_time' => $validated['pickup_time'],
                    'drop_time' => $validated['drop_time'],
                    'days' => array_values($days),
                ]
            );

            $user->update(['city_id' => $cityId]);
            $profile->load(['city', 'pickupArea', 'dropArea']);
            $user = $user->fresh();

            return $this->successResponse([
                'next_step' => $user->parentSelfNextStep(),
                'onboarding_complete' => $user->isParentSelfOnboardingComplete(),
                'commute_profile' => $profile->toApiArray(),
            ], 'Pickup, drop location and office timing saved');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to save commute profile');
        }
    }
}
