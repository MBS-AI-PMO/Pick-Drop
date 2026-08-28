<?php

namespace App\Services;

use App\Models\PickDropCharge;
use App\Models\PickupRequest;
use Illuminate\Support\Carbon;
use RuntimeException;

class ShiftFareService
{
    public const MIN_MONTHS = 1;

    /**
     * @param  list<string>  $days
     * @return array{
     *     duration_months: int,
     *     shift_start_date: string,
     *     shift_end_date: string,
     *     distance_km: float,
     *     trip_count: int,
     *     working_days: int,
     *     per_km_rate: float,
     *     per_trip_amount: float,
     *     estimated_amount: float,
     *     driver_monthly_rate: float,
     *     driver_payout_amount: float,
     *     driver_payout_due_on: string,
     *     currency: string
     * }
     */
    public function quote(
        float $pickupLat,
        float $pickupLng,
        float $dropLat,
        float $dropLng,
        array $days,
        int $durationMonths,
        ?string $startDate = null,
        array $stops = []
    ): array {
        $durationMonths = max(self::MIN_MONTHS, $durationMonths);
        $charge = PickDropCharge::query()->first();

        if (!$charge || !$charge->is_active) {
            throw new RuntimeException('Pick-drop charges are not configured. Please contact support.');
        }

        $rate = (float) $charge->per_km_rate;
        if ($rate <= 0) {
            throw new RuntimeException('Pick-drop charges are not set. Please contact support.');
        }

        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : now()->startOfDay();
        $end = $start->copy()->addMonths($durationMonths);
        $distanceKm = $this->routeDistanceKm($pickupLat, $pickupLng, $dropLat, $dropLng, $stops);
        $workingDays = $this->countWorkingDays($start, $end, $days);

        if ($workingDays < 1) {
            throw new RuntimeException('Selected days do not fall within this shift period.');
        }

        $tripCount = $workingDays * (count($stops) > 2 ? 1 : 2);
        $perTrip = round($distanceKm * $rate, 2);
        $amount = round($perTrip * $tripCount, 2);
        $driverMonthly = max(0, (float) $charge->driver_monthly_rate);
        $shiftEnd = $end->copy()->subDay();

        return [
            'duration_months' => $durationMonths,
            'shift_start_date' => $start->toDateString(),
            'shift_end_date' => $shiftEnd->toDateString(),
            'distance_km' => $distanceKm,
            'trip_count' => $tripCount,
            'working_days' => $workingDays,
            'per_km_rate' => $rate,
            'per_trip_amount' => $perTrip,
            'estimated_amount' => $amount,
            'driver_monthly_rate' => $driverMonthly,
            'driver_payout_amount' => round($driverMonthly * $durationMonths, 2),
            'driver_payout_due_on' => $start->copy()->addMonth()->subDay()->toDateString(),
            'currency' => strtoupper((string) ($charge->currency ?: 'PKR')),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function quoteFromRequest(PickupRequest $request): array
    {
        $start = $request->shift_start_date?->toDateString()
            ?: $request->scheduled_date?->toDateString();

        $stops = $request->relationLoaded('stops') || $request->stops()->exists()
            ? $request->stops()->get(['lat', 'lng'])->map(fn ($s) => ['lat' => $s->lat, 'lng' => $s->lng])->all()
            : [];

        return $this->quote(
            (float) $request->pickup_lat,
            (float) $request->pickup_lng,
            (float) $request->drop_lat,
            (float) $request->drop_lng,
            $request->days ?? [],
            (int) ($request->duration_months ?: self::MIN_MONTHS),
            $start,
            $stops
        );
    }

    public function apply(PickupRequest $request): PickupRequest
    {
        $quote = $this->quoteFromRequest($request);

        $request->fill([
            'duration_months' => $quote['duration_months'],
            'shift_start_date' => $quote['shift_start_date'],
            'shift_end_date' => $quote['shift_end_date'],
            'distance_km' => $quote['distance_km'],
            'trip_count' => $quote['trip_count'],
            'estimated_amount' => $quote['estimated_amount'],
            'driver_monthly_rate' => $quote['driver_monthly_rate'],
            'driver_payout_amount' => $quote['driver_payout_amount'],
            'driver_payout_due_on' => $quote['driver_payout_due_on'],
        ]);

        return $request;
    }

    /**
     * @param  list<array{lat?:float,lng?:float}>  $stops
     */
    public function routeDistanceKm(
        float $pickupLat,
        float $pickupLng,
        float $dropLat,
        float $dropLng,
        array $stops = []
    ): float {
        $points = [];
        foreach ($stops as $stop) {
            if (!isset($stop['lat'], $stop['lng'])) {
                continue;
            }
            $points[] = [(float) $stop['lat'], (float) $stop['lng']];
        }

        if (count($points) < 2) {
            $points = [[$pickupLat, $pickupLng], [$dropLat, $dropLng]];
        }

        $total = 0.0;
        for ($i = 1; $i < count($points); $i++) {
            $total += $this->distanceKm($points[$i - 1][0], $points[$i - 1][1], $points[$i][0], $points[$i][1]);
        }

        return max(0.5, round($total, 2));
    }

    public function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earth = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return round($earth * 2 * atan2(sqrt($a), sqrt(1 - $a)), 2);
    }

    /**
     * @param  list<string>  $days
     */
    public function countWorkingDays(Carbon $start, Carbon $end, array $days): int
    {
        $selected = [];
        foreach ($days as $day) {
            $key = strtolower(trim((string) $day));
            $map = [
                'monday' => Carbon::MONDAY, 'mon' => Carbon::MONDAY,
                'tuesday' => Carbon::TUESDAY, 'tue' => Carbon::TUESDAY,
                'wednesday' => Carbon::WEDNESDAY, 'wed' => Carbon::WEDNESDAY,
                'thursday' => Carbon::THURSDAY, 'thu' => Carbon::THURSDAY,
                'friday' => Carbon::FRIDAY, 'fri' => Carbon::FRIDAY,
                'saturday' => Carbon::SATURDAY, 'sat' => Carbon::SATURDAY,
                'sunday' => Carbon::SUNDAY, 'sun' => Carbon::SUNDAY,
            ];
            if (isset($map[$key])) {
                $selected[$map[$key]] = true;
            }
        }

        if ($selected === []) {
            return 0;
        }

        $count = 0;
        for ($cursor = $start->copy(); $cursor->lt($end); $cursor->addDay()) {
            if (isset($selected[$cursor->dayOfWeek])) {
                $count++;
            }
        }

        return $count;
    }
}
