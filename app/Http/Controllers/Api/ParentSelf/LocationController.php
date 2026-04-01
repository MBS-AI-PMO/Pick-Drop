<?php

namespace App\Http\Controllers\Api\ParentSelf;

use App\Http\Controllers\Api\ParentSelf\BaseApiController;
use App\Models\City;
use Illuminate\Http\JsonResponse;
use Throwable;

class LocationController extends BaseApiController
{
    public function cities(): JsonResponse
    {
        try {
            $cities = City::select('id', 'name')->orderBy('name')->get();

            return $this->successResponse($cities, 'Cities');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch cities');
        }
    }

    public function areas(int $cityId): JsonResponse
    {
        try {
            $city = City::with(['areas' => function ($q) {
                $q->select('id', 'city_id', 'name');
            }])->findOrFail($cityId);

            return $this->successResponse($city->areas, 'Areas');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch areas');
        }
    }
}

