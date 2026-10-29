<?php
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
  $paymentsOpen = request()->is('payments*')
    || request()->is('driver-payments*')
    || request()->is('driver-payroll*')
    || request()->is('payrolls*');
?>
<nav class="sidebar pd-sidebar">
  <div class="sidebar-header">
    <a href="<?php echo e(route('dashboard')); ?>" class="pd-brand" aria-label="PickDrop">
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

      
      <li class="pd-nav-section pd-nav-section--link">
        <a href="<?php echo e(route('dashboard')); ?>" class="pd-nav-section-toggle <?php echo e(active_class(['dashboard'])); ?>">
          <span class="pd-nav-ico"><i class="link-icon" data-lucide="layout-dashboard"></i></span>
          <span class="link-title">Dashboard</span>
        </a>
      </li>

      <li class="pd-nav-section pd-nav-section--link">
        <a href="<?php echo e(route('reports.index')); ?>" class="pd-nav-section-toggle <?php echo e(active_class(['reports', 'reports/*'])); ?>">
          <span class="pd-nav-ico"><i class="link-icon" data-lucide="bar-chart-2"></i></span>
          <span class="link-title">Reports</span>
        </a>
      </li>

      
      <li class="pd-nav-section <?php echo e($peopleOpen ? 'is-open' : ''); ?>">
        <a class="pd-nav-section-toggle <?php echo e($peopleOpen ? 'active' : ''); ?>"
           data-bs-toggle="collapse" href="#nav-people"
           role="button"
           aria-expanded="<?php echo e($peopleOpen ? 'true' : 'false'); ?>"
           aria-controls="nav-people">
          <span class="pd-nav-ico"><i class="link-icon" data-lucide="users"></i></span>
          <span class="link-title">People</span>
          <i class="link-arrow" data-lucide="chevron-down"></i>
        </a>
        <div class="collapse <?php echo e($peopleOpen ? 'show' : ''); ?>" id="nav-people">
          <ul class="pd-nav-section-list">
            <li class="nav-item <?php echo e(active_class(['users', 'users/*'])); ?>">
              <a href="<?php echo e(route('users.index')); ?>" class="nav-link"><span class="link-title">Users</span></a>
            </li>
            <li class="nav-item <?php echo e(active_class(['login-logs', 'login-logs/*'])); ?>">
              <a href="<?php echo e(route('login-logs.index')); ?>" class="nav-link"><span class="link-title">Login logs</span></a>
            </li>
            <li class="nav-item <?php echo e(active_class(['driver-verifications', 'driver-verifications/*'])); ?>">
              <a href="<?php echo e(route('driver-verifications.index')); ?>" class="nav-link"><span class="link-title">Driver KYC</span></a>
            </li>
            <li class="nav-item <?php echo e(active_class(['parent-self-verifications', 'parent-self-verifications/*'])); ?>">
              <a href="<?php echo e(route('parent-self-verifications.index')); ?>" class="nav-link"><span class="link-title">Parent / Self KYC</span></a>
            </li>
            <li class="nav-item <?php echo e(active_class(['vehicle-verifications', 'vehicle-verifications/*'])); ?>">
              <a href="<?php echo e(route('vehicle-verifications.index')); ?>" class="nav-link"><span class="link-title">Vehicle Verification</span></a>
            </li>
          </ul>
        </div>
      </li>

      <li class="pd-nav-section <?php echo e($locationsOpen ? 'is-open' : ''); ?>">
        <a class="pd-nav-section-toggle <?php echo e($locationsOpen ? 'active' : ''); ?>"
           data-bs-toggle="collapse" href="#nav-locations"
           role="button"
           aria-expanded="<?php echo e($locationsOpen ? 'true' : 'false'); ?>"
           aria-controls="nav-locations">
          <span class="pd-nav-ico"><i class="link-icon" data-lucide="map"></i></span>
          <span class="link-title">Locations</span>
          <i class="link-arrow" data-lucide="chevron-down"></i>
        </a>
        <div class="collapse <?php echo e($locationsOpen ? 'show' : ''); ?>" id="nav-locations">
          <ul class="pd-nav-section-list">
            <li class="nav-item">
              <a href="<?php echo e(route('locations.cities.index')); ?>" class="nav-link <?php echo e(request()->is('locations') || request()->is('locations/cities') ? 'active' : ''); ?>">
                <span class="link-title">Manage Cities</span>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?php echo e(route('locations.areas.index')); ?>" class="nav-link <?php echo e(request()->is('locations/areas') ? 'active' : ''); ?>">
                <span class="link-title">Manage Areas</span>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?php echo e(route('locations.points.index')); ?>" class="nav-link <?php echo e(request()->is('locations/points') ? 'active' : ''); ?>">
                <span class="link-title">Pickup / Drop points</span>
              </a>
            </li>
          </ul>
        </div>
      </li>

      <li class="pd-nav-section <?php echo e($vehiclesOpen ? 'is-open' : ''); ?>">
        <a class="pd-nav-section-toggle <?php echo e($vehiclesOpen ? 'active' : ''); ?>"
           data-bs-toggle="collapse" href="#nav-vehicles"
           role="button"
           aria-expanded="<?php echo e($vehiclesOpen ? 'true' : 'false'); ?>"
           aria-controls="nav-vehicles">
          <span class="pd-nav-ico"><i class="link-icon" data-lucide="bus"></i></span>
          <span class="link-title">Vehicles</span>
          <i class="link-arrow" data-lucide="chevron-down"></i>
        </a>
        <div class="collapse <?php echo e($vehiclesOpen ? 'show' : ''); ?>" id="nav-vehicles">
          <ul class="pd-nav-section-list">
            <li class="nav-item">
              <a href="<?php echo e(route('vehicles.index')); ?>" class="nav-link <?php echo e(request()->is('vehicles*') ? 'active' : ''); ?>">
                <span class="link-title">List</span>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?php echo e(route('vehicle-categories.index')); ?>" class="nav-link <?php echo e(request()->is('vehicle-categories*') ? 'active' : ''); ?>">
                <span class="link-title">Categories</span>
              </a>
            </li>
          </ul>
        </div>
      </li>

      
      <li class="pd-nav-section <?php echo e($operationsOpen ? 'is-open' : ''); ?>">
        <a class="pd-nav-section-toggle <?php echo e($operationsOpen ? 'active' : ''); ?>"
           data-bs-toggle="collapse" href="#nav-operations"
           role="button"
           aria-expanded="<?php echo e($operationsOpen ? 'true' : 'false'); ?>"
           aria-controls="nav-operations">
          <span class="pd-nav-ico"><i class="link-icon" data-lucide="briefcase"></i></span>
          <span class="link-title">Operations</span>
          <i class="link-arrow" data-lucide="chevron-down"></i>
        </a>
        <div class="collapse <?php echo e($operationsOpen ? 'show' : ''); ?>" id="nav-operations">
          <ul class="pd-nav-section-list">
            <li class="nav-item <?php echo e(active_class(['pickup-requests', 'pickup-requests/*'])); ?>">
              <a href="<?php echo e(route('pickup-requests.index')); ?>" class="nav-link"><span class="link-title">Pickup Requests</span></a>
            </li>
            <li class="nav-item <?php echo e(active_class(['fleet', 'fleet/*'])); ?>">
              <a href="<?php echo e(route('fleet.live.index')); ?>" class="nav-link"><span class="link-title">Live fleet</span></a>
            </li>
            <li class="nav-item <?php echo e(active_class(['routes', 'routes/*'])); ?>">
              <a href="<?php echo e(route('routes.index')); ?>" class="nav-link"><span class="link-title">Routes</span></a>
            </li>
            <li class="nav-item <?php echo e(active_class(['schools', 'schools/*'])); ?>">
              <a href="<?php echo e(route('schools.index')); ?>" class="nav-link"><span class="link-title">Institutions</span></a>
            </li>
          </ul>
        </div>
      </li>

      <li class="pd-nav-section pd-nav-section--link">
        <a href="<?php echo e(route('issues.index')); ?>" class="pd-nav-section-toggle <?php echo e(active_class(['issues', 'issues/*'])); ?>">
          <span class="pd-nav-ico"><i class="link-icon" data-lucide="flag"></i></span>
          <span class="link-title">Complaints</span>
        </a>
      </li>

      <li class="pd-nav-section pd-nav-section--link">
        <a href="<?php echo e(route('sos.index')); ?>" class="pd-nav-section-toggle <?php echo e(active_class(['sos', 'sos/*'])); ?>">
          <span class="pd-nav-ico"><i class="link-icon" data-lucide="alert-triangle"></i></span>
          <span class="link-title">SOS Alerts</span>
        </a>
      </li>

      <li class="pd-nav-section pd-nav-section--link">
        <a href="<?php echo e(route('holidays.index')); ?>" class="pd-nav-section-toggle <?php echo e(active_class(['holidays', 'holidays/*'])); ?>">
          <span class="pd-nav-ico"><i class="link-icon" data-lucide="calendar-days"></i></span>
          <span class="link-title">Calendar</span>
        </a>
      </li>

      
      <li class="pd-nav-section <?php echo e($paymentsOpen ? 'is-open' : ''); ?>">
        <a class="pd-nav-section-toggle <?php echo e($paymentsOpen ? 'active' : ''); ?>"
           data-bs-toggle="collapse" href="#nav-payments"
           role="button"
           aria-expanded="<?php echo e($paymentsOpen ? 'true' : 'false'); ?>"
           aria-controls="nav-payments">
          <span class="pd-nav-ico"><i class="link-icon" data-lucide="credit-card"></i></span>
          <span class="link-title">Payments</span>
          <i class="link-arrow" data-lucide="chevron-down"></i>
        </a>
        <div class="collapse <?php echo e($paymentsOpen ? 'show' : ''); ?>" id="nav-payments">
          <ul class="pd-nav-section-list">
            <li class="nav-item <?php echo e(active_class(['payments', 'payments/*'])); ?>">
              <a href="<?php echo e(route('payments.index')); ?>" class="nav-link"><span class="link-title">Invoices &amp; Payments</span></a>
            </li>
            <li class="nav-item <?php echo e(active_class(['driver-payments', 'driver-payments/*', 'driver-payroll', 'driver-payroll/*', 'payrolls', 'payrolls/*'])); ?>">
              <a href="<?php echo e(route('driver-payroll.index')); ?>" class="nav-link"><span class="link-title">Driver Payments</span></a>
            </li>
          </ul>
        </div>
      </li>

      <li class="pd-nav-section pd-nav-section--link">
        <a href="<?php echo e(route('charges.index')); ?>" class="pd-nav-section-toggle <?php echo e(active_class(['charges', 'charges/*'])); ?>">
          <span class="pd-nav-ico"><i class="link-icon" data-lucide="badge-dollar-sign"></i></span>
          <span class="link-title">Pick-Drop Charges</span>
        </a>
      </li>

    </ul>
  </div>

  <div class="sidebar-footer">
    <ul class="nav pd-footer-nav">
      <li class="nav-item <?php echo e(active_class(['platform-settings', 'platform-settings/*'])); ?>">
        <a href="<?php echo e(route('platform-settings.edit')); ?>" class="nav-link">
          <i class="link-icon" data-lucide="settings"></i>
          <span class="link-title">Settings</span>
        </a>
      </li>
      <li class="nav-item nav-item-logout">
        <a href="<?php echo e(route('login')); ?>" class="nav-link">
          <i class="link-icon" data-lucide="log-out"></i>
          <span class="link-title">Logout</span>
        </a>
      </li>
    </ul>
  </div>
</nav>
<?php /**PATH C:\xampp\htdocs\Pick-Drop\resources\views/layout/partials/sidebar.blade.php ENDPATH**/ ?>