<nav class="sidebar">
  <div class="sidebar-header">
    <a href="{{ url('/') }}" class="sidebar-brand">
      Pick<span>Drop</span>
    </a>
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
        <a href="{{ url('/') }}" class="nav-link">
          <i class="link-icon" data-lucide="layout-dashboard"></i>
          <span class="link-title">Dashboard</span>
        </a>
      </li>

      <li class="nav-item nav-category">Management</li>

      <li class="nav-item {{ active_class(['users', 'users/*']) }}">
        <a href="{{ url('/users') }}" class="nav-link">
          <i class="link-icon" data-lucide="users"></i>
          <span class="link-title">Users</span>
        </a>
      </li>

      <li class="nav-item {{ active_class(['vehicles', 'vehicles/*']) }}">
        <a href="{{ url('/vehicles') }}" class="nav-link">
          <i class="link-icon" data-lucide="bus"></i>
          <span class="link-title">Vehicles</span>
        </a>
      </li>

      <li class="nav-item {{ active_class(['routes', 'routes/*']) }}">
        <a href="{{ url('/routes') }}" class="nav-link">
          <i class="link-icon" data-lucide="map-pin"></i>
          <span class="link-title">Routes</span>
        </a>
      </li>

      <li class="nav-item nav-category">Finance</li>

      <li class="nav-item {{ active_class(['payments', 'payments/*']) }}">
        <a href="{{ url('/payments') }}" class="nav-link">
          <i class="link-icon" data-lucide="credit-card"></i>
          <span class="link-title">Payments</span>
        </a>
      </li>

      <li class="nav-item nav-category">Analytics</li>

      <li class="nav-item {{ active_class(['reports', 'reports/*']) }}">
        <a href="{{ url('/reports') }}" class="nav-link">
          <i class="link-icon" data-lucide="bar-chart-2"></i>
          <span class="link-title">Reports</span>
        </a>
      </li>

      <li class="nav-item nav-category">Account</li>

      <li class="nav-item">
        <a href="{{ url('/auth/login') }}" class="nav-link">
          <i class="link-icon" data-lucide="log-out"></i>
          <span class="link-title">Logout</span>
        </a>
      </li>

    </ul>
  </div>
</nav>