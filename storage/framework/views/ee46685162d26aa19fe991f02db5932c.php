<?php $__env->startSection('content'); ?>

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Pickup Requests</h4>
    <p class="text-secondary mb-0">See who submitted a request, current status, and assigned driver</p>
  </div>
  <div class="d-flex gap-2">
    <?php if(($counts['cleared'] ?? 0) > 0): ?>
      <form action="<?php echo e(route('pickup-requests.recover')); ?>" method="POST" onsubmit="confirmRecoverRequests(event, this)">
        <?php echo csrf_field(); ?>
        <button type="submit" class="btn btn-outline-primary">
          <i data-lucide="rotate-ccw" class="icon-xs"></i>
          Recover (<?php echo e(number_format($counts['cleared'])); ?>)
        </button>
      </form>
    <?php endif; ?>
    <?php if(($counts['all'] ?? 0) > 0): ?>
      <form action="<?php echo e(route('pickup-requests.clear')); ?>" method="POST" onsubmit="confirmClearRequests(event, this)">
        <?php echo csrf_field(); ?>
        <button type="submit" class="btn btn-outline-danger">
          <i data-lucide="trash-2" class="icon-xs"></i>
          Clear all
        </button>
      </form>
    <?php endif; ?>
  </div>
</div>

<div class="row g-3 mb-3">
  <?php $all = max(1, (int) $counts['all']); ?>
  <div class="col-sm-6 col-xl-3">
    <a href="<?php echo e(route('pickup-requests.index')); ?>" class="dashboard-stat-link">
      <div class="card dashboard-stat-card h-100">
        <div class="card-body">
          <?php if (isset($component)) { $__componentOriginal044086a1bd9f343df960f063e041fa0c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal044086a1bd9f343df960f063e041fa0c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-ring','data' => ['percent' => $counts['all'] > 0 ? 100 : 0,'tone' => 'blue']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-ring'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['percent' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($counts['all'] > 0 ? 100 : 0),'tone' => 'blue']); ?>
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
            <h3><?php echo e(number_format($counts['all'])); ?></h3>
            <p class="dashboard-stat-label">All requests</p>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-ring','data' => ['percent' => ($counts['pending'] / $all) * 100,'tone' => 'orange']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-ring'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['percent' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($counts['pending'] / $all) * 100),'tone' => 'orange']); ?>
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
            <h3><?php echo e(number_format($counts['pending'])); ?></h3>
            <p class="dashboard-stat-label">Waiting for driver</p>
          </div>
        </div>
      </div>
    </a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a href="<?php echo e(route('pickup-requests.index', ['status' => 'in_progress'])); ?>" class="dashboard-stat-link">
      <div class="card dashboard-stat-card h-100">
        <div class="card-body">
          <?php if (isset($component)) { $__componentOriginal044086a1bd9f343df960f063e041fa0c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal044086a1bd9f343df960f063e041fa0c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-ring','data' => ['percent' => ($counts['accepted'] / $all) * 100,'tone' => 'teal']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-ring'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['percent' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($counts['accepted'] / $all) * 100),'tone' => 'teal']); ?>
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
            <h3><?php echo e(number_format($counts['accepted'])); ?></h3>
            <p class="dashboard-stat-label">In progress</p>
          </div>
        </div>
      </div>
    </a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a href="<?php echo e(route('pickup-requests.index', ['status' => 'completed'])); ?>" class="dashboard-stat-link">
      <div class="card dashboard-stat-card h-100">
        <div class="card-body">
          <?php if (isset($component)) { $__componentOriginal044086a1bd9f343df960f063e041fa0c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal044086a1bd9f343df960f063e041fa0c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-ring','data' => ['percent' => ($counts['completed'] / $all) * 100,'tone' => 'green']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-ring'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['percent' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($counts['completed'] / $all) * 100),'tone' => 'green']); ?>
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
            <h3><?php echo e(number_format($counts['completed'])); ?></h3>
            <p class="dashboard-stat-label">Completed</p>
          </div>
        </div>
      </div>
    </a>
  </div>
</div>

