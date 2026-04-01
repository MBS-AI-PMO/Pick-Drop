@extends('layout.master')

@section('content')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Dashboard</h4>
    <p class="text-secondary mb-0">Welcome back, Admin</p>
  </div>
</div>

{{-- Stats Cards --}}
<div class="row g-3 mb-4">
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body d-flex align-items-center justify-content-between py-3">
        <div>
          <p class="text-secondary fs-13px mb-1">Active Vehicles</p>
          <h3 class="mb-1 fw-bold">{{ number_format($stats['vehicles'] ?? 0) }}</h3>
          <span class="text-secondary fs-12px">Total registered vehicles</span>
        </div>
        <div class="w-50px h-50px d-flex align-items-center justify-content-center rounded-circle" style="background:rgba(var(--bs-primary-rgb),0.12);">
          <i data-lucide="bus" class="text-primary"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body d-flex align-items-center justify-content-between py-3">
        <div>
          <p class="text-secondary fs-13px mb-1">Total Users</p>
          <h3 class="mb-1 fw-bold">{{ number_format($stats['users'] ?? 0) }}</h3>
          <span class="text-secondary fs-12px">All registered users</span>
        </div>
        <div class="w-50px h-50px d-flex align-items-center justify-content-center rounded-circle" style="background:rgba(var(--bs-success-rgb),0.12);">
          <i data-lucide="users" class="text-success"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body d-flex align-items-center justify-content-between py-3">
        <div>
          <p class="text-secondary fs-13px mb-1">Total Routes</p>
          <h3 class="mb-1 fw-bold">{{ number_format($stats['routes'] ?? 0) }}</h3>
          <span class="text-secondary fs-12px">Configured school routes</span>
        </div>
        <div class="w-50px h-50px d-flex align-items-center justify-content-center rounded-circle" style="background:rgba(var(--bs-info-rgb),0.12);">
          <i data-lucide="map-pin" class="text-info"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body d-flex align-items-center justify-content-between py-3">
        <div>
          <p class="text-secondary fs-13px mb-1">Alerts Today</p>
          <h3 class="mb-1 fw-bold">{{ number_format($stats['alerts_today'] ?? 0) }}</h3>
          <span class="text-secondary fs-12px">System alerts logged today</span>
        </div>
        <div class="w-50px h-50px d-flex align-items-center justify-content-center rounded-circle" style="background:rgba(var(--bs-danger-rgb),0.12);">
          <i data-lucide="bell" class="text-danger"></i>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Live Fleet Tracking --}}
