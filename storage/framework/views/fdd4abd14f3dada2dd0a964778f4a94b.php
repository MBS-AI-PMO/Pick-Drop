<?php $__env->startSection('content'); ?>
<?php
  $periodQuery = array_filter([
    'period' => $period,
    'from' => $period === 'custom' ? $from->toDateString() : null,
    'to' => $period === 'custom' ? $to->toDateString() : null,
    'city_id' => $filters['city_id'] ?? null,
    'driver_id' => $filters['driver_id'] ?? null,
    'status' => $filters['status'] ?? null,
    'type' => $filters['type'] ?? null,
    'payment_status' => $filters['payment_status'] ?? null,
  ]);
  $maxTrend = max(1, collect($trend)->max('value') ?: 0);
  $statusColors = [
    'pending' => '#94a3b8',
    'accepted' => '#3b82f6',
    'picked_up' => '#0ea5e9',
    'dropped' => '#14b8a6',
    'completed' => '#3f6fd9',
    'cancelled' => '#e63946',
  ];
?>

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin gap-3">
  <div>
    <h4 class="mb-1">Operations Reports</h4>
    <p class="text-secondary mb-0">Pickup & drop performance · <?php echo e($periodLabel); ?></p>
  </div>
  <a href="<?php echo e(route('reports.export', $periodQuery)); ?>" class="btn btn-primary d-flex align-items-center gap-2">
    <i data-lucide="download" style="width:15px;height:15px;"></i> Export CSV
  </a>
</div>

<div class="pd-report-toolbar mb-4">
  <div class="pd-period-tabs" role="tablist">
    <?php $__currentLoopData = ['daily' => 'Today', 'weekly' => 'Last 7 days', 'monthly' => 'This month', 'custom' => 'Custom']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <a class="pd-period-tab <?php echo e($period === $key ? 'is-active' : ''); ?>"
         href="<?php echo e(route('reports.index', array_merge($periodQuery, $key === 'custom' ? ['period' => 'custom', 'from' => $from->toDateString(), 'to' => $to->toDateString()] : ['period' => $key]))); ?>">
        <?php echo e($label); ?>

      </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>

  <?php if($period === 'custom'): ?>
    <form method="GET" action="<?php echo e(route('reports.index')); ?>" class="pd-report-dates">
      <input type="hidden" name="period" value="custom">
      <?php $__currentLoopData = ['city_id', 'driver_id', 'status', 'type', 'payment_status']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $keep): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if(!empty($filters[$keep])): ?>
          <input type="hidden" name="<?php echo e($keep); ?>" value="<?php echo e($filters[$keep]); ?>">
        <?php endif; ?>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      <input type="date" name="from" class="pd-date-input" value="<?php echo e($from->toDateString()); ?>" max="<?php echo e(now()->toDateString()); ?>" aria-label="From date">
      <span class="pd-date-sep">to</span>
      <input type="date" name="to" class="pd-date-input" value="<?php echo e($to->toDateString()); ?>" max="<?php echo e(now()->toDateString()); ?>" aria-label="To date">
      <button type="submit" class="btn btn-primary pd-date-apply">Apply</button>
    </form>
  <?php endif; ?>
</div>