<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" action="<?php echo e(route('pickup-requests.index')); ?>">
      <div class="row g-2 align-items-center">
        <div class="col-12 col-md-3">
          <input type="text" name="search" class="form-control"
                 placeholder="Search name, email, pickup, drop..."
                 value="<?php echo e(request('search')); ?>">
        </div>
        <div class="col-12 col-md-2">
          <select class="form-select" name="type" onchange="this.form.submit()">
            <option value="">All types</option>
            <option value="parent" <?php echo e(request('type') === 'parent' ? 'selected' : ''); ?>>Parent</option>
            <option value="self" <?php echo e(request('type') === 'self' ? 'selected' : ''); ?>>Self</option>
          </select>
        </div>
        <div class="col-12 col-md-2">
          <select class="form-select" name="status" onchange="this.form.submit()">
            <option value="">All statuses</option>
            <option value="in_progress" <?php echo e(request('status') === 'in_progress' ? 'selected' : ''); ?>>In progress</option>
            <?php $__currentLoopData = \App\Models\PickupRequest::statusOptions(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($value); ?>" <?php echo e(request('status') === $value ? 'selected' : ''); ?>><?php echo e($label); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div class="col-12 col-md-2">
          <select class="form-select" name="city_id" onchange="this.form.submit()">
            <option value="">All cities</option>
            <?php $__currentLoopData = $cities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $city): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($city->id); ?>" <?php echo e((string) request('city_id') === (string) $city->id ? 'selected' : ''); ?>><?php echo e($city->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div class="col-12 col-md-2">
          <select class="form-select" name="trip_mode" onchange="this.form.submit()">
            <option value="">All trip modes</option>
            <?php $__currentLoopData = \App\Models\PickupRequest::tripModeOptions(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($value); ?>" <?php echo e(request('trip_mode') === $value ? 'selected' : ''); ?>><?php echo e($label); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div class="col-auto">
          <button type="submit" class="btn btn-outline-secondary">Filter</button>
          <?php if(request('search') || request('status') || request('type') || request('city_id') || request('trip_mode')): ?>
            <a href="<?php echo e(route('pickup-requests.index')); ?>" class="btn btn-outline-danger ms-1">Clear</a>
          <?php endif; ?>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-4 py-3">Request</th>
            <th class="py-3">Requested by</th>
            <th class="py-3">Type</th>
            <th class="py-3">Trip</th>
            <th class="py-3">Pickup → Drop</th>
            <th class="py-3">Driver</th>
            <th class="py-3">Submitted</th>
            <th class="py-3">Payment</th>
            <th class="py-3">Status</th>
            <th class="py-3 text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php $__empty_1 = true; $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
              <td class="ps-4">
                <div class="fw-semibold">#<?php echo e($item->id); ?></div>
                <small class="text-muted"><?php echo e($item->city?->name ?? '—'); ?></small>
              </td>
              <td>
                <div class="fw-semibold"><?php echo e($item->requesterName()); ?></div>
                <small class="text-muted"><?php echo e($item->parent?->email ?? '—'); ?></small>
                <?php if($item->student): ?>
                  <div><small class="text-muted">Child: <?php echo e($item->student->name); ?></small></div>
                <?php endif; ?>
              </td>
              <td>
                <?php if($item->type === 'self'): ?>
                  <span class="badge bg-info text-dark">Self</span>
                <?php else: ?>
                  <span class="badge bg-primary">Parent</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if($item->tripMode() === \App\Models\PickupRequest::TRIP_ROUND): ?>
                  <span class="badge rounded-pill px-3 py-1" style="background:#eef4ff;color:#3f6fd9;">Round trip</span>
                <?php else: ?>
                  <span class="badge bg-secondary">One way</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="fw-semibold"><?php echo e(\Illuminate\Support\Str::limit($item->pickup_point, 28)); ?></div>
                <small class="text-muted"><?php echo e(\Illuminate\Support\Str::limit($item->drop_point, 28)); ?></small>
              </td>
              <td>
                <?php if($item->driver): ?>
                  <div><?php echo e($item->driver->name); ?></div>
                  <small class="text-muted"><?php echo e($item->vehicle?->name ?? $item->vehicle?->license_plate ?? '—'); ?></small>
                <?php else: ?>
                  <span class="text-muted">Unassigned</span>
                <?php endif; ?>
              </td>
              <td><?php echo e($item->created_at?->format('d M Y, h:i A')); ?></td>
              <td>
                <span class="badge rounded-pill px-3 py-1" style="<?php echo e($item->paymentStatusBadgeStyle()); ?>">
                  <?php echo e($item->paymentStatusLabel()); ?>

                </span>
                <?php if($item->estimated_amount): ?>
                  <div><small class="text-muted"><?php echo e(number_format((float) $item->estimated_amount, 0)); ?></small></div>
                <?php endif; ?>
              </td>
              <td>
                <span class="badge rounded-pill px-3 py-1" style="<?php echo e($item->statusBadgeStyle()); ?>">
                  <?php echo e($item->statusLabel()); ?>

                </span>
              </td>
              <td class="text-center">
                <div class="action-btns">
                  <a href="<?php echo e(route('pickup-requests.show', $item)); ?>" class="action-btn action-btn-view" title="View">
                    <i data-lucide="eye"></i>
                  </a>
                  <form action="<?php echo e(route('pickup-requests.destroy', $item)); ?>" method="POST"
                        class="d-inline" onsubmit="confirmDelete(event, this)">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="action-btn action-btn-delete" title="Delete">
                      <i data-lucide="trash-2"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
              <td colspan="10" class="text-center py-5 text-muted">No pickup requests found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php if (isset($component)) { $__componentOriginal38def3cf8acaa014b33af3e7e7449e67 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38def3cf8acaa014b33af3e7e7449e67 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app-pagination','data' => ['paginator' => $requests,'label' => 'pickup requests']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($requests),'label' => 'pickup requests']); ?>
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

<?php $__env->stopSection(); ?>

<?php $__env->startPush('custom-scripts'); ?>
<script>
  function confirmClearRequests(event, form) {
    event.preventDefault();
    Swal.fire({
      title: 'Clear all data?',
      text: 'Are you sure you want to clear all pickup requests? You can recover them later.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Yes, clear all',
      cancelButtonText: 'Cancel',
      scrollbarPadding: false,
      customClass: { popup: 'rounded-4' }
    }).then(function (result) {
      if (result.isConfirmed) form.submit();
    });
  }

  function confirmRecoverRequests(event, form) {
    event.preventDefault();
    Swal.fire({
      title: 'Recover cleared data?',
      text: 'This will put the cleared pickup requests back on the list.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#0d6efd',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Yes, recover',
      cancelButtonText: 'Cancel',
      scrollbarPadding: false,
      customClass: { popup: 'rounded-4' }
    }).then(function (result) {
      if (result.isConfirmed) form.submit();
    });
  }
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layout.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Pick-Drop\resources\views/pickdrop/pickup-requests/index.blade.php ENDPATH**/ ?>