<?php

namespace App\Support;

use App\Models\Area;
use App\Models\City;
use Illuminate\Validation\ValidationException;

trait ValidatesCityAreas
{
    protected function assertCityIsActive(int $cityId, string $field = 'city_id'): void
    {
        $exists = City::query()
            ->where('id', $cityId)
            ->where('status', 'Active')
            ->exists();

        if (!$exists) {
            throw ValidationException::withMessages([
                $field => ['Selected city is not available. Choose an active city first.'],
            ]);
        }
    }

    /**
     * @param  list<int|string>  $areaIds
     */
    protected function assertAreaIdsBelongToCity(int $cityId, array $areaIds, string $field = 'service_areas'): void
    {
        $this->assertCityIsActive($cityId);

        $ids = array_values(array_unique(array_map('intval', $areaIds)));
        if ($ids === []) {
            throw ValidationException::withMessages([
                $field => ['Select at least one area that belongs to the selected city.'],
            ]);
        }

        $matchedCount = Area::query()
            ->whereIn('id', $ids)
            ->where('city_id', $cityId)
            ->where('status', 'Active')
            ->count();

        if ($matchedCount !== count($ids)) {
            throw ValidationException::withMessages([
                $field => ['All selected areas must belong to the selected city.'],
            ]);
        }
    }

    protected function assertAreaBelongsToCity(int $cityId, int $areaId, string $field = 'area_id'): void
    {
        $this->assertAreaIdsBelongToCity($cityId, [$areaId], $field);
    }
}
