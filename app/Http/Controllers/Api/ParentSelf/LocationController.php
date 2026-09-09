<?php

namespace App\Http\Controllers\Api\ParentSelf;

use App\Http\Controllers\Api\ParentSelf\BaseApiController;
use App\Models\City;
use App\Models\LocationPoint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

    /**
     * Admin-managed pickup/drop point presets for the selected city.
     */
    public function points(Request $request, City $city): JsonResponse
    {
        try {
            if (strcasecmp((string) $city->status, 'Active') !== 0) {
                return $this->errorResponse('Selected city is not available.', 404);
            }

            $query = LocationPoint::query()
                ->active()
                ->where('city_id', $city->id)
                ->with(['area']);

            if ($request->filled('area_id')) {
                $query->where(function ($q) use ($request) {
                    $q->whereNull('area_id')->orWhere('area_id', $request->integer('area_id'));
                });
            }

            if ($request->filled('type')) {
                $type = strtolower($request->string('type')->toString());
                if (in_array($type, [LocationPoint::TYPE_PICKUP, LocationPoint::TYPE_DROP], true)) {
                    $query->where(function ($q) use ($type) {
                        $q->where('type', $type)->orWhere('type', LocationPoint::TYPE_BOTH);
                    });
                }
            }

            $points = $query->orderBy('name')->get()->map(fn (LocationPoint $point) => $point->toApiArray());

            return $this->successResponse($points, 'Pickup/drop points');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch pickup/drop points');
        }
    }
}
