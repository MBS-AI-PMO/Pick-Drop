@php
  $peopleOpen = request()->is('users*')
    || request()->is('login-logs*')
    || request()->is('driver-verifications*')
    || request()->is('parent-self-verifications*')
    || request()->is('vehicle-verifications*');
  $locationsOpen = request()->is('locations*');
  $vehiclesOpen = request()->is('vehicles*') || request()->is('vehicle-categories*');
  $operationsOpen = request()->is('pickup-requests*')
    || request()->is('fleet*')
    || request()->is('routes*')
    || request()->is('schools*');
@endphp
<nav class="sidebar pd-sidebar">
  <div class="sidebar-header">
    <a href="{{ route('dashboard') }}" class="pd-brand" aria-label="PickDrop">
      <span class="pd-brand__mark">P</span>
      <span class="pd-brand__text">Pick<span>Drop</span></span>
    </a>
    <div class="sidebar-toggler not-active" aria-label="Toggle sidebar">
      <span></span>
      <span></span>
      <span></span>
    </div>
  </div>

  <div class="sidebar-body">
    <div class="pd-nav-eyebrow">Menu</div>
    <ul class="nav" id="sidebarNav">

      {{-- 1. Overview --}}
      <li class="pd-nav-section pd-nav-section--link">
        <a href="{{ route('dashboard') }}" class="pd-nav-section-toggle {{ active_class(['dashboard']) }}">
          <span class="pd-nav-ico"><i class="link-icon" data-lucide="layout-dashboard"></i></span>
          <span class="link-title">Dashboard</span>
        </a>
      </li>

      <li class="pd-nav-section pd-nav-section--link">
        <a href="{{ route('reports.index') }}" class="pd-nav-section-toggle {{ active_class(['reports', 'reports/*']) }}">
          <span class="pd-nav-ico"><i class="link-icon" data-lucide="bar-chart-2"></i></span>
          <span class="link-title">Reports</span>
        </a>
      </li>

      {{-- 2. People & places --}}
      <li class="pd-nav-section {{ $peopleOpen ? 'is-open' : '' }}">
        <a class="pd-nav-section-toggle {{ $peopleOpen ? 'active' : '' }}"
           data-bs-toggle="collapse" href="#nav-people"
           role="button"
           aria-expanded="{{ $peopleOpen ? 'true' : 'false' }}"
           aria-controls="nav-people">
          <span class="pd-nav-ico"><i class="link-icon" data-lucide="users"></i></span>
          <span class="link-title">People</span>
          <i class="link-arrow" data-lucide="chevron-down"></i>
        </a>
        <div class="collapse {{ $peopleOpen ? 'show' : '' }}" id="nav-people">
          <ul class="pd-nav-section-list">
            <li class="nav-item {{ active_class(['users', 'users/*']) }}">
              <a href="{{ route('users.index') }}" class="nav-link"><span class="link-title">Users</span></a>
            </li>
            <li class="nav-item {{ active_class(['login-logs', 'login-logs/*']) }}">
              <a href="{{ route('login-logs.index') }}" class="nav-link"><span class="link-title">Login logs</span></a>
            </li>
            <li class="nav-item {{ active_class(['driver-verifications', 'driver-verifications/*']) }}">
              <a href="{{ route('driver-verifications.index') }}" class="nav-link"><span class="link-title">Driver KYC</span></a>
            </li>
            <li class="nav-item {{ active_class(['parent-self-verifications', 'parent-self-verifications/*']) }}">
              <a href="{{ route('parent-self-verifications.index') }}" class="nav-link"><span class="link-title">Parent / Self KYC</span></a>
            </li>
            <li class="nav-item {{ active_class(['vehicle-verifications', 'vehicle-verifications/*']) }}">
              <a href="{{ route('vehicle-verifications.index') }}" class="nav-link"><span class="link-title">Vehicle Verification</span></a>
            </li>
          </ul>
        </div>
      </li>

      <li class="pd-nav-section {{ $locationsOpen ? 'is-open' : '' }}">
        <a class="pd-nav-section-toggle {{ $locationsOpen ? 'active' : '' }}"
           data-bs-toggle="collapse" href="#nav-locations"
           role="button"
           aria-expanded="{{ $locationsOpen ? 'true' : 'false' }}"
           aria-controls="nav-locations">
          <span class="pd-nav-ico"><i class="link-icon" data-lucide="map"></i></span>
          <span class="link-title">Locations</span>
          <i class="link-arrow" data-lucide="chevron-down"></i>
        </a>
        <div class="collapse {{ $locationsOpen ? 'show' : '' }}" id="nav-locations">
          <ul class="pd-nav-section-list">
            <li class="nav-item">
              <a href="{{ route('locations.cities.index') }}" class="nav-link {{ request()->is('locations') || request()->is('locations/cities') ? 'active' : '' }}">
                <span class="link-title">Manage Cities</span>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('locations.areas.index') }}" class="nav-link {{ request()->is('locations/areas') ? 'active' : '' }}">
                <span class="link-title">Manage Areas</span>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('locations.points.index') }}" class="nav-link {{ request()->is('locations/points') ? 'active' : '' }}">
                <span class="link-title">Pickup / Drop points</span>
              </a>
            </li>
          </ul>
        </div>
      </li>

      <li class="pd-nav-section {{ $vehiclesOpen ? 'is-open' : '' }}">
        <a class="pd-nav-section-toggle {{ $vehiclesOpen ? 'active' : '' }}"
           data-bs-toggle="collapse" href="#nav-vehicles"
           role="button"
           aria-expanded="{{ $vehiclesOpen ? 'true' : 'false' }}"
           aria-controls="nav-vehicles">
          <span class="pd-nav-ico"><i class="link-icon" data-lucide="bus"></i></span>
          <span class="link-title">Vehicles</span>
          <i class="link-arrow" data-lucide="chevron-down"></i>
        </a>
        <div class="collapse {{ $vehiclesOpen ? 'show' : '' }}" id="nav-vehicles">
          <ul class="pd-nav-section-list">
            <li class="nav-item">
              <a href="{{ route('vehicles.index') }}" class="nav-link {{ request()->is('vehicles*') ? 'active' : '' }}">
                <span class="link-title">List</span>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('vehicle-categories.index') }}" class="nav-link {{ request()->is('vehicle-categories*') ? 'active' : '' }}">
                <span class="link-title">Categories</span>
              </a>
            </li>
          </ul>
        </div>
      </li>

      {{-- 3. Day-to-day ops --}}
      <li class="pd-nav-section {{ $operationsOpen ? 'is-open' : '' }}">
        <a class="pd-nav-section-toggle {{ $operationsOpen ? 'active' : '' }}"
           data-bs-toggle="collapse" href="#nav-operations"
           role="button"
           aria-expanded="{{ $operationsOpen ? 'true' : 'false' }}"
           aria-controls="nav-operations">
          <span class="pd-nav-ico"><i class="link-icon" data-lucide="briefcase"></i></span>
          <span class="link-title">Operations</span>
          <i class="link-arrow" data-lucide="chevron-down"></i>
        </a>
        <div class="collapse {{ $operationsOpen ? 'show' : '' }}" id="nav-operations">
          <ul class="pd-nav-section-list">
            <li class="nav-item {{ active_class(['pickup-requests', 'pickup-requests/*']) }}">
              <a href="{{ route('pickup-requests.index') }}" class="nav-link"><span class="link-title">Pickup Requests</span></a>
            </li>
            <li class="nav-item {{ active_class(['fleet', 'fleet/*']) }}">
              <a href="{{ route('fleet.live.index') }}" class="nav-link"><span class="link-title">Live fleet</span></a>
            </li>
            <li class="nav-item {{ active_class(['routes', 'routes/*']) }}">
              <a href="{{ route('routes.index') }}" class="nav-link"><span class="link-title">Routes</span></a>
            </li>
            <li class="nav-item {{ active_class(['schools', 'schools/*']) }}">
              <a href="{{ route('schools.index') }}" class="nav-link"><span class="link-title">Institutions</span></a>
            </li>
          </ul>
        </div>
      </li>

      <li class="pd-nav-section pd-nav-section--link">
        <a href="{{ route('issues.index') }}" class="pd-nav-section-toggle {{ active_class(['issues', 'issues/*']) }}">
          <span class="pd-nav-ico"><i class="link-icon" data-lucide="flag"></i></span>
          <span class="link-title">Complaints</span>
        </a>
      </li>

      <li class="pd-nav-section pd-nav-section--link">
        <a href="{{ route('sos.index') }}" class="pd-nav-section-toggle {{ active_class(['sos', 'sos/*']) }}">
          <span class="pd-nav-ico"><i class="link-icon" data-lucide="alert-triangle"></i></span>
          <span class="link-title">SOS Alerts</span>
        </a>
      </li>

      <li class="pd-nav-section pd-nav-section--link">
        <a href="{{ route('holidays.index') }}" class="pd-nav-section-toggle {{ active_class(['holidays', 'holidays/*']) }}">
          <span class="pd-nav-ico"><i class="link-icon" data-lucide="calendar-days"></i></span>
          <span class="link-title">Calendar</span>
        </a>
      </li>

      {{-- 4. Money --}}
      <li class="pd-nav-section pd-nav-section--link">
        <a href="{{ route('payments.index') }}" class="pd-nav-section-toggle {{ active_class(['payments', 'payments/*']) }}">
          <span class="pd-nav-ico"><i class="link-icon" data-lucide="credit-card"></i></span>
          <span class="link-title">Payments</span>
        </a>
      </li>

      <li class="pd-nav-section pd-nav-section--link">
        <a href="{{ route('charges.index') }}" class="pd-nav-section-toggle {{ active_class(['charges', 'charges/*']) }}">
          <span class="pd-nav-ico"><i class="link-icon" data-lucide="badge-dollar-sign"></i></span>
          <span class="link-title">Pick-Drop Charges</span>
        </a>
      </li>

      <li class="pd-nav-section pd-nav-section--link">
        <a href="{{ route('driver-payroll.index') }}"
           class="pd-nav-section-toggle {{ active_class(['driver-payments', 'driver-payments/*', 'driver-payroll', 'driver-payroll/*', 'payrolls', 'payrolls/*']) }}">
          <span class="pd-nav-ico"><i class="link-icon" data-lucide="wallet"></i></span>
          <span class="link-title">Driver Payments</span>
        </a>
      </li>

    </ul>
  </div>

  <div class="sidebar-footer">
    <ul class="nav pd-footer-nav">
      <li class="nav-item {{ active_class(['platform-settings', 'platform-settings/*']) }}">
        <a href="{{ route('platform-settings.edit') }}" class="nav-link">
          <i class="link-icon" data-lucide="settings"></i>
          <span class="link-title">Settings</span>
        </a>
      </li>
      <li class="nav-item nav-item-logout">
        <a href="{{ route('login') }}" class="nav-link">
          <i class="link-icon" data-lucide="log-out"></i>
          <span class="link-title">Logout</span>
        </a>
      </li>
    </ul>
  </div>
</nav>
