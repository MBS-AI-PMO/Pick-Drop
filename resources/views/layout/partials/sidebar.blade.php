<nav class="sidebar">
  <div class="sidebar-header">
    <a href="{{ route('dashboard') }}" class="sidebar-brand brand-logo">Pick<span>Drop</span></a>
    <div class="sidebar-toggler not-active">
      <span></span>
      <span></span>
      <span></span>
    </div>
  </div>
  <div class="sidebar-body">
    <ul class="nav" id="sidebarNav">
      <li class="nav-item nav-category">Main</li>

      <li class="nav-item {{ active_class(['dashboard']) }}">
        <a href="{{ route('dashboard') }}" class="nav-link">
          <i class="link-icon" data-lucide="layout-dashboard"></i>
          <span class="link-title">Dashboard</span>
        </a>
      </li>

      <li class="nav-item nav-category">Management</li>

      <li class="nav-item {{ active_class(['users', 'users/*']) }}">
        <a href="{{ route('users.index') }}" class="nav-link">
          <i class="link-icon" data-lucide="users"></i>
          <span class="link-title">Users</span>
        </a>
      </li>

      <li class="nav-item {{ active_class(['driver-verifications', 'driver-verifications/*']) }}">
        <a href="{{ route('driver-verifications.index') }}" class="nav-link">
          <i class="link-icon" data-lucide="badge-check"></i>
          <span class="link-title">Driver KYC</span>
        </a>
      </li>

      <li class="nav-item {{ active_class(['parent-self-verifications', 'parent-self-verifications/*']) }}">
        <a href="{{ route('parent-self-verifications.index') }}" class="nav-link">
          <i class="link-icon" data-lucide="user-round-check"></i>
          <span class="link-title">Parent / Self KYC</span>
        </a>
      </li>

      <li class="nav-item {{ active_class(['vehicle-verifications', 'vehicle-verifications/*']) }}">
        <a href="{{ route('vehicle-verifications.index') }}" class="nav-link">
          <i class="link-icon" data-lucide="car-front"></i>
          <span class="link-title">Vehicle Verification</span>
        </a>
      </li>

      <li class="nav-item has-sub {{ request()->is('locations*') ? 'active' : '' }}">
        <a class="nav-link {{ request()->is('locations*') ? 'active' : '' }}"
           data-bs-toggle="collapse" href="#locations-nav" role="button"
           aria-expanded="{{ request()->is('locations*') ? 'true' : 'false' }}"
           aria-controls="locations-nav">
          <i class="link-icon" data-lucide="map"></i>
          <span class="link-title">Locations</span>
          <i class="link-arrow" data-lucide="chevron-down"></i>
        </a>
        <div class="collapse {{ request()->is('locations*') ? 'show' : '' }}" id="locations-nav">
          <ul class="nav sub-menu">
            <li class="nav-item">
              <a href="{{ route('locations.cities.index') }}" class="nav-link {{ request()->is('locations') || request()->is('locations/cities') ? 'active' : '' }}">Manage Cities</a>
            </li>
            <li class="nav-item">
              <a href="{{ route('locations.areas.index') }}" class="nav-link {{ request()->is('locations/areas') ? 'active' : '' }}">Manage Areas</a>
            </li>
          </ul>
        </div>
      </li>

      <li class="nav-item has-sub {{ request()->is('vehicles*') || request()->is('vehicle-categories*') ? 'active' : '' }}">
        <a class="nav-link {{ request()->is('vehicles*') || request()->is('vehicle-categories*') ? 'active' : '' }}"
           data-bs-toggle="collapse" href="#vehicles-nav" role="button"
           aria-expanded="{{ request()->is('vehicles*') || request()->is('vehicle-categories*') ? 'true' : 'false' }}"
           aria-controls="vehicles-nav">
          <i class="link-icon" data-lucide="bus"></i>
          <span class="link-title">Vehicles</span>
          <i class="link-arrow" data-lucide="chevron-down"></i>
        </a>
        <div class="collapse {{ request()->is('vehicles*') || request()->is('vehicle-categories*') ? 'show' : '' }}" id="vehicles-nav">
          <ul class="nav sub-menu">
            <li class="nav-item">
              <a href="{{ route('vehicles.index') }}" class="nav-link {{ request()->is('vehicles*') ? 'active' : '' }}">List</a>
            </li>
            <li class="nav-item">
              <a href="{{ route('vehicle-categories.index') }}" class="nav-link {{ request()->is('vehicle-categories*') ? 'active' : '' }}">Categories</a>
            </li>
          </ul>
        </div>
      </li>

      <li class="nav-item {{ active_class(['pickup-requests', 'pickup-requests/*']) }}">
        <a href="{{ route('pickup-requests.index') }}" class="nav-link">
          <i class="link-icon" data-lucide="clipboard-list"></i>
          <span class="link-title">Pickup Requests</span>
        </a>
      </li>

      <li class="nav-item {{ active_class(['issues', 'issues/*']) }}">
        <a href="{{ route('issues.index') }}" class="nav-link">
          <i class="link-icon" data-lucide="flag"></i>
          <span class="link-title">Issues</span>
        </a>
      </li>

      <li class="nav-item {{ active_class(['sos', 'sos/*']) }}">
        <a href="{{ route('sos.index') }}" class="nav-link">
          <i class="link-icon" data-lucide="alert-triangle"></i>
          <span class="link-title">SOS Alerts</span>
        </a>
      </li>

      <li class="nav-item {{ active_class(['schools', 'schools/*']) }}">
        <a href="{{ route('schools.index') }}" class="nav-link">
          <i class="link-icon" data-lucide="school"></i>
          <span class="link-title">Schools</span>
        </a>
      </li>

      <li class="nav-item {{ active_class(['holidays', 'holidays/*']) }}">
        <a href="{{ route('holidays.index') }}" class="nav-link">
          <i class="link-icon" data-lucide="calendar-days"></i>
          <span class="link-title">Holidays</span>
        </a>
      </li>

      <li class="nav-item {{ active_class(['routes', 'routes/*']) }}">
        <a href="{{ route('routes.index') }}" class="nav-link">
          <i class="link-icon" data-lucide="map-pin"></i>
          <span class="link-title">Routes</span>
        </a>
      </li>

      <li class="nav-item nav-category">Finance</li>

      <li class="nav-item has-sub {{ request()->is('payments*') ? 'active' : '' }}">
        <a class="nav-link {{ request()->is('payments*') ? 'active' : '' }}"
           data-bs-toggle="collapse" href="#payments-nav" role="button"
           aria-expanded="{{ request()->is('payments*') ? 'true' : 'false' }}"
           aria-controls="payments-nav">
          <i class="link-icon" data-lucide="credit-card"></i>
          <span class="link-title">Payments</span>
          <i class="link-arrow" data-lucide="chevron-down"></i>
        </a>
        <div class="collapse {{ request()->is('payments*') ? 'show' : '' }}" id="payments-nav">
          <ul class="nav sub-menu">
            <li class="nav-item">
              <a href="{{ route('payments.index') }}" class="nav-link {{ request()->is('payments') || request()->is('payments/invoices*') ? 'active' : '' }}">Invoices</a>
            </li>
            <li class="nav-item">
              <a href="{{ route('payments.settings') }}" class="nav-link {{ request()->is('payments/settings') ? 'active' : '' }}">Bank account</a>
            </li>
          </ul>
        </div>
      </li>

      <li class="nav-item {{ active_class(['platform-settings', 'platform-settings/*']) }}">
        <a href="{{ route('platform-settings.edit') }}" class="nav-link">
          <i class="link-icon" data-lucide="settings"></i>
          <span class="link-title">Platform settings</span>
        </a>
      </li>

      <li class="nav-item {{ active_class(['charges', 'charges/*']) }}">
        <a href="{{ route('charges.index') }}" class="nav-link">
          <i class="link-icon" data-lucide="badge-dollar-sign"></i>
          <span class="link-title">Pick-Drop Charges</span>
        </a>
      </li>

      <li class="nav-item nav-category">Analytics</li>

      <li class="nav-item {{ active_class(['reports', 'reports/*']) }}">
        <a href="{{ route('reports.index') }}" class="nav-link">
          <i class="link-icon" data-lucide="bar-chart-2"></i>
          <span class="link-title">Reports</span>
        </a>
      </li>

      <li class="nav-item nav-category">Account</li>

      <li class="nav-item nav-item-logout">
        <a href="{{ route('auth.login') }}" class="nav-link">
          <i class="link-icon" data-lucide="log-out"></i>
          <span class="link-title">Logout</span>
        </a>
      </li>

    </ul>
  </div>
</nav>
