<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\City;
use App\Models\DriverVerification;
use App\Models\DriverVehicleVerification;
use App\Models\IssueReport;
use App\Models\PickupRequest;
use App\Models\SchoolRoute;
use App\Models\Student;
use App\Models\User;
use App\Models\Vehicle;
use App\Support\AppPagination;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        [$period, $from, $to] = $this->resolvePeriod($request);

        $tripQuery = PickupRequest::query()->whereBetween('created_at', [$from, $to]);

        $totalTrips = (clone $tripQuery)->count();
        $completedTrips = (clone $tripQuery)->where('status', 'completed')->count();
        $cancelledTrips = (clone $tripQuery)->where('status', 'cancelled')->count();
        $activeTrips = (clone $tripQuery)->whereIn('status', ['accepted', 'picked_up', 'dropped'])->count();
        $pendingTrips = (clone $tripQuery)->where('status', 'pending')->count();

        $statusCounts = (clone $tripQuery)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusBreakdown = collect([
            'pending' => 'Pending',
            'accepted' => 'Accepted',
            'picked_up' => 'Picked up',
            'dropped' => 'Dropped',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ])->map(function (string $label, string $status) use ($statusCounts, $totalTrips) {
            $count = (int) ($statusCounts[$status] ?? 0);

            return [
                'key' => $status,
                'label' => $label,
                'count' => $count,
                'percent' => $this->percentage($count, $totalTrips),
            ];
        })->values();

        $kpis = [
            'total_trips' => $totalTrips,
            'completed' => $completedTrips,
            'cancelled' => $cancelledTrips,
            'active' => $activeTrips,
            'pending' => $pendingTrips,
            'completion_rate' => $this->percentage($completedTrips, $totalTrips),
            'cancellation_rate' => $this->percentage($cancelledTrips, $totalTrips),
        ];

        $snapshot = [
            'vehicles_total' => Vehicle::count(),
            'vehicles_active' => Vehicle::where('status', 'Active')->count(),
            'vehicles_assigned' => Vehicle::whereNotNull('driver_id')->count(),
            'drivers' => User::whereRaw('LOWER(role) = ?', ['driver'])->count(),
            'parents' => User::whereRaw('LOWER(role) in (?, ?)', ['parent', 'self'])->count(),
            'students' => Student::count(),
            'routes_active' => SchoolRoute::where('status', 'Active')->count(),
            'cities' => City::count(),
            'areas' => Area::count(),
            'kyc_pending' => DriverVerification::where('status', DriverVerification::STATUS_PENDING)->count(),
            'kyc_approved' => DriverVerification::where('status', DriverVerification::STATUS_APPROVED)->count(),
            'vehicle_kyc_pending' => DriverVehicleVerification::where('status', DriverVehicleVerification::STATUS_PENDING)->count(),
            'issues_open' => IssueReport::whereIn('status', ['open', 'in_progress'])->count(),
            'issues_resolved' => IssueReport::whereIn('status', ['resolved', 'closed'])->count(),
        ];

        $snapshot['fleet_utilization'] = $this->percentage(
            $snapshot['vehicles_assigned'],
            $snapshot['vehicles_total']
        );

        $trips = PickupRequest::with(['parent', 'student', 'driver', 'city', 'area', 'vehicle'])
            ->whereBetween('created_at', [$from, $to])
            ->latest()
            ->paginate(AppPagination::PER_PAGE)
            ->withQueryString();

        return view('pickdrop.reports.index', [
            'period' => $period,
            'from' => $from,
            'to' => $to,
            'periodLabel' => $this->periodLabel($period, $from, $to),
            'kpis' => $kpis,
            'snapshot' => $snapshot,
            'trend' => $this->tripTrend($from, $to, $period),
            'statusBreakdown' => $statusBreakdown,
            'topCities' => $this->topCities($from, $to),
            'topDrivers' => $this->topDrivers($from, $to),
            'trips' => $trips,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        [$period, $from, $to] = $this->resolvePeriod($request);

        $filename = 'pickdrop-trips-' . $from->format('Ymd') . '-' . $to->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($from, $to) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'ID', 'Type', 'Status', 'Parent', 'Student', 'Driver',
                'City', 'Area', 'Pickup', 'Drop', 'Created at',
            ]);

            PickupRequest::with(['parent', 'student', 'driver', 'city', 'area'])
                ->whereBetween('created_at', [$from, $to])
                ->orderByDesc('id')
                ->chunk(200, function ($rows) use ($handle) {
                    foreach ($rows as $trip) {
                        fputcsv($handle, [
                            $trip->id,
                            $trip->type,
                            $trip->status,
                            $trip->parent?->name,
                            $trip->student?->name,
                            $trip->driver?->name,
                            $trip->city?->name,
                            $trip->area?->name,
                            $trip->pickup_point,
                            $trip->drop_point,
                            optional($trip->created_at)->format('Y-m-d H:i'),
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * @return array{0: string, 1: Carbon, 2: Carbon}
     */
    private function resolvePeriod(Request $request): array
    {
        $period = $request->string('period')->toString();
        if (! in_array($period, ['daily', 'weekly', 'monthly', 'custom'], true)) {
            $period = 'weekly';
        }

        $now = now();

        if ($period === 'daily') {
            return [$period, $now->copy()->startOfDay(), $now->copy()->endOfDay()];
        }

        if ($period === 'monthly') {
            return [$period, $now->copy()->startOfMonth(), $now->copy()->endOfDay()];
        }

        if ($period === 'custom') {
            $from = $request->filled('from')
                ? Carbon::parse($request->input('from'))->startOfDay()
                : $now->copy()->startOfMonth();
            $to = $request->filled('to')
                ? Carbon::parse($request->input('to'))->endOfDay()
                : $now->copy()->endOfDay();

            if ($from->greaterThan($to)) {
                [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
            }

            if ($from->diffInDays($to) > 90) {
                $from = $to->copy()->subDays(90)->startOfDay();
            }

            return [$period, $from, $to];
        }

        return [$period, $now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()];
    }

    private function periodLabel(string $period, Carbon $from, Carbon $to): string
    {
        return match ($period) {
            'daily' => 'Today · ' . $from->format('d M Y'),
            'weekly' => $from->format('d M') . ' – ' . $to->format('d M Y'),
            'monthly' => $from->format('F Y'),
            default => $from->format('d M Y') . ' – ' . $to->format('d M Y'),
        };
    }

    /**
     * @return list<array{label: string, value: int}>
     */
    private function tripTrend(Carbon $from, Carbon $to, string $period): array
    {
        if ($period === 'daily') {
            $rows = PickupRequest::query()
                ->whereBetween('created_at', [$from, $to])
                ->selectRaw('HOUR(created_at) as bucket, COUNT(*) as total')
                ->groupBy('bucket')
                ->pluck('total', 'bucket');

            $points = [];
            for ($hour = 0; $hour < 24; $hour += 2) {
                $value = (int) ($rows[$hour] ?? 0) + (int) ($rows[$hour + 1] ?? 0);
                $points[] = [
                    'label' => Carbon::createFromTime($hour)->format('g A'),
                    'value' => $value,
                ];
            }

            return $points;
        }

        $rows = PickupRequest::query()
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE(created_at) as bucket, COUNT(*) as total')
            ->groupBy('bucket')
            ->pluck('total', 'bucket');

        $points = [];
        $cursor = $from->copy()->startOfDay();
        $end = $to->copy()->startOfDay();

        while ($cursor->lte($end)) {
            $key = $cursor->toDateString();
            $points[] = [
                'label' => $period === 'monthly' ? $cursor->format('j') : $cursor->format('D j'),
                'value' => (int) ($rows[$key] ?? 0),
            ];
            $cursor->addDay();
        }

        return $points;
    }

    /**
     * @return list<array{name: string, trips: int}>
     */
    private function topCities(Carbon $from, Carbon $to): array
    {
        $rows = PickupRequest::query()
            ->whereBetween('created_at', [$from, $to])
            ->whereNotNull('city_id')
            ->selectRaw('city_id, COUNT(*) as trips')
            ->groupBy('city_id')
            ->orderByDesc('trips')
            ->limit(6)
            ->get();

        $cities = City::whereIn('id', $rows->pluck('city_id'))->get()->keyBy('id');

        return $rows->map(fn ($row) => [
            'name' => $cities[$row->city_id]->name ?? 'Unknown city',
            'trips' => (int) $row->trips,
        ])->all();
    }

    /**
     * @return list<array{name: string, trips: int}>
     */
    private function topDrivers(Carbon $from, Carbon $to): array
    {
        $rows = PickupRequest::query()
            ->whereBetween('created_at', [$from, $to])
            ->whereNotNull('driver_id')
            ->where('status', '!=', 'cancelled')
            ->selectRaw('driver_id, COUNT(*) as trips')
            ->groupBy('driver_id')
            ->orderByDesc('trips')
            ->limit(6)
            ->get();

        $drivers = User::whereIn('id', $rows->pluck('driver_id'))->get()->keyBy('id');

        return $rows->map(fn ($row) => [
            'name' => $drivers[$row->driver_id]->name ?? 'Unassigned',
            'trips' => (int) $row->trips,
        ])->all();
    }

    private function percentage(int $value, int $total): int
    {
        if ($total <= 0) {
            return 0;
        }

        return (int) round(($value / $total) * 100);
    }
}