<form method="GET" action="<?php echo e(route('reports.index')); ?>" class="card mb-4">
  <div class="card-body py-3">
    <input type="hidden" name="period" value="<?php echo e($period); ?>">
    <?php if($period === 'custom'): ?>
      <input type="hidden" name="from" value="<?php echo e($from->toDateString()); ?>">
      <input type="hidden" name="to" value="<?php echo e($to->toDateString()); ?>">
    <?php endif; ?>
    <div class="row g-2 align-items-end">
      <div class="col-md-2">
        <label class="form-label mb-1">City</label>
        <select name="city_id" class="form-select form-select-sm">
          <option value="">All cities</option>
          <?php $__currentLoopData = $filterCities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $city): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($city->id); ?>" <?php if((int) ($filters['city_id'] ?? 0) === (int) $city->id): echo 'selected'; endif; ?>><?php echo e($city->name); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label mb-1">Driver</label>
        <select name="driver_id" class="form-select form-select-sm">
          <option value="">All drivers</option>
          <?php $__currentLoopData = $filterDrivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($driver->id); ?>" <?php if((int) ($filters['driver_id'] ?? 0) === (int) $driver->id): echo 'selected'; endif; ?>><?php echo e($driver->name); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label mb-1">Status</label>
        <select name="status" class="form-select form-select-sm">
          <option value="">All statuses</option>
          <?php $__currentLoopData = ['pending' => 'Pending', 'accepted' => 'Accepted', 'picked_up' => 'Picked up', 'dropped' => 'Dropped', 'completed' => 'Completed', 'cancelled' => 'Cancelled']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($value); ?>" <?php if(($filters['status'] ?? '') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label mb-1">Type</label>
        <select name="type" class="form-select form-select-sm">
          <option value="">Parent & Self</option>
          <option value="parent" <?php if(($filters['type'] ?? '') === 'parent'): echo 'selected'; endif; ?>>Parent</option>
          <option value="self" <?php if(($filters['type'] ?? '') === 'self'): echo 'selected'; endif; ?>>Self</option>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label mb-1">Payment</label>
        <select name="payment_status" class="form-select form-select-sm">
          <option value="">All payments</option>
          <option value="unpaid" <?php if(($filters['payment_status'] ?? '') === 'unpaid'): echo 'selected'; endif; ?>>Unpaid</option>
          <option value="pending_confirmation" <?php if(($filters['payment_status'] ?? '') === 'pending_confirmation'): echo 'selected'; endif; ?>>Awaiting confirmation</option>
          <option value="paid" <?php if(($filters['payment_status'] ?? '') === 'paid'): echo 'selected'; endif; ?>>Paid</option>
        </select>
      </div>
      <div class="col-md-2 d-flex gap-2">
        <button type="submit" class="btn btn-primary btn-sm flex-fill">Filter</button>
        <a href="<?php echo e(route('reports.index', ['period' => $period])); ?>" class="btn btn-outline-secondary btn-sm">Reset</a>
      </div>
    </div>
  </div>
</form>

<div class="row g-3 mb-4">
  <?php $tripDenom = max(1, (int) $kpis['total_trips']); ?>
  <div class="col-sm-6 col-xl-3">
    <div class="card dashboard-stat-card h-100">
      <div class="card-body">
        <?php if (isset($component)) { $__componentOriginal044086a1bd9f343df960f063e041fa0c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal044086a1bd9f343df960f063e041fa0c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-ring','data' => ['percent' => $kpis['total_trips'] > 0 ? 100 : 0,'tone' => 'blue']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-ring'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['percent' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['total_trips'] > 0 ? 100 : 0),'tone' => 'blue']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal044086a1bd9f343df960f063e041fa0c)): ?>
<?php $attributes = $__attributesOriginal044086a1bd9f343df960f063e041fa0c; ?>
<?php unset($__attributesOriginal044086a1bd9f343df960f063e041fa0c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal044086a1bd9f343df960f063e041fa0c)): ?>
<?php $component = $__componentOriginal044086a1bd9f343df960f063e041fa0c; ?>
<?php unset($__componentOriginal044086a1bd9f343df960f063e041fa0c); ?>
<?php endif; ?>
        <div>
          <h3><?php echo e(number_format($kpis['total_trips'])); ?></h3>
          <p class="dashboard-stat-label">Total trips</p>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card dashboard-stat-card h-100">
      <div class="card-body">
        <?php if (isset($component)) { $__componentOriginal044086a1bd9f343df960f063e041fa0c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal044086a1bd9f343df960f063e041fa0c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-ring','data' => ['percent' => $kpis['completion_rate'],'tone' => 'green']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-ring'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['percent' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['completion_rate']),'tone' => 'green']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal044086a1bd9f343df960f063e041fa0c)): ?>
<?php $attributes = $__attributesOriginal044086a1bd9f343df960f063e041fa0c; ?>
<?php unset($__attributesOriginal044086a1bd9f343df960f063e041fa0c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal044086a1bd9f343df960f063e041fa0c)): ?>
<?php $component = $__componentOriginal044086a1bd9f343df960f063e041fa0c; ?>
<?php unset($__componentOriginal044086a1bd9f343df960f063e041fa0c); ?>
<?php endif; ?>
        <div>
          <h3><?php echo e(number_format($kpis['completed'])); ?></h3>
          <p class="dashboard-stat-label">Completed</p>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card dashboard-stat-card h-100">
      <div class="card-body">
        <?php if (isset($component)) { $__componentOriginal044086a1bd9f343df960f063e041fa0c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal044086a1bd9f343df960f063e041fa0c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-ring','data' => ['percent' => ($kpis['active'] / $tripDenom) * 100,'tone' => 'teal']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-ring'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['percent' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($kpis['active'] / $tripDenom) * 100),'tone' => 'teal']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal044086a1bd9f343df960f063e041fa0c)): ?>
