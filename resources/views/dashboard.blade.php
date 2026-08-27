@extends('layout.master')

@section('content')
@php
  $tripBadge = [
    'Accepted' => 'background:#dbeafe;color:#1e40af;',
    'Picked Up' => 'background:#e0f2fe;color:#075985;',
    'Dropped' => 'background:#eef4ff;color:#3f6fd9;',
  ];
@endphp

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin gap-3">
  <div>
    <h4 class="mb-1">Dashboard</h4>
    <p class="text-secondary mb-0">Welcome back, {{ auth()->user()?->name ?? 'Admin' }} · live operations overview</p>
  </div>
  <a href="{{ route('reports.index') }}" class="btn btn-success d-flex align-items-center gap-1">
    <i data-lucide="bar-chart-2" style="width:15px;height:15px;"></i> View reports
  </a>
</div>

<div class="dashboard-stats row g-3 mb-4">
  <div class="col-sm-6 col-xl-3">
    <a href="{{ route('vehicles.index') }}" class="dashboard-stat-link">
      <div class="card dashboard-stat-card h-100">
        <div class="card-body d-flex align-items-center justify-content-between py-3">
          <div>
            <p class="text-secondary fs-13px mb-1">Active vehicles</p>
            <h3 class="mb-1 fw-bold">{{ number_format($stats['vehicles'] ?? 0) }}</h3>
            <span class="text-secondary fs-12px">Total registered vehicles</span>
          </div>
          <div class="dashboard-stat-icon w-50px h-50px d-flex align-items-center justify-content-center rounded-circle" style="background:rgba(var(--bs-primary-rgb),0.12);">
            <i data-lucide="bus" class="text-primary"></i>
          </div>
        </div>
      </div>
    </a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a href="{{ route('users.index') }}" class="dashboard-stat-link">
      <div class="card dashboard-stat-card h-100">
        <div class="card-body d-flex align-items-center justify-content-between py-3">
          <div>
            <p class="text-secondary fs-13px mb-1">Total users</p>
            <h3 class="mb-1 fw-bold">{{ number_format($stats['users'] ?? 0) }}</h3>
            <span class="text-secondary fs-12px">All registered users</span>
          </div>
          <div class="dashboard-stat-icon w-50px h-50px d-flex align-items-center justify-content-center rounded-circle" style="background:rgba(34,197,94,0.12);">
            <i data-lucide="users" style="color:#3f6fd9;"></i>
          </div>
        </div>
      </div>
    </a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a href="{{ route('pickup-requests.index', ['status' => 'pending']) }}" class="dashboard-stat-link">
      <div class="card dashboard-stat-card h-100">
        <div class="card-body d-flex align-items-center justify-content-between py-3">
          <div>
            <p class="text-secondary fs-13px mb-1">Pending requests</p>
            <h3 class="mb-1 fw-bold">{{ number_format($stats['pending_requests'] ?? 0) }}</h3>
            <span class="text-secondary fs-12px">Waiting for a driver</span>
          </div>
          <div class="dashboard-stat-icon w-50px h-50px d-flex align-items-center justify-content-center rounded-circle" style="background:rgba(14,165,233,0.12);">
            <i data-lucide="clipboard-list" class="text-info"></i>
          </div>
        </div>
      </div>
    </a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a href="{{ route('notifications.index') }}" class="dashboard-stat-link">
      <div class="card dashboard-stat-card h-100">
        <div class="card-body d-flex align-items-center justify-content-between py-3">
          <div>
            <p class="text-secondary fs-13px mb-1">Alerts today</p>
            <h3 class="mb-1 fw-bold">{{ number_format($stats['alerts_today'] ?? 0) }}</h3>
            <span class="text-danger fs-12px">System alerts logged today</span>
          </div>
          <div class="dashboard-stat-icon w-50px h-50px d-flex align-items-center justify-content-center rounded-circle" style="background:rgba(230,57,70,0.12);">
            <i data-lucide="bell" class="text-danger"></i>
          </div>
        </div>
      </div>
    </a>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-lg-7">
    <div class="card dashboard-card h-100">
      <div class="card-body pb-0">
        <div class="dashboard-card-header d-flex justify-content-between align-items-center mb-3">
          <div>
            <h6 class="card-title mb-0">Active trips</h6>
            <p class="text-secondary fs-12px mb-0">Who requested, who is driving</p>
          </div>
          <div class="d-flex align-items-center gap-2">
            <span class="badge rounded-pill px-3 py-1" style="background:#dbeafe;color:#1e40af;">{{ number_format($activeTripsCount ?? 0) }} Active</span>
            <a href="{{ route('pickup-requests.index') }}" class="text-primary fs-13px">View all</a>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-hover dashboard-table align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="py-3">Requested by</th>
                <th class="py-3">Driver</th>
                <th class="py-3">Vehicle</th>
                <th class="py-3">Status</th>
                <th class="py-3" style="min-width:140px;">Progress</th>
              </tr>
            </thead>
            <tbody>
              @forelse($activeTrips as $trip)
                <tr role="button" onclick="window.location='{{ $trip['url'] }}'">
                  <td class="py-3">
                    <p class="mb-0 fw-semibold">{{ $trip['route_title'] }}</p>
                    <small class="text-secondary">{{ $trip['route_subtitle'] }}</small>
                  </td>
                  <td class="py-3">
                    <p class="mb-0">{{ $trip['driver_name'] }}</p>
                    <small class="text-secondary">{{ $trip['driver_id'] }}</small>
                  </td>
                  <td class="py-3">
                    <p class="mb-0">{{ $trip['vehicle_name'] }}</p>
                    <small class="text-secondary">{{ $trip['vehicle_meta'] }}</small>
                  </td>
                  <td class="py-3">
                    <span class="badge rounded-pill px-3 py-1" style="{{ $tripBadge[$trip['status_label']] ?? 'background:#e0f2fe;color:#075985;' }}">
                      {{ $trip['status_label'] }}
                    </span>
                  </td>
                  <td class="py-3">
                    <div class="d-flex justify-content-between mb-1">
                      <span class="fs-12px text-secondary">{{ $trip['progress'] }}%</span>
                    </div>
                    <div class="progress" style="height:6px;">
                      <div class="progress-bar {{ $trip['progress_class'] }}" style="width:{{ $trip['progress'] }}%"></div>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center text-secondary py-5">No active trips found</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card dashboard-card mb-3">
      <div class="card-body">
        <div class="dashboard-card-header d-flex justify-content-between align-items-center mb-3">
          <div>
            <h6 class="card-title mb-0">Waiting for driver</h6>
            <p class="text-secondary fs-12px mb-0">Pending pickup requests</p>
          </div>
          <span class="badge rounded-pill px-3 py-1" style="background:#fef9c3;color:#92400e;">{{ number_format($pendingRequestsCount ?? 0) }} Pending</span>
        </div>
        <div class="d-flex flex-column gap-3">
          @forelse($pendingRequests as $pending)
            <a href="{{ route('pickup-requests.show', $pending) }}" class="text-decoration-none text-reset">
              <div class="dashboard-schedule-item d-flex justify-content-between align-items-center">
                <div>
                  <p class="mb-0 fw-semibold">{{ $pending->requesterName() }}</p>
                  <p class="text-secondary fs-12px mb-0">
                    {{ $pending->typeLabel() }} · #{{ $pending->id }}
                    @if($pending->area?->name) · {{ $pending->area->name }}@endif
                  </p>
                </div>
                <span class="badge rounded-pill px-3 py-1" style="{{ $pending->statusBadgeStyle() }}">{{ $pending->statusLabel() }}</span>
              </div>
            </a>
          @empty
            <div class="text-center text-secondary py-3">No pending requests</div>
          @endforelse
        </div>
        <div class="mt-3 d-flex justify-content-end">
          <a href="{{ route('pickup-requests.index', ['status' => 'pending']) }}" class="text-primary fs-13px">View pending <i data-lucide="arrow-right" class="icon-xs"></i></a>
        </div>
      </div>
    </div>

    <div class="card dashboard-card mb-3">
      <div class="card-body">
        <div class="dashboard-card-header d-flex justify-content-between align-items-center mb-3">
          <div>
            <h6 class="card-title mb-0">Recent alerts</h6>
            <p class="text-secondary fs-12px mb-0">Latest system activity</p>
          </div>
          <span class="badge rounded-pill px-3 py-1" style="background:#fee2e2;color:#991b1b;">{{ number_format($recentAlertsCount ?? 0) }} New</span>
        </div>
        <div class="d-flex flex-column gap-3">
          @forelse($recentAlerts as $alert)
            <div class="dashboard-alert-item d-flex align-items-start gap-3 p-2 rounded-2" style="background:rgba(var(--bs-{{ $alert['color'] }}-rgb),0.07);">
              <div class="w-35px h-35px rounded-circle bg-{{ $alert['color'] }} d-flex align-items-center justify-content-center flex-shrink-0">
                <i data-lucide="{{ $alert['icon'] }}" class="icon-sm text-white"></i>
              </div>
              <div>
                <p class="mb-0 fw-semibold">{{ $alert['title'] }}</p>
                <p class="text-secondary fs-12px mb-0">{{ \Illuminate\Support\Str::limit($alert['message'], 58) }}</p>
                <p class="text-secondary fs-11px mb-0">{{ $alert['time'] }}</p>
              </div>
            </div>
          @empty
            <div class="text-center text-secondary py-3">No recent alerts found</div>
          @endforelse
        </div>
        <div class="mt-3 d-flex justify-content-end">
          <a href="{{ route('notifications.index') }}" class="text-primary fs-13px">View all alerts <i data-lucide="arrow-right" class="icon-xs"></i></a>
        </div>
      </div>
    </div>

    <div class="card dashboard-card">
      <div class="card-body">
        <div class="dashboard-card-header d-flex justify-content-between align-items-center mb-3">
          <div>
            <h6 class="card-title mb-0">Today's schedule</h6>
            <p class="text-secondary fs-12px mb-0">Active school routes</p>
          </div>
          <i data-lucide="calendar" class="icon-md text-secondary"></i>
        </div>
        <div class="d-flex flex-column gap-3">
          @forelse($todaySchedule as $index => $schedule)
            <div class="dashboard-schedule-item d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center gap-2">
                <span class="report-rank">{{ $index + 1 }}</span>
                <div>
                  <p class="mb-0 fw-semibold">{{ $schedule['title'] }}</p>
                  <p class="text-secondary fs-12px mb-0">{{ $schedule['time'] }}</p>
                </div>
              </div>
              <span class="badge rounded-pill px-3 py-1" style="{{ $schedule['badge_style'] }}">{{ $schedule['status'] }}</span>
            </div>
          @empty
            <div class="text-center text-secondary py-3">No routes scheduled today</div>
          @endforelse
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card dashboard-card h-100">
      <div class="card-body">
        <div class="dashboard-card-header mb-3">
          <h6 class="card-title mb-0">On-time performance</h6>
        </div>
        <div class="d-flex align-items-baseline gap-2 mb-2">
          <h4 class="mb-0 fw-bold">{{ $metrics['on_time_performance'] ?? 0 }}%</h4>
          <span class="fs-12px" style="color:#3f6fd9;">Live</span>
        </div>
        <div class="progress" style="height:7px;">
          <div class="progress-bar" style="width:{{ $metrics['on_time_performance'] ?? 0 }}%; background:#3f6fd9;"></div>
        </div>
        <p class="text-secondary fs-12px mb-0 mt-3">Completed trips vs cancelled trips</p>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card dashboard-card h-100">
      <div class="card-body">
        <div class="dashboard-card-header mb-3">
          <h6 class="card-title mb-0">Fleet utilization</h6>
        </div>
        <div class="d-flex align-items-baseline gap-2 mb-2">
          <h4 class="mb-0 fw-bold">{{ $metrics['fleet_utilization'] ?? 0 }}%</h4>
          <span class="fs-12px" style="color:#3f6fd9;">Live</span>
        </div>
        <div class="progress" style="height:7px;">
          <div class="progress-bar bg-primary" style="width:{{ $metrics['fleet_utilization'] ?? 0 }}%"></div>
        </div>
        <p class="text-secondary fs-12px mb-0 mt-3">Vehicles assigned to drivers</p>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card dashboard-card h-100">
      <div class="card-body">
        <div class="dashboard-card-header mb-3">
          <h6 class="card-title mb-0">Parent satisfaction</h6>
        </div>
        <div class="d-flex align-items-baseline gap-2 mb-2">
          <h4 class="mb-0 fw-bold">{{ number_format($metrics['parent_satisfaction'] ?? 0, 1) }}/5</h4>
          <span class="fs-12px" style="color:#3f6fd9;">Live</span>
        </div>
        <div class="d-flex gap-1 mt-1">
          @for($i = 1; $i <= 5; $i++)
            @if($i <= ($metrics['parent_satisfaction_stars'] ?? 0))
              <i data-lucide="star" class="icon-sm text-warning" style="fill:currentColor;"></i>
            @else
              <i data-lucide="star" class="icon-sm text-warning" style="fill:currentColor;opacity:0.4;"></i>
            @endif
          @endfor
        </div>
        <p class="text-secondary fs-12px mb-0 mt-3">Based on resolved vs open issues</p>
      </div>
    </div>
  </div>
</div>

<style>
  .report-rank {
    width: 24px;
    height: 24px;
    border-radius: 8px;
    background: #eef2f7;
    color: #1d3557;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
    flex-shrink: 0;
  }
  [data-bs-theme="dark"] .report-rank {
    background: #2a3140;
    color: #f1f5f9;
  }
  .dashboard-table tr[role="button"] {
    cursor: pointer;
  }
</style>
@endsection
