<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\City;
use App\Models\DriverPayrollBill;
use App\Models\DriverVerification;
use App\Models\DriverVehicleVerification;
use App\Models\Invoice;
use App\Models\IssueReport;
use App\Models\Payment;
use App\Models\PickupRequest;
use App\Models\SchoolRoute;
use App\Models\SosAlert;
use App\Models\Student;
use App\Models\User;
use App\Models\Vehicle;
use App\Support\AppPagination;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        [$period, $from, $to] = $this->resolvePeriod($request);
        $filters = $this->resolveFilters($request);

        $tripQuery = $this->tripsInPeriod($from, $to);
        $this->applyFilters($tripQuery, $filters);

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
            'revenue' => (float) $this->paidInvoices($from, $to, $filters)->sum('total'),
            'pending_invoices' => $this->pendingInvoices($from, $to, $filters),
            'refunds' => (float) $this->refunds($from, $to, $filters)->sum('amount'),
            'driver_payouts' => (float) $this->driverPayouts($from, $to, $filters)->sum('calculated_amount'),
            'sos' => $this->sosAlerts($from, $to, $filters)->count(),
            'complaints' => $this->complaints($from, $to, $filters)->count(),
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

        $trips = (clone $tripQuery)
            ->with(['parent', 'student', 'driver', 'city', 'area', 'vehicle'])
            ->latest()
            ->paginate(AppPagination::PER_PAGE)
            ->withQueryString();

        return view('pickdrop.reports.index', [
            'period' => $period,
            'from' => $from,
            'to' => $to,
            'periodLabel' => $this->periodLabel($period, $from, $to),
            'filters' => $filters,
            'filterCities' => City::query()->orderBy('name')->get(['id', 'name']),
            'filterDrivers' => User::query()
                ->whereRaw('LOWER(role) = ?', ['driver'])
                ->orderBy('name')
                ->get(['id', 'name']),
            'kpis' => $kpis,
            'snapshot' => $snapshot,
            'trend' => $this->tripTrend($from, $to, $period, clone $tripQuery),
            'statusBreakdown' => $statusBreakdown,
            'topCities' => $this->topCities(clone $tripQuery),
            'topDrivers' => $this->topDrivers(clone $tripQuery, $filters),
            'trips' => $trips,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        [$period, $from, $to] = $this->resolvePeriod($request);
        $filters = $this->resolveFilters($request);

        $filename = 'pickdrop-trips-' . $from->format('Ymd') . '-' . $to->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($from, $to, $filters) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'ID', 'Type', 'Status', 'Payment', 'Parent', 'Student', 'Driver',
                'City', 'Area', 'Pickup', 'Drop', 'Created at',
            ]);

            $query = $this->tripsInPeriod($from, $to)->with(['parent', 'student', 'driver', 'city', 'area']);
            $this->applyFilters($query, $filters);

            $query->orderByDesc('id')
                ->chunk(200, function ($rows) use ($handle) {
                    foreach ($rows as $trip) {
                        fputcsv($handle, [
                            $trip->id,
                            $trip->type,
                            $trip->status,
                            $trip->payment_status,
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

            if ($from->diffInDays($to, true) > 90) {
                $from = $to->copy()->subDays(90)->startOfDay();
            }

            return [$period, $from, $to];
        }

        return [$period, $now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()];
    }

    /**
     * @return array{city_id:?int,driver_id:?int,status:?string,type:?string,payment_status:?string}
     */
    private function resolveFilters(Request $request): array
    {
        $status = strtolower(trim($request->string('status')->toString()));
        $type = strtolower(trim($request->string('type')->toString()));
        $payment = strtolower(trim($request->string('payment_status')->toString()));

        return [
            'city_id' => $request->filled('city_id') ? $request->integer('city_id') : null,
            'driver_id' => $request->filled('driver_id') ? $request->integer('driver_id') : null,
            'status' => in_array($status, ['pending', 'accepted', 'picked_up', 'dropped', 'completed', 'cancelled'], true) ? $status : null,
            'type' => in_array($type, ['parent', 'self'], true) ? $type : null,
            'payment_status' => in_array($payment, ['unpaid', 'pending_confirmation', 'paid'], true) ? $payment : null,
        ];
    }

    /**
     * Requests that were created in the window, scheduled in it, or whose
     * service shift overlaps it. School runs are stored as one request with
     * a shift range, so a created_at-only window hides trips that are running now.
     */
    private function tripsInPeriod(Carbon $from, Carbon $to): Builder
    {
        $query = PickupRequest::query();
        $this->constrainTripPeriod($query, $from, $to);

        return $query;
    }

    private function constrainTripPeriod(Builder $query, Carbon $from, Carbon $to): void
    {
        $start = $from->toDateString();
        $end = $to->toDateString();

        $query->where(function (Builder $query) use ($from, $to, $start, $end) {
            $query->whereBetween('created_at', [$from, $to])
                ->orWhereBetween('scheduled_date', [$start, $end])
                ->orWhere(function (Builder $query) use ($start, $end) {
                    $query->whereNotNull('shift_start_date')
                        ->where('shift_start_date', '<=', $end)
                        ->where(function (Builder $query) use ($start) {
                            $query->where('shift_end_date', '>=', $start)
                                ->orWhere(function (Builder $query) use ($start) {
                                    $query->whereNull('shift_end_date')
                                        ->where('shift_start_date', '>=', $start);
                                });
                        });
                });
        });
    }

    /**
     * @param  array{city_id:?int,driver_id:?int,status:?string,type:?string,payment_status:?string}  $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        if ($filters['city_id']) {
            $query->where('city_id', $filters['city_id']);
        }
        if ($filters['driver_id']) {
            $query->where('driver_id', $filters['driver_id']);
        }
        if ($filters['status']) {
            $query->whereRaw('LOWER(status) = ?', [$filters['status']]);
        }
        if ($filters['type']) {
            $query->whereRaw('LOWER(type) = ?', [$filters['type']]);
        }
        if ($filters['payment_status']) {
            $query->whereRaw('LOWER(payment_status) = ?', [$filters['payment_status']]);
        }
    }

    /**
     * @param  array{city_id:?int,driver_id:?int,status:?string,type:?string,payment_status:?string}  $filters
     */
    private function hasTripFilters(array $filters): bool
    {
        return (bool) ($filters['city_id'] || $filters['driver_id'] || $filters['status'] || $filters['type'] || $filters['payment_status']);
    }

    /**
     * @param  array{city_id:?int,driver_id:?int,status:?string,type:?string,payment_status:?string}  $filters
     */
    private function applyRelatedTripFilters(Builder $query, string $relation, array $filters): void
    {
        if (! $this->hasTripFilters($filters)) {
            return;
        }

        $query->whereHas($relation, function (Builder $trip) use ($filters) {
            $this->applyFilters($trip, $filters);
        });
    }

    /**
     * @param  array{city_id:?int,driver_id:?int,status:?string,type:?string,payment_status:?string}  $filters
     */
    private function paidInvoices(Carbon $from, Carbon $to, array $filters): Builder
    {
        $query = Invoice::query()
            ->where('status', Invoice::STATUS_PAID)
            ->whereBetween('paid_at', [$from, $to]);
        $this->applyRelatedTripFilters($query, 'pickupRequest', $filters);

        return $query;
    }

    /**
     * @param  array{city_id:?int,driver_id:?int,status:?string,type:?string,payment_status:?string}  $filters
     */
    private function pendingInvoices(Carbon $from, Carbon $to, array $filters): int
    {
        $start = $from->toDateString();
        $end = $to->toDateString();

        $query = Invoice::query()
            ->whereIn('status', [Invoice::STATUS_UNPAID, Invoice::STATUS_PARTIAL, Invoice::STATUS_OVERDUE]);
        $this->applyRelatedTripFilters($query, 'pickupRequest', $filters);
        $query->where(function (Builder $query) use ($from, $to, $start, $end, $filters) {
            $query->whereBetween('issue_date', [$start, $end])
                ->orWhereBetween('due_date', [$start, $end])
                ->orWhereBetween('created_at', [$from, $to])
                ->orWhereHas('pickupRequest', function (Builder $trip) use ($from, $to, $filters) {
                    $this->constrainTripPeriod($trip, $from, $to);
                    $this->applyFilters($trip, $filters);
                });
        });

        return (int) $query->count();
    }

    /**
     * @param  array{city_id:?int,driver_id:?int,status:?string,type:?string,payment_status:?string}  $filters
     */
    private function refunds(Carbon $from, Carbon $to, array $filters): Builder
    {
        $query = Payment::query()
            ->where('status', Payment::STATUS_REFUNDED)
            ->whereBetween('refunded_at', [$from, $to]);
        $this->applyRelatedTripFilters($query, 'invoice.pickupRequest', $filters);

        return $query;
    }

    /**
     * @param  array{city_id:?int,driver_id:?int,status:?string,type:?string,payment_status:?string}  $filters
     */
    private function driverPayouts(Carbon $from, Carbon $to, array $filters): Builder
    {
        $query = DriverPayrollBill::query()
            ->where('status', DriverPayrollBill::STATUS_PAID)
            ->whereBetween('paid_at', [$from, $to]);
        $this->applyRelatedTripFilters($query, 'pickupRequest', $filters);

        return $query;
    }

    /**
     * @param  array{city_id:?int,driver_id:?int,status:?string,type:?string,payment_status:?string}  $filters
     */
    private function sosAlerts(Carbon $from, Carbon $to, array $filters): Builder
    {
        $query = SosAlert::query()->whereBetween('created_at', [$from, $to]);
        $this->applyRelatedTripFilters($query, 'pickupRequest', $filters);

        return $query;
    }

    /**
     * @param  array{city_id:?int,driver_id:?int,status:?string,type:?string,payment_status:?string}  $filters
     */
    private function complaints(Carbon $from, Carbon $to, array $filters): Builder
    {
        $query = IssueReport::query()->whereBetween('created_at', [$from, $to]);
        $this->applyRelatedTripFilters($query, 'pickupRequest', $filters);

        return $query;
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
    private function tripTrend(Carbon $from, Carbon $to, string $period, Builder $trips): array
    {
        $rows = (clone $trips)->get([
            'id',
            'created_at',
            'scheduled_date',
            'shift_start_date',
            'shift_end_date',
            'pickup_time',
        ]);

        if ($period === 'daily') {
            $points = [];
            for ($hour = 0; $hour < 24; $hour += 2) {
                $value = $rows->filter(function (PickupRequest $trip) use ($hour, $from) {
                    $tripHour = $this->tripHour($trip, $from);

                    return $tripHour === $hour || $tripHour === $hour + 1;
                })->count();
                $points[] = [
                    'label' => Carbon::createFromTime($hour)->format('g A'),
                    'value' => $value,
                ];
            }

            return $points;
        }

        $points = [];
        $cursor = $from->copy()->startOfDay();
        $end = $to->copy()->startOfDay();

        while ($cursor->lte($end)) {
            $day = $cursor->copy();
            $points[] = [
                'label' => $period === 'monthly' ? $cursor->format('j') : $cursor->format('D j'),
                'value' => $rows->filter(fn (PickupRequest $trip) => $this->tripCoversDate($trip, $day))->count(),
            ];
            $cursor->addDay();
        }

        return $points;
    }

    private function tripCoversDate(PickupRequest $trip, Carbon $day): bool
    {
        $date = $day->toDateString();

        if ($trip->created_at && $trip->created_at->toDateString() === $date) {
            return true;
        }

        if ($trip->scheduled_date && $trip->scheduled_date->toDateString() === $date) {
            return true;
        }

        if (! $trip->shift_start_date) {
            return false;
        }

        $start = $trip->shift_start_date->toDateString();
        $end = $trip->shift_end_date?->toDateString();
        if ($end === null) {
            return $start === $date;
        }

        return $start <= $date && $end >= $date;
    }

    private function tripHour(PickupRequest $trip, Carbon $day): ?int
    {
        if ($trip->created_at && $trip->created_at->toDateString() === $day->toDateString()) {
            return (int) $trip->created_at->format('G');
        }

        $time = trim((string) $trip->pickup_time);
        if ($time === '' || ! str_contains($time, ':')) {
            return null;
        }

        return (int) substr($time, 0, 2);
    }

    /**
     * @return list<array{name: string, trips: int}>
     */
    private function topCities(Builder $trips): array
    {
        $rows = (clone $trips)
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
     * @param  array{city_id:?int,driver_id:?int,status:?string,type:?string,payment_status:?string}  $filters
     * @return list<array{name: string, trips: int}>
     */
    private function topDrivers(Builder $trips, array $filters): array
    {
        $query = (clone $trips)->whereNotNull('driver_id');
        if (! $filters['status']) {
            $query->whereRaw('LOWER(status) != ?', ['cancelled']);
        }

        $rows = $query
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
