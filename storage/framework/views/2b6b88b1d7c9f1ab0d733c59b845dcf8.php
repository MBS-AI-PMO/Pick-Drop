<?php $__env->startSection('content'); ?>
<?php
  $tripBadge = [
    'Accepted' => 'background:#dbeafe;color:#1e40af;',
    'Picked Up' => 'background:#e0f2fe;color:#075985;',
    'Dropped' => 'background:#eef4ff;color:#3f6fd9;',
  ];
  $rings = $stats['rings'] ?? ['vehicles' => 0, 'users' => 0, 'pending' => 0, 'alerts' => 0];
?>

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin gap-3 dashboard-header">
  <div>
    <h4 class="mb-1">Dashboard</h4>
    <p class="text-secondary mb-0">Welcome back, <?php echo e(auth()->user()?->name ?? 'Admin'); ?> · live operations overview</p>
  </div>
  <a href="<?php echo e(route('reports.index')); ?>" class="btn btn-primary d-flex align-items-center gap-2 pd-header-action">
    <i data-lucide="bar-chart-2" style="width:15px;height:15px;"></i> View reports
  </a>
</div>

<div class="dashboard-stats row g-3 mb-4">
  <div class="col-sm-6 col-xl-3">
    <a href="<?php echo e(route('vehicles.index')); ?>" class="dashboard-stat-link">
      <div class="card dashboard-stat-card h-100">
        <div class="card-body">
          <?php if (isset($component)) { $__componentOriginal044086a1bd9f343df960f063e041fa0c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal044086a1bd9f343df960f063e041fa0c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-ring','data' => ['percent' => $rings['vehicles'],'tone' => 'blue']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-ring'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['percent' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($rings['vehicles']),'tone' => 'blue']); ?>
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
            <h3><?php echo e(number_format($stats['vehicles'] ?? 0)); ?></h3>
            <p class="dashboard-stat-label">Active vehicles</p>
          </div>
        </div>
      </div>
    </a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a href="<?php echo e(route('users.index')); ?>" class="dashboard-stat-link">
      <div class="card dashboard-stat-card h-100">
        <div class="card-body">
          <?php if (isset($component)) { $__componentOriginal044086a1bd9f343df960f063e041fa0c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal044086a1bd9f343df960f063e041fa0c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-ring','data' => ['percent' => $rings['users'],'tone' => 'green']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-ring'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['percent' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($rings['users']),'tone' => 'green']); ?>
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
            <h3><?php echo e(number_format($stats['users'] ?? 0)); ?></h3>
            <p class="dashboard-stat-label">Total users</p>
          </div>
        </div>
      </div>
    </a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a href="<?php echo e(route('pickup-requests.index', ['status' => 'pending'])); ?>" class="dashboard-stat-link">
      <div class="card dashboard-stat-card h-100">
        <div class="card-body">
          <?php if (isset($component)) { $__componentOriginal044086a1bd9f343df960f063e041fa0c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal044086a1bd9f343df960f063e041fa0c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-ring','data' => ['percent' => $rings['pending'],'tone' => 'teal']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-ring'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['percent' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($rings['pending']),'tone' => 'teal']); ?>
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
            <h3><?php echo e(number_format($stats['pending_requests'] ?? 0)); ?></h3>
            <p class="dashboard-stat-label">Pending requests</p>
          </div>
        </div>
      </div>
    </a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a href="<?php echo e(route('notifications.index')); ?>" class="dashboard-stat-link">
      <div class="card dashboard-stat-card h-100">
        <div class="card-body">
          <?php if (isset($component)) { $__componentOriginal044086a1bd9f343df960f063e041fa0c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal044086a1bd9f343df960f063e041fa0c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-ring','data' => ['percent' => $rings['alerts'],'tone' => 'orange']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-ring'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['percent' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($rings['alerts']),'tone' => 'orange']); ?>
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
            <h3><?php echo e(number_format($stats['alerts_today'] ?? 0)); ?></h3>
            <p class="dashboard-stat-label">Alerts today</p>
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
            <span class="badge rounded-pill px-3 py-1" style="background:#dbeafe;color:#1e40af;"><?php echo e(number_format($activeTripsCount ?? 0)); ?> Active</span>
            <a href="<?php echo e(route('pickup-requests.index')); ?>" class="text-primary fs-13px">View all</a>
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
              <?php $__empty_1 = true; $__currentLoopData = $activeTrips; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr role="button" onclick="window.location='<?php echo e($trip['url']); ?>'">
                  <td class="py-3">
                    <p class="mb-0 fw-semibold"><?php echo e($trip['route_title']); ?></p>
                    <small class="text-secondary"><?php echo e($trip['route_subtitle']); ?></small>
                  </td>
                  <td class="py-3">
                    <p class="mb-0"><?php echo e($trip['driver_name']); ?></p>
                    <small class="text-secondary"><?php echo e($trip['driver_id']); ?></small>
                  </td>
                  <td class="py-3">
                    <p class="mb-0"><?php echo e($trip['vehicle_name']); ?></p>
                    <small class="text-secondary"><?php echo e($trip['vehicle_meta']); ?></small>
                  </td>
                  <td class="py-3">
                    <span class="badge rounded-pill px-3 py-1" style="<?php echo e($tripBadge[$trip['status_label']] ?? 'background:#e0f2fe;color:#075985;'); ?>">
                      <?php echo e($trip['status_label']); ?>

                    </span>
                  </td>
                  <td class="py-3">
                    <div class="d-flex justify-content-between mb-1">
                      <span class="fs-12px text-secondary"><?php echo e($trip['progress']); ?>%</span>
                    </div>
                    <div class="progress" style="height:6px;">
                      <div class="progress-bar <?php echo e($trip['progress_class']); ?>" style="width:<?php echo e($trip['progress']); ?>%"></div>
                    </div>
                  </td>
                </tr>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                  <td colspan="5" class="text-center text-secondary py-5">No active trips found</td>
                </tr>
              <?php endif; ?>
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
          <span class="badge rounded-pill px-3 py-1" style="background:#fef9c3;color:#92400e;"><?php echo e(number_format($pendingRequestsCount ?? 0)); ?> Pending</span>
        </div>
        <div class="d-flex flex-column gap-3">
          <?php $__empty_1 = true; $__currentLoopData = $pendingRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pending): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <a href="<?php echo e(route('pickup-requests.show', $pending)); ?>" class="text-decoration-none text-reset">
              <div class="dashboard-schedule-item d-flex justify-content-between align-items-center">
                <div>
                  <p class="mb-0 fw-semibold"><?php echo e($pending->requesterName()); ?></p>
                  <p class="text-secondary fs-12px mb-0">
                    <?php echo e($pending->typeLabel()); ?> · #<?php echo e($pending->id); ?>

                    <?php if($pending->area?->name): ?> · <?php echo e($pending->area->name); ?><?php endif; ?>
                  </p>
                </div>
                <span class="badge rounded-pill px-3 py-1" style="<?php echo e($pending->statusBadgeStyle()); ?>"><?php echo e($pending->statusLabel()); ?></span>
              </div>
            </a>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="text-center text-secondary py-3">No pending requests</div>
          <?php endif; ?>
        </div>
        <div class="mt-3 d-flex justify-content-end">
          <a href="<?php echo e(route('pickup-requests.index', ['status' => 'pending'])); ?>" class="text-primary fs-13px">View pending <i data-lucide="arrow-right" class="icon-xs"></i></a>
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
          <span class="badge rounded-pill px-3 py-1" style="background:#fee2e2;color:#991b1b;"><?php echo e(number_format($recentAlertsCount ?? 0)); ?> New</span>
        </div>
        <div class="d-flex flex-column gap-3">
          <?php $__empty_1 = true; $__currentLoopData = $recentAlerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="dashboard-alert-item d-flex align-items-start gap-3 p-2 rounded-2" style="background:rgba(var(--bs-<?php echo e($alert['color']); ?>-rgb),0.07);">
              <div class="w-35px h-35px rounded-circle bg-<?php echo e($alert['color']); ?> d-flex align-items-center justify-content-center flex-shrink-0">
                <i data-lucide="<?php echo e($alert['icon']); ?>" class="icon-sm text-white"></i>
              </div>
              <div>
                <p class="mb-0 fw-semibold"><?php echo e($alert['title']); ?></p>
                <p class="text-secondary fs-12px mb-0"><?php echo e(\Illuminate\Support\Str::limit($alert['message'], 58)); ?></p>
                <p class="text-secondary fs-11px mb-0"><?php echo e($alert['time']); ?></p>
              </div>
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="text-center text-secondary py-3">No recent alerts found</div>
          <?php endif; ?>
        </div>
        <div class="mt-3 d-flex justify-content-end">
          <a href="<?php echo e(route('notifications.index')); ?>" class="text-primary fs-13px">View all alerts <i data-lucide="arrow-right" class="icon-xs"></i></a>
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
          <?php $__empty_1 = true; $__currentLoopData = $todaySchedule; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $schedule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="dashboard-schedule-item d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center gap-2">
                <span class="report-rank"><?php echo e($index + 1); ?></span>
                <div>
                  <p class="mb-0 fw-semibold"><?php echo e($schedule['title']); ?></p>
                  <p class="text-secondary fs-12px mb-0"><?php echo e($schedule['time']); ?></p>
                </div>
              </div>
              <span class="badge rounded-pill px-3 py-1" style="<?php echo e($schedule['badge_style']); ?>"><?php echo e($schedule['status']); ?></span>
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="text-center text-secondary py-3">No routes scheduled today</div>
          <?php endif; ?>
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
          <h4 class="mb-0 fw-bold"><?php echo e($metrics['on_time_performance'] ?? 0); ?>%</h4>
          <span class="fs-12px" style="color:#3f6fd9;">Live</span>
        </div>
        <div class="progress" style="height:7px;">
          <div class="progress-bar" style="width:<?php echo e($metrics['on_time_performance'] ?? 0); ?>%; background:#3f6fd9;"></div>
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
          <h4 class="mb-0 fw-bold"><?php echo e($metrics['fleet_utilization'] ?? 0); ?>%</h4>
          <span class="fs-12px" style="color:#3f6fd9;">Live</span>
        </div>
        <div class="progress" style="height:7px;">
          <div class="progress-bar bg-primary" style="width:<?php echo e($metrics['fleet_utilization'] ?? 0); ?>%"></div>
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
          <h4 class="mb-0 fw-bold"><?php echo e(number_format($metrics['parent_satisfaction'] ?? 0, 1)); ?>/5</h4>
          <span class="fs-12px" style="color:#3f6fd9;">Live</span>
        </div>
        <div class="d-flex gap-1 mt-1">
          <?php for($i = 1; $i <= 5; $i++): ?>
            <?php if($i <= ($metrics['parent_satisfaction_stars'] ?? 0)): ?>
              <i data-lucide="star" class="icon-sm text-warning" style="fill:currentColor;"></i>
            <?php else: ?>
              <i data-lucide="star" class="icon-sm text-warning" style="fill:currentColor;opacity:0.4;"></i>
            <?php endif; ?>
          <?php endfor; ?>
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Pick-Drop\resources\views/dashboard.blade.php ENDPATH**/ ?>