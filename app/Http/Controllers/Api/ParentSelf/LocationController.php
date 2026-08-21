<?php

namespace App\Http\Controllers\Api\ParentSelf;

use App\Http\Controllers\Api\ParentSelf\BaseApiController;
use App\Models\City;
use Illuminate\Http\JsonResponse;
use Throwable;

class LocationController extends BaseApiController
{
    /**
     * City dropdown: multiple cities. App selected city ki `areas` array use kare.
     */
    public function cities(): JsonResponse
    {
        try {
            return $this->successResponse(City::dropdownWithAreas(), 'Cities');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch cities');
        }
    }

    /**
     * Step 2: areas of the selected city only.
     */
    public function areas(City $city): JsonResponse
    {
        try {
            if (strcasecmp((string) $city->status, 'Active') !== 0) {
                return $this->errorResponse('Selected city is not available.', 404);
            }

            $areas = $city->areas()
                ->active()
                ->select('id', 'city_id', 'name', 'latitude', 'longitude', 'status')
                ->orderBy('name')
                ->get();

            return $this->successResponse($areas, 'Areas');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch areas');
        }
    }
}