<?php $attributes = $__attributesOriginal044086a1bd9f343df960f063e041fa0c; ?>
<?php unset($__attributesOriginal044086a1bd9f343df960f063e041fa0c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal044086a1bd9f343df960f063e041fa0c)): ?>
<?php $component = $__componentOriginal044086a1bd9f343df960f063e041fa0c; ?>
<?php unset($__componentOriginal044086a1bd9f343df960f063e041fa0c); ?>
<?php endif; ?>
        <div>
          <h3><?php echo e(number_format($kpis['active'])); ?></h3>
          <p class="dashboard-stat-label">In progress</p>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card dashboard-stat-card h-100">
      <div class="card-body">
        <?php if (isset($component)) { $__componentOriginal044086a1bd9f343df960f063e041fa0c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal044086a1bd9f343df960f063e041fa0c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-ring','data' => ['percent' => $kpis['cancellation_rate'],'tone' => 'orange']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-ring'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['percent' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['cancellation_rate']),'tone' => 'orange']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal044086a1bd9f343df960f063e041fa0c)): ?>
<?php $attributes = $__attributesOriginal044086a1bd9f343df960f063e041fa0c; ?>
<?php unset($__attributesOriginal044086a1bd9f343df960f063e041fa0c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal044086a1bd9f343df960f063e041fa0c)): ?>
<?php $component = $__componentOriginal044086a1bd9f343df960f063e041fa0c; ?>
<?php unset($__componentOriginal044086a1bd9f343df960f063e041fa0c); ?>
<?php endif; ?>
        <div>
          <h3><?php echo e(number_format($kpis['cancelled'])); ?></h3>
          <p class="dashboard-stat-label">Cancelled</p>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-sm-6 col-xl-3">
    <div class="card dashboard-stat-card h-100">
      <div class="card-body">
        <div>
          <h3>PKR <?php echo e(number_format($kpis['revenue'] ?? 0, 0)); ?></h3>
          <p class="dashboard-stat-label">Customer payments</p>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card dashboard-stat-card h-100">
      <div class="card-body">
        <div>
          <h3><?php echo e(number_format($kpis['pending_invoices'] ?? 0)); ?></h3>
          <p class="dashboard-stat-label">Pending invoices</p>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card dashboard-stat-card h-100">
      <div class="card-body">
        <div>
          <h3>PKR <?php echo e(number_format($kpis['driver_payouts'] ?? 0, 0)); ?></h3>
          <p class="dashboard-stat-label">Driver payments</p>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card dashboard-stat-card h-100">
      <div class="card-body">
        <div>
          <h3><?php echo e(number_format($kpis['sos'] ?? 0)); ?> / <?php echo e(number_format($kpis['complaints'] ?? 0)); ?></h3>
          <p class="dashboard-stat-label">SOS / complaints</p>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-lg-7">
    <div class="card dashboard-card h-100">
      <div class="card-body">
        <div class="dashboard-card-header d-flex justify-content-between align-items-center mb-3">
          <div>
            <h6 class="card-title mb-0">Trip volume</h6>
            <p class="text-secondary fs-12px mb-0"><?php echo e($period === 'daily' ? 'Trips by time of day' : 'Trips by day'); ?></p>
          </div>
          <i data-lucide="bar-chart-2" class="text-secondary" style="width:18px;height:18px;"></i>
        </div>
        <div class="report-chart d-flex align-items-end justify-content-between gap-1" style="height:160px;">
          <?php $__empty_1 = true; $__currentLoopData = $trend; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $point): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php $height = max(6, (int) round(($point['value'] / $maxTrend) * 140)); ?>
            <div class="report-chart-col flex-fill d-flex flex-column align-items-center justify-content-end h-100">
              <span class="report-chart-value"><?php echo e($point['value'] > 0 ? $point['value'] : ''); ?></span>
              <div class="report-chart-bar <?php echo e($point['value'] > 0 ? 'is-filled' : ''); ?>" style="height:<?php echo e($height); ?>px;"></div>
              <small class="text-secondary report-chart-label"><?php echo e($point['label']); ?></small>
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="w-100 text-center text-secondary py-5">No trip activity in this period</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card dashboard-card h-100">
      <div class="card-body">
        <div class="dashboard-card-header d-flex justify-content-between align-items-center mb-3">
          <h6 class="card-title mb-0">Request status</h6>
        </div>
        <div class="d-flex flex-column gap-3">
          <?php $__currentLoopData = $statusBreakdown; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div>
              <div class="d-flex justify-content-between mb-1">
                <span class="fs-13px"><?php echo e($row['label']); ?></span>
                <span class="fs-13px fw-semibold"><?php echo e(number_format($row['count'])); ?> · <?php echo e($row['percent']); ?>%</span>
              </div>
              <div class="progress" style="height:7px;">
                <div class="progress-bar" style="width:<?php echo e($row['percent']); ?>%;background:<?php echo e($statusColors[$row['key']] ?? '#94a3b8'); ?>;"></div>
              </div>
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-lg-4">
    <div class="card dashboard-card h-100">
      <div class="card-body">
        <div class="dashboard-card-header mb-3">
          <h6 class="card-title mb-0">Fleet & compliance</h6>
        </div>
        <div class="d-flex flex-column gap-3">
          <div class="d-flex justify-content-between">
            <span class="text-secondary fs-13px">Active vehicles</span>
            <span class="fw-semibold"><?php echo e(number_format($snapshot['vehicles_active'])); ?> / <?php echo e(number_format($snapshot['vehicles_total'])); ?></span>
          </div>
          <div>
            <div class="d-flex justify-content-between mb-1">
              <span class="text-secondary fs-13px">Fleet assigned</span>
              <span class="fw-semibold"><?php echo e($snapshot['fleet_utilization']); ?>%</span>
            </div>
            <div class="progress" style="height:6px;">
              <div class="progress-bar bg-primary" style="width:<?php echo e($snapshot['fleet_utilization']); ?>%;"></div>
            </div>
          </div>
          <div class="d-flex justify-content-between">
            <span class="text-secondary fs-13px">Approved drivers</span>
            <span class="fw-semibold"><?php echo e(number_format($snapshot['kyc_approved'])); ?> / <?php echo e(number_format($snapshot['drivers'])); ?></span>
          </div>
          <div class="d-flex justify-content-between">
            <span class="text-secondary fs-13px">Pending driver KYC</span>
            <span class="fw-semibold text-warning"><?php echo e(number_format($snapshot['kyc_pending'])); ?></span>
          </div>
          <div class="d-flex justify-content-between">
            <span class="text-secondary fs-13px">Pending vehicle KYC</span>
            <span class="fw-semibold text-warning"><?php echo e(number_format($snapshot['vehicle_kyc_pending'])); ?></span>
          </div>
          <div class="d-flex justify-content-between">
            <span class="text-secondary fs-13px">Open issues</span>
            <span class="fw-semibold <?php echo e($snapshot['issues_open'] > 0 ? 'text-danger' : ''); ?>"><?php echo e(number_format($snapshot['issues_open'])); ?></span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card dashboard-card h-100">
      <div class="card-body">
        <div class="dashboard-card-header mb-3">
          <h6 class="card-title mb-0">Network coverage</h6>
        </div>
        <div class="row g-3">
          <div class="col-6">
            <p class="text-secondary fs-12px mb-1">Parents</p>
            <h5 class="fw-bold mb-0"><?php echo e(number_format($snapshot['parents'])); ?></h5>
          </div>
          <div class="col-6">
            <p class="text-secondary fs-12px mb-1">Students</p>
            <h5 class="fw-bold mb-0"><?php echo e(number_format($snapshot['students'])); ?></h5>
          </div>
          <div class="col-6">
            <p class="text-secondary fs-12px mb-1">Active routes</p>
            <h5 class="fw-bold mb-0"><?php echo e(number_format($snapshot['routes_active'])); ?></h5>
          </div>
          <div class="col-6">
            <p class="text-secondary fs-12px mb-1">Cities / areas</p>
            <h5 class="fw-bold mb-0"><?php echo e(number_format($snapshot['cities'])); ?> / <?php echo e(number_format($snapshot['areas'])); ?></h5>
          </div>
        </div>
        <hr class="my-3">
        <p class="text-secondary fs-12px mb-0">Resolved issues this system: <?php echo e(number_format($snapshot['issues_resolved'])); ?></p>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card dashboard-card h-100">
      <div class="card-body">
        <div class="dashboard-card-header mb-3">
          <h6 class="card-title mb-0">Busiest cities</h6>
        </div>
        <?php $__empty_1 = true; $__currentLoopData = $topCities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $city): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <?php $cityMax = max(1, $topCities[0]['trips'] ?? 1); ?>
          <div class="mb-3">
            <div class="d-flex justify-content-between mb-1">
              <span class="fs-13px fw-semibold"><?php echo e($city['name']); ?></span>
              <span class="fs-13px text-secondary"><?php echo e(number_format($city['trips'])); ?> trips</span>
            </div>
            <div class="progress" style="height:6px;">
              <div class="progress-bar" style="width:<?php echo e((int) round(($city['trips'] / $cityMax) * 100)); ?>%;background:var(--pd-primary);"></div>
            </div>
          </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
          <p class="text-secondary mb-0 py-3 text-center">No city demand in this period</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-lg-4">
    <div class="card dashboard-card h-100">
      <div class="card-body">
        <div class="dashboard-card-header mb-3">
          <h6 class="card-title mb-0">Top drivers</h6>
        </div>
        <?php $__empty_1 = true; $__currentLoopData = $topDrivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <div class="d-flex align-items-center justify-content-between <?php echo e($loop->last ? '' : 'mb-3'); ?>">
            <div class="d-flex align-items-center gap-2">
              <span class="report-rank"><?php echo e($index + 1); ?></span>
              <span class="fw-semibold"><?php echo e($driver['name']); ?></span>
            </div>
            <span class="text-secondary fs-13px"><?php echo e(number_format($driver['trips'])); ?> trips</span>
          </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
          <p class="text-secondary mb-0 py-3 text-center">No assigned trips in this period</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card dashboard-card h-100">
      <div class="card-body pb-0">
        <div class="dashboard-card-header d-flex justify-content-between align-items-center mb-3">
          <h6 class="card-title mb-0">Recent trips</h6>
          <span class="text-secondary fs-12px"><?php echo e($periodLabel); ?></span>
        </div>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="py-3">Trip</th>
                <th class="py-3">Student / parent</th>
                <th class="py-3">Driver</th>
                <th class="py-3">City</th>
                <th class="py-3">Status</th>
              </tr>
            </thead>
            <tbody>
              <?php $__empty_1 = true; $__currentLoopData = $trips; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                  $status = strtolower((string) $trip->status);
                  $badge = match ($status) {
                    'completed' => 'background:#eef4ff;color:#3f6fd9;',
                    'cancelled' => 'background:#fee2e2;color:#991b1b;',
                    'pending' => 'background:#f3f4f6;color:#374151;',
                    'picked_up', 'dropped' => 'background:#dbeafe;color:#1e40af;',
                    default => 'background:#e0f2fe;color:#075985;',
                  };
                ?>
                <tr>
                  <td class="py-3">
                    <p class="mb-0 fw-semibold">#<?php echo e($trip->id); ?> · <?php echo e(ucfirst($trip->type ?: 'pickup')); ?></p>
                    <small class="text-secondary"><?php echo e($trip->created_at?->format('d M, h:i A')); ?></small>
                  </td>
                  <td class="py-3">
                    <p class="mb-0"><?php echo e($trip->student?->name ?? '—'); ?></p>
                    <small class="text-secondary"><?php echo e($trip->parent?->name ?? '—'); ?></small>
                  </td>
                  <td class="py-3 text-secondary"><?php echo e($trip->driver?->name ?? 'Unassigned'); ?></td>
                  <td class="py-3 text-secondary"><?php echo e($trip->city?->name ?? '—'); ?></td>
                  <td class="py-3">
                    <span class="badge rounded-pill px-3 py-1" style="<?php echo e($badge); ?>"><?php echo e(str_replace('_', ' ', $trip->status)); ?></span>
                  </td>
                </tr>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                  <td colspan="5" class="text-center py-5 text-muted">No trips found for this period.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php if (isset($component)) { $__componentOriginal38def3cf8acaa014b33af3e7e7449e67 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38def3cf8acaa014b33af3e7e7449e67 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app-pagination','data' => ['paginator' => $trips,'label' => 'trips']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($trips),'label' => 'trips']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal38def3cf8acaa014b33af3e7e7449e67)): ?>
<?php $attributes = $__attributesOriginal38def3cf8acaa014b33af3e7e7449e67; ?>
<?php unset($__attributesOriginal38def3cf8acaa014b33af3e7e7449e67); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal38def3cf8acaa014b33af3e7e7449e67)): ?>
<?php $component = $__componentOriginal38def3cf8acaa014b33af3e7e7449e67; ?>
<?php unset($__componentOriginal38def3cf8acaa014b33af3e7e7449e67); ?>
<?php endif; ?>
    </div>
  </div>
