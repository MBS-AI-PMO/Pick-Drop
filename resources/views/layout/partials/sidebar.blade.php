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

      <li class="nav-item {{ active_class(['/']) }}">
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

      <li class="nav-item {{ active_class(['vehicles', 'vehicles/*']) }}">
        <a href="{{ route('vehicles.index') }}" class="nav-link">
          <i class="link-icon" data-lucide="bus"></i>
          <span class="link-title">Vehicles</span>
        </a>
      </li>

      <li class="nav-item {{ active_class(['routes', 'routes/*']) }}">
        <a href="{{ route('routes.index') }}" class="nav-link">
          <i class="link-icon" data-lucide="map-pin"></i>
          <span class="link-title">Routes</span>
        </a>
      </li>

      <li class="nav-item nav-category">Finance</li>

      <li class="nav-item {{ active_class(['payments', 'payments/*']) }}">
        <a href="{{ route('payments.index') }}" class="nav-link">
          <i class="link-icon" data-lucide="credit-card"></i>
          <span class="link-title">Payments</span>
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

      <li class="nav-item">
        <a href="{{ route('auth.login') }}" class="nav-link">
          <i class="link-icon" data-lucide="log-out"></i>
          <span class="link-title">Logout</span>
        </a>
      </li>

    </ul>
  </div>
</nav>