<div class="row g-3 mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <h6 class="card-title mb-0">Live Fleet Tracking</h6>
            <p class="text-secondary fs-12px mb-0">Showing all active vehicles</p>
          </div>
          <a href="{{ route('vehicles.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        {{-- Map Placeholder --}}
        <div class="rounded-3 overflow-hidden position-relative" style="height:260px; background:linear-gradient(135deg,#e8f0fe 0%,#f0f4ff 100%);">
          {{-- Grid lines for map feel --}}
          <svg width="100%" height="100%" style="position:absolute;top:0;left:0;opacity:0.3;">
            <defs>
              <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                <path d="M 40 0 L 0 0 0 40" fill="none" stroke="#94a3b8" stroke-width="0.5"/>
              </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#grid)"/>
          </svg>
          {{-- Map roads --}}
          <svg width="100%" height="100%" style="position:absolute;top:0;left:0;">
            <line x1="0" y1="130" x2="100%" y2="130" stroke="#cbd5e1" stroke-width="2"/>
            <line x1="0" y1="190" x2="100%" y2="190" stroke="#cbd5e1" stroke-width="2"/>
            <line x1="200" y1="0" x2="200" y2="100%" stroke="#cbd5e1" stroke-width="2"/>
            <line x1="450" y1="0" x2="450" y2="100%" stroke="#cbd5e1" stroke-width="2"/>
            <line x1="700" y1="0" x2="700" y2="100%" stroke="#cbd5e1" stroke-width="2"/>
          </svg>
          {{-- Vehicle dots --}}
          <div class="position-absolute" style="left:28%;top:42%;">
            <div class="d-flex align-items-center">
              <div class="w-14px h-14px rounded-circle bg-success border border-white border-2 shadow-sm"></div>
              <small class="ms-1 bg-white px-1 rounded shadow-sm" style="font-size:10px;">Bus #1001</small>
            </div>
          </div>
          <div class="position-absolute" style="left:55%;top:60%;">
            <div class="d-flex align-items-center">
              <div class="w-14px h-14px rounded-circle bg-danger border border-white border-2 shadow-sm"></div>
              <small class="ms-1 bg-white px-1 rounded shadow-sm" style="font-size:10px;">Bus #2415</small>
            </div>
          </div>
          <div class="position-absolute" style="left:72%;top:30%;">
            <div class="d-flex align-items-center">
              <div class="w-14px h-14px rounded-circle bg-success border border-white border-2 shadow-sm"></div>
              <small class="ms-1 bg-white px-1 rounded shadow-sm" style="font-size:10px;">Bus #1003</small>
            </div>
          </div>
          {{-- Map attribution --}}
          <div class="position-absolute bottom-0 end-0 me-2 mb-1" style="font-size:10px;color:#94a3b8;">Map Data © PickDrop</div>
          {{-- Zoom controls --}}
          <div class="position-absolute bottom-0 end-0 mb-4 me-2 d-flex flex-column gap-1">
            <button class="btn btn-sm btn-white border shadow-sm p-0" style="width:26px;height:26px;line-height:1;">+</button>
            <button class="btn btn-sm btn-white border shadow-sm p-0" style="width:26px;height:26px;line-height:1;">−</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Active Trips + Recent Alerts --}}
