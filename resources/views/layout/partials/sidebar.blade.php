<nav class="sidebar">
  <div class="sidebar-header">
    <a href="{{ route('dashboard') }}" class=" sidebar-brand brand-logo mb-2">Pick<span style="font-weight: 800;">Drop</span></a>
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

      <li class="nav-item {{ request()->is('locations*') ? 'active' : '' }}">
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

      <li class="nav-item {{ request()->is('vehicles*') || request()->is('vehicle-categories*') ? 'active' : '' }}">
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

      <li class="nav-item {{ active_class(['routes', 'routes/*']) }}">
        <a href="{{ route('routes.index') }}" class="nav-link">
          <i class="link-icon" data-lucide="map-pin"></i>
          <span class="link-title">Routes</span>
        </a>
      </li>

      <li class="nav-item nav-category">Finance</li>

      <li class="nav-item {{ active_class(['payments', 'payments/*']) }}">
        <a href="#" class="nav-link">
          <i class="link-icon" data-lucide="credit-card"></i>
          <span class="link-title">Payments</span>
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
        <a href="#" class="nav-link">
          <i class="link-icon" data-lucide="bar-chart-2"></i>
          <span class="link-title">Reports</span>
        </a>
      </li>

      <li class="nav-item nav-category">Account</li>

      <li class="nav-item">
        <a href="{{ route('auth.login') }}" class="nav-link">
          <i class="link-icon" data-lucide="log-out"></i>
          <span class="link-title">Logout</span>
        </a>
      </li>

    </ul>
  </div>
</nav>