</div>

<style>
  .pd-report-toolbar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 12px 16px;
    padding: 8px;
    background: var(--pd-theme-card, #fff);
    border: 1px solid rgba(226, 232, 244, 0.95);
    border-radius: 16px;
    box-shadow: 0 8px 24px rgba(80, 100, 160, 0.06);
  }
  .pd-period-tabs {
    display: inline-flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 4px;
    padding: 4px;
    background: #f3f5f9;
    border-radius: 12px;
  }
  .pd-period-tab {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 34px;
    padding: 0 14px;
    border-radius: 9px;
    color: #5b6b82;
    font-size: 13px;
    font-weight: 600;
    line-height: 1;
    text-decoration: none;
    white-space: nowrap;
    transition: background-color 0.16s ease, color 0.16s ease, box-shadow 0.16s ease;
  }
  .pd-period-tab:hover {
    color: #5b6cff;
    background: rgba(255, 255, 255, 0.7);
  }
  .pd-period-tab.is-active {
    color: #5b6cff;
    background: #fff;
    box-shadow: 0 1px 4px rgba(80, 100, 160, 0.14);
  }
  .pd-report-dates {
    display: flex;
    align-items: center;
    flex-wrap: nowrap;
    gap: 8px;
    margin-left: auto;
  }
  .pd-date-input {
    width: 148px !important;
    flex: 0 0 148px;
    height: 36px;
    padding: 0 10px;
    border: 1px solid #e4eaf4;
    border-radius: 10px;
    background: #fff;
    color: #1d3557;
    font-size: 13px;
    font-weight: 600;
  }
  .pd-date-input:focus {
    outline: 0;
    border-color: #5b6cff;
    box-shadow: 0 0 0 3px rgba(91, 108, 255, 0.14);
  }
  .pd-date-sep {
    color: #8a96a8;
    font-size: 12px;
    font-weight: 600;
    flex: 0 0 auto;
  }
  .pd-date-apply {
    height: 36px;
    padding: 0 16px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    white-space: nowrap;
  }
  .report-chart-bar {
    width: 100%;
    max-width: 28px;
    border-radius: 6px 6px 2px 2px;
    background: rgba(29, 53, 87, 0.18);
  }
  .report-chart-bar.is-filled {
    background: var(--pd-primary);
  }
  .report-chart-value {
    font-size: 10px;
    font-weight: 700;
    color: #64748b;
    min-height: 14px;
  }
  .report-chart-label {
    font-size: 10px;
    margin-top: 6px;
    white-space: nowrap;
  }
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
  }
  [data-bs-theme="dark"] .pd-report-toolbar {
    background: var(--pd-theme-card, #1e2129);
    border-color: var(--pd-theme-border, rgba(255, 255, 255, 0.08));
    box-shadow: none;
  }
  [data-bs-theme="dark"] .pd-period-tabs {
    background: rgba(255, 255, 255, 0.06);
  }
  [data-bs-theme="dark"] .pd-period-tab {
    color: #c8d0dc;
  }
  [data-bs-theme="dark"] .pd-period-tab:hover {
    color: #fff;
    background: rgba(255, 255, 255, 0.08);
  }
  [data-bs-theme="dark"] .pd-period-tab.is-active {
    color: #fff;
    background: rgba(91, 108, 255, 0.28);
    box-shadow: none;
  }
  [data-bs-theme="dark"] .pd-date-input {
    background: rgba(255, 255, 255, 0.04);
    border-color: rgba(255, 255, 255, 0.12);
    color: #f4f7fb;
  }
  [data-bs-theme="dark"] .pd-date-sep {
    color: #8b97ab;
  }
  [data-bs-theme="dark"] .report-chart-bar {
    background: rgba(230, 57, 70, 0.18);
  }
  [data-bs-theme="dark"] .report-chart-bar.is-filled {
    background: var(--pd-primary);
  }
  [data-bs-theme="dark"] .report-rank {
    background: #2a3140;
    color: #f1f5f9;
  }
  @media (max-width: 767px) {
    .pd-report-dates {
      width: 100%;
      margin-left: 0;
      flex-wrap: wrap;
    }
    .pd-date-input {
      width: calc(50% - 20px) !important;
      flex: 1 1 calc(50% - 20px);
    }
  }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Pick-Drop\resources\views/pickdrop/reports/index.blade.php ENDPATH**/ ?>