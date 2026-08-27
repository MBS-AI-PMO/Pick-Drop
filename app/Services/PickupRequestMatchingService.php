<?php

namespace App\Services;

use App\Models\PickupRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PickupRequestMatchingService
{
    /**
     * @return list<int>
     */
    public function serviceAreaIds(User $driver): array
    {
        return collect($driver->service_areas ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Pickup area plus optional drop area from the request.
     *
     * @return list<int>
     */
    public function requestAreaIds(PickupRequest $pickupRequest): array
    {
        return collect([$pickupRequest->area_id, $pickupRequest->drop_area_id])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function driverIsEligible(User $driver): bool
    {
        if (strcasecmp(trim((string) $driver->role), 'driver') !== 0) {
            return false;
        }

        if (strcasecmp(trim((string) $driver->status), 'Active') !== 0) {
            return false;
        }

        return $driver->isOnboardingComplete();
    }

    public function driverCanServe(User $driver, PickupRequest $pickupRequest): bool
    {
        if (!$this->driverIsEligible($driver)) {
            return false;
        }

        $cityId = $driver->driverCityId();
        if (!$cityId || (int) $pickupRequest->city_id !== $cityId) {
            return false;
        }

        $serviceAreas = $this->serviceAreaIds($driver);
        $requestAreas = $this->requestAreaIds($pickupRequest);

        if ($serviceAreas === [] || $requestAreas === []) {
            return false;
        }

        return count(array_intersect($serviceAreas, $requestAreas)) > 0;
    }

    public function constrainAvailableQuery(Builder $query, User $driver): Builder
    {
        $cityId = $driver->driverCityId();
        $serviceAreaIds = $this->serviceAreaIds($driver);

        if (!$this->driverIsEligible($driver) || !$cityId || $serviceAreaIds === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query
            ->where('status', 'pending')
            ->whereNull('driver_id')
            ->where('city_id', $cityId)
            ->where(function (Builder $q) use ($serviceAreaIds) {
                $q->whereIn('area_id', $serviceAreaIds)
                    ->orWhereIn('drop_area_id', $serviceAreaIds);
            })
            ->whereDoesntHave('driverRejections', function (Builder $q) use ($driver) {
                $q->where('driver_id', $driver->id);
            });
    }

    /**
     * Active, verified drivers whose city and service areas match this request.
     *
     * @return Collection<int, User>
     */
    public function eligibleDrivers(PickupRequest $pickupRequest, bool $excludeRejectors = true): Collection
    {
        $requestAreas = $this->requestAreaIds($pickupRequest);
        if ($requestAreas === [] || !$pickupRequest->city_id) {
            return collect();
        }

        $rejectedIds = $excludeRejectors
            ? $pickupRequest->driverRejections()->pluck('driver_id')->map(fn ($id) => (int) $id)->all()
            : [];

        return User::query()
            ->where('role', 'driver')
            ->where('status', 'Active')
            ->whereNotNull('service_areas')
            ->with(['driverVerification'])
            ->get()
            ->filter(function (User $driver) use ($pickupRequest, $rejectedIds) {
                if ($rejectedIds !== [] && in_array((int) $driver->id, $rejectedIds, true)) {
                    return false;
                }

                return $this->driverCanServe($driver, $pickupRequest);
            })
            ->values();
    }
}
