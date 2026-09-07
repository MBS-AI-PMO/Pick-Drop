<?php

namespace App\Http\Controllers;

use App\Models\IssueReport;
use App\Models\Notification;
use App\Models\PickupRequest;
use App\Models\SchoolRoute;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $activeTripStatuses = ['accepted', 'picked_up', 'dropped'];

        $activeTrips = PickupRequest::with(['driver', 'vehicle.category', 'student', 'parent'])
            ->whereIn('status', $activeTripStatuses)
            ->latest()
            ->take(5)
            ->get()
            ->map(fn (PickupRequest $trip) => $this->formatTrip($trip));

        $pendingRequests = PickupRequest::with(['parent', 'city', 'area'])
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        $recentAlerts = Notification::latest()
            ->take(3)
            ->get()
            ->map(fn (Notification $notification) => $this->formatAlert($notification));

        $todaySchedule = SchoolRoute::with('vehicle')
            ->where('status', 'Active')
            ->orderByRaw('start_time is null, start_time asc')
            ->take(3)
            ->get()
            ->map(fn (SchoolRoute $route) => $this->formatSchedule($route));

        $totalVehicles = Vehicle::count();
        $activeVehicles = Vehicle::where('status', 'Active')->count();
        $assignedVehicles = Vehicle::whereNotNull('driver_id')->orWhereNotNull('route_id')->count();
        $totalUsers = User::count();
        $activeUsers = User::whereRaw('LOWER(TRIM(status)) = ?', ['active'])->count();
        $totalPickup = PickupRequest::count();
        $pendingPickup = PickupRequest::where('status', 'pending')->count();
        $alertsToday = Notification::whereDate('created_at', today())->count()
            + IssueReport::whereDate('created_at', today())->count();
        $alertsWeek = Notification::where('created_at', '>=', now()->subDays(7))->count()
            + IssueReport::where('created_at', '>=', now()->subDays(7))->count();
        $completedTrips = PickupRequest::where('status', 'completed')->count();
        $cancelledTrips = PickupRequest::where('status', 'cancelled')->count();
        $resolvedIssues = IssueReport::whereIn('status', ['resolved', 'closed'])->count();
        $totalIssues = IssueReport::count();

        $onTimePerformance = $this->percentage($completedTrips, $completedTrips + $cancelledTrips);
        $fleetUtilization = $this->percentage($assignedVehicles, $totalVehicles);
        $parentSatisfaction = $totalIssues > 0
            ? max(1, round(5 - (($totalIssues - $resolvedIssues) / $totalIssues), 1))
            : 5.0;

        $stats = [
            'vehicles' => $activeVehicles,
            'users' => $totalUsers,
            'pending_requests' => $pendingPickup,
            'alerts_today' => $alertsToday,
            'rings' => [
                'vehicles' => $this->percentage($activeVehicles, $totalVehicles),
                'users' => $this->percentage($activeUsers > 0 ? $activeUsers : $totalUsers, max($totalUsers, 1)),
                'pending' => $this->percentage($pendingPickup, max($totalPickup, 1)),
                'alerts' => $this->percentage($alertsToday, max($alertsWeek, 1)),
            ],
        ];

        $metrics = [
            'on_time_performance' => $onTimePerformance,
            'fleet_utilization' => $fleetUtilization,
            'parent_satisfaction' => $parentSatisfaction,
            'parent_satisfaction_stars' => (int) round($parentSatisfaction),
        ];

        return view('dashboard', [
            'stats' => $stats,
            'activeTrips' => $activeTrips,
            'activeTripsCount' => PickupRequest::whereIn('status', $activeTripStatuses)->count(),
            'pendingRequests' => $pendingRequests,
            'pendingRequestsCount' => PickupRequest::where('status', 'pending')->count(),
            'recentAlerts' => $recentAlerts,
            'recentAlertsCount' => Notification::where('is_read', false)->count(),
            'todaySchedule' => $todaySchedule,
            'metrics' => $metrics,
        ]);
    }

    private function formatTrip(PickupRequest $trip): array
    {
        $progress = match ($trip->status) {
            'accepted' => 35,
            'picked_up' => 65,
            'dropped' => 90,
            default => 0,
        };

        $progressClass = match ($trip->status) {
            'dropped' => 'bg-success',
            'picked_up' => 'bg-primary',
            default => 'bg-info',
        };

        return [
            'id' => $trip->id,
            'route_title' => $trip->requesterName(),
            'route_subtitle' => $trip->typeLabel() . ' · Request #' . $trip->id,
            'driver_name' => $trip->driver?->name ?? 'Unassigned',
            'driver_id' => $trip->driver ? 'ID: ' . $trip->driver->id : 'Waiting for driver',
            'vehicle_name' => $trip->vehicle?->name ?? 'No vehicle',
            'vehicle_meta' => $trip->vehicle?->category?->passenger_capacity
                ? $trip->vehicle->category->passenger_capacity . ' Seats'
                : ($trip->vehicle?->license_plate ?? 'N/A'),
            'status_label' => $trip->statusLabel(),
            'progress' => $progress,
            'progress_class' => $progressClass,
            'url' => route('pickup-requests.show', $trip),
        ];
    }

    private function formatAlert(Notification $notification): array
    {
        $type = strtolower($notification->type ?? 'info');

        return [
            'title' => $notification->title,
            'message' => $notification->message,
            'time' => $notification->created_at?->diffForHumans() ?? '',
            'icon' => match ($type) {
                'success' => 'check-circle',
                'warning' => 'clock',
                'danger', 'error' => 'alert-triangle',
                default => 'bell',
            },
            'color' => match ($type) {
                'success' => 'success',
                'warning' => 'warning',
                'danger', 'error' => 'danger',
                default => 'info',
            },
        ];
    }

    private function formatSchedule(SchoolRoute $route): array
    {
        $start = $this->timeFromRoute($route->start_time);
        $end = $this->timeFromRoute($route->end_time);
        $now = now();

        $status = 'Upcoming';
        $badgeStyle = 'background:#f3f4f6;color:#374151;';

        if ($start && $end && $now->between($start, $end)) {
            $status = 'In Progress';
            $badgeStyle = 'background:#dbeafe;color:#1e40af;';
        } elseif ($end && $now->greaterThan($end)) {
            $status = 'Completed';
            $badgeStyle = 'background:#d1fae5;color:#065f46;';
        }

        return [
            'title' => $route->name,
            'time' => ($start ? $start->format('g:i A') : 'N/A') . ' - ' . ($end ? $end->format('g:i A') : 'N/A'),
            'status' => $status,
            'badge_style' => $badgeStyle,
        ];
    }

    private function timeFromRoute($value): ?Carbon
    {
        if (!$value) {
            return null;
        }

        $parsed = $value instanceof Carbon ? $value : Carbon::parse($value);

        return today()->setTime(
            (int) $parsed->format('H'),
            (int) $parsed->format('i'),
            (int) $parsed->format('s')
        );
    }

    private function percentage(int $value, int $total): int
    {
        if ($total <= 0) {
            return 0;
        }

        return (int) round(($value / $total) * 100);
    }
}