<div class="row g-3 mb-4">

  {{-- Active Trips --}}
  <div class="col-lg-7">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6 class="card-title mb-0">Active Trips</h6>
          <span class="badge bg-primary">24 Active</span>
        </div>
        <div class="table-responsive">
          <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
              <tr>
                <th class="border-0 py-2">Route</th>
                <th class="border-0 py-2">Driver</th>
                <th class="border-0 py-2">Vehicle</th>
                <th class="border-0 py-2">Status</th>
                <th class="border-0 py-2">Progress</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>
                  <p class="mb-0 fw-semibold">Route #1001</p>
                  <small class="text-secondary">Morning Pickup</small>
                </td>
                <td>
                  <p class="mb-0">Michael Johnson</p>
                  <small class="text-secondary">ID: MJ7346</small>
                </td>
                <td>
                  <p class="mb-0">Bus #2415</p>
                  <small class="text-secondary">28 Seats</small>
                </td>
                <td><span class="badge bg-success-subtle text-success">On Time</span></td>
                <td style="min-width:100px;">
                  <div class="progress" style="height:5px;">
                    <div class="progress-bar bg-success" style="width:65%"></div>
                  </div>
                  <small class="text-secondary">65% complete</small>
                </td>
              </tr>
              <tr>
                <td>
                  <p class="mb-0 fw-semibold">Route #1002</p>
                  <small class="text-secondary">Morning Pickup</small>
                </td>
                <td>
                  <p class="mb-0">Michael Johnson</p>
                  <small class="text-secondary">ID: MJ7346</small>
                </td>
                <td>
                  <p class="mb-0">Bus #2415</p>
                  <small class="text-secondary">28 Seats</small>
                </td>
                <td><span class="badge bg-success-subtle text-success">On Time</span></td>
                <td style="min-width:100px;">
                  <div class="progress" style="height:5px;">
                    <div class="progress-bar bg-success" style="width:80%"></div>
                  </div>
                  <small class="text-secondary">80% complete</small>
                </td>
              </tr>
              <tr>
                <td>
                  <p class="mb-0 fw-semibold">Route #1003</p>
                  <small class="text-secondary">Morning Pickup</small>
                </td>
                <td>
                  <p class="mb-0">Michael Johnson</p>
                  <small class="text-secondary">ID: MJ7346</small>
                </td>
                <td>
                  <p class="mb-0">Bus #2415</p>
                  <small class="text-secondary">28 Seats</small>
                </td>
                <td><span class="badge bg-danger-subtle text-danger">Delayed</span></td>
                <td style="min-width:100px;">
                  <div class="progress" style="height:5px;">
                    <div class="progress-bar bg-danger" style="width:45%"></div>
                  </div>
                  <small class="text-secondary">45% complete</small>
                </td>
              </tr>
              <tr>
                <td>
                  <p class="mb-0 fw-semibold">Route #1004</p>
                  <small class="text-secondary">Morning Pickup</small>
                </td>
                <td>
                  <p class="mb-0">Michael Johnson</p>
                  <small class="text-secondary">ID: MJ7346</small>
                </td>
                <td>
                  <p class="mb-0">Bus #2415</p>
                  <small class="text-secondary">28 Seats</small>
                </td>
                <td><span class="badge bg-success-subtle text-success">On Time</span></td>
                <td style="min-width:100px;">
                  <div class="progress" style="height:5px;">
                    <div class="progress-bar bg-success" style="width:66%"></div>
                  </div>
                  <small class="text-secondary">66% complete</small>
                </td>
              </tr>
              <tr>
                <td>
                  <p class="mb-0 fw-semibold">Route #1005</p>
                  <small class="text-secondary">Morning Pickup</small>
                </td>
                <td>
                  <p class="mb-0">Michael Johnson</p>
                  <small class="text-secondary">ID: MJ7346</small>
                </td>
                <td>
                  <p class="mb-0">Bus #2415</p>
                  <small class="text-secondary">28 Seats</small>
                </td>
                <td><span class="badge bg-success-subtle text-success">On Time</span></td>
                <td style="min-width:100px;">
                  <div class="progress" style="height:5px;">
                    <div class="progress-bar bg-success" style="width:75%"></div>
                  </div>
                  <small class="text-secondary">75% complete</small>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  {{-- Recent Alerts --}}
  <div class="col-lg-5">
    <div class="card mb-3">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6 class="card-title mb-0">Recent Alerts</h6>
          <span class="badge bg-danger">3 New</span>
        </div>
        <div class="d-flex flex-column gap-3">
          <div class="d-flex align-items-start gap-3 p-2 rounded-2" style="background:rgba(var(--bs-danger-rgb),0.07);">
            <div class="w-35px h-35px rounded-circle bg-danger d-flex align-items-center justify-content-center flex-shrink-0">
              <i data-lucide="alert-triangle" class="icon-sm text-white"></i>
            </div>
            <div>
              <p class="mb-0 fw-semibold">Vehicle Breakdown</p>
              <p class="text-secondary fs-12px mb-0">Bus #2415 reported engine issues</p>
              <p class="text-secondary fs-11px mb-0">10 mins ago</p>
            </div>
          </div>
          <div class="d-flex align-items-start gap-3 p-2 rounded-2" style="background:rgba(var(--bs-warning-rgb),0.07);">
            <div class="w-35px h-35px rounded-circle bg-warning d-flex align-items-center justify-content-center flex-shrink-0">
              <i data-lucide="clock" class="icon-sm text-white"></i>
            </div>
            <div>
              <p class="mb-0 fw-semibold">Route Delay</p>
              <p class="text-secondary fs-12px mb-0">Route #1002 delayed by 15 minutes</p>
              <p class="text-secondary fs-11px mb-0">26 mins ago</p>
            </div>
          </div>
          <div class="d-flex align-items-start gap-3 p-2 rounded-2" style="background:rgba(var(--bs-info-rgb),0.07);">
            <div class="w-35px h-35px rounded-circle bg-info d-flex align-items-center justify-content-center flex-shrink-0">
              <i data-lucide="wifi-off" class="icon-sm text-white"></i>
            </div>
            <div>
              <p class="mb-0 fw-semibold">GPS Signal Lost</p>
              <p class="text-secondary fs-12px mb-0">Bus #2410 lost GPS signal temporarily</p>
              <p class="text-secondary fs-11px mb-0">1 hour ago</p>
            </div>
          </div>
        </div>
        <div class="mt-3 d-flex justify-content-end">
          <a href="javascript:;" class="text-primary fs-13px">View All Alerts <i data-lucide="arrow-right" class="icon-xs"></i></a>
        </div>
      </div>
    </div>

    {{-- Today's Schedule --}}
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6 class="card-title mb-0">Today's Schedule</h6>
          <i data-lucide="calendar" class="icon-md text-secondary"></i>
        </div>
        <div class="d-flex flex-column gap-3">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <p class="mb-0 fw-semibold">Morning Pickup</p>
              <p class="text-secondary fs-12px mb-0">7:30 AM – 8:30 AM</p>
            </div>
            <span class="badge rounded-pill" style="background:#d1fae5;color:#065f46;">Completed</span>
          </div>
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <p class="mb-0 fw-semibold">Afternoon Drop-off</p>
              <p class="text-secondary fs-12px mb-0">3:30 PM – 4:30 PM</p>
            </div>
            <span class="badge rounded-pill" style="background:#dbeafe;color:#1e40af;">In Progress</span>
          </div>
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <p class="mb-0 fw-semibold">Evening Activity</p>
              <p class="text-secondary fs-12px mb-0">5:30 PM – 6:30 PM</p>
            </div>
            <span class="badge rounded-pill" style="background:#f3f4f6;color:#374151;">Upcoming</span>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

{{-- Key Metrics --}}
<div class="row g-3 mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <h6 class="card-title mb-3">Key Metrics</h6>
        <div class="row g-4">
          <div class="col-md-4">
            <p class="text-secondary fs-13px mb-1">On Time Performance</p>
            <div class="d-flex align-items-baseline gap-2 mb-2">
              <h4 class="mb-0 fw-bold">94%</h4>
              <span class="text-success fs-12px"><i data-lucide="arrow-up" class="icon-xs"></i> +2%</span>
            </div>
            <div class="progress" style="height:6px;">
              <div class="progress-bar bg-success" style="width:94%"></div>
            </div>
          </div>
          <div class="col-md-4">
            <p class="text-secondary fs-13px mb-1">Fleet Utilization</p>
            <div class="d-flex align-items-baseline gap-2 mb-2">
              <h4 class="mb-0 fw-bold">86%</h4>
              <span class="text-danger fs-12px"><i data-lucide="arrow-down" class="icon-xs"></i> -1%</span>
            </div>
            <div class="progress" style="height:6px;">
              <div class="progress-bar bg-primary" style="width:86%"></div>
            </div>
          </div>
          <div class="col-md-4">
            <p class="text-secondary fs-13px mb-1">Parent Satisfaction</p>
            <div class="d-flex align-items-baseline gap-2 mb-2">
              <h4 class="mb-0 fw-bold">4.8/5</h4>
              <span class="text-success fs-12px"><i data-lucide="arrow-up" class="icon-xs"></i> +0.2</span>
            </div>
            <div class="d-flex gap-1 mt-1">
              @for($i = 1; $i <= 5; $i++)
                @if($i <= 4)
                  <i data-lucide="star" class="icon-sm text-warning" style="fill:currentColor;"></i>
                @else
                  <i data-lucide="star" class="icon-sm text-warning" style="fill:currentColor;opacity:0.4;"></i>
                @endif
              @endfor
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection