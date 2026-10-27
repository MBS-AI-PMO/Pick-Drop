<?php $__env->startSection('content'); ?>

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Complaints</h4>
    <p class="text-secondary mb-0">Parent, self, and driver reports including delays</p>
  </div>
  <?php if(($counts['all'] ?? 0) > 0): ?>
    <form action="<?php echo e(route('issues.clear')); ?>" method="POST">
      <?php echo csrf_field(); ?>
      <button type="submit" class="btn btn-outline-danger">
        <i data-lucide="trash-2" class="icon-xs"></i>
        Clear all
      </button>
    </form>
  <?php endif; ?>
</div>

<div class="row g-3 mb-3">
  <?php $all = max(1, (int) $counts['all']); ?>
  <div class="col-sm-6 col-xl-3">
    <a href="<?php echo e(route('issues.index')); ?>" class="dashboard-stat-link">
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
            <p class="dashboard-stat-label">All</p>
          </div>
        </div>
      </div>
    </a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a href="<?php echo e(route('issues.index', ['status' => 'open'])); ?>" class="dashboard-stat-link">
      <div class="card dashboard-stat-card h-100">
        <div class="card-body">
          <?php if (isset($component)) { $__componentOriginal044086a1bd9f343df960f063e041fa0c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal044086a1bd9f343df960f063e041fa0c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-ring','data' => ['percent' => ($counts['open'] / $all) * 100,'tone' => 'orange']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-ring'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['percent' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($counts['open'] / $all) * 100),'tone' => 'orange']); ?>
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
            <h3><?php echo e(number_format($counts['open'])); ?></h3>
            <p class="dashboard-stat-label">Open</p>
          </div>
        </div>
      </div>
    </a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a href="<?php echo e(route('issues.index', ['status' => 'in_progress'])); ?>" class="dashboard-stat-link">
      <div class="card dashboard-stat-card h-100">
        <div class="card-body">
          <?php if (isset($component)) { $__componentOriginal044086a1bd9f343df960f063e041fa0c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal044086a1bd9f343df960f063e041fa0c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-ring','data' => ['percent' => ($counts['in_progress'] / $all) * 100,'tone' => 'teal']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-ring'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['percent' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($counts['in_progress'] / $all) * 100),'tone' => 'teal']); ?>
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
            <h3><?php echo e(number_format($counts['in_progress'])); ?></h3>
            <p class="dashboard-stat-label">In progress</p>
          </div>
        </div>
      </div>
    </a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a href="<?php echo e(route('issues.index', ['status' => 'resolved'])); ?>" class="dashboard-stat-link">
      <div class="card dashboard-stat-card h-100">
        <div class="card-body">
          <?php if (isset($component)) { $__componentOriginal044086a1bd9f343df960f063e041fa0c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal044086a1bd9f343df960f063e041fa0c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-ring','data' => ['percent' => ($counts['resolved'] / $all) * 100,'tone' => 'green']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-ring'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['percent' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($counts['resolved'] / $all) * 100),'tone' => 'green']); ?>
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
            <h3><?php echo e(number_format($counts['resolved'])); ?></h3>
            <p class="dashboard-stat-label">Resolved</p>
          </div>
        </div>
      </div>
    </a>
  </div>
</div>

<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" action="<?php echo e(route('issues.index')); ?>">
      <div class="row g-2 align-items-center">
        <div class="col-12 col-md-4">
          <input type="text" name="search" class="form-control" placeholder="Search subject, user..."
                 value="<?php echo e(request('search')); ?>">
        </div>
        <div class="col-12 col-md-2">
          <select class="form-select" name="status" onchange="this.form.submit()">
            <option value="">All statuses</option>
            <?php $__currentLoopData = ['open' => 'Open', 'in_progress' => 'In progress', 'resolved' => 'Resolved', 'closed' => 'Closed']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($value); ?>" <?php echo e(request('status') === $value ? 'selected' : ''); ?>><?php echo e($label); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div class="col-12 col-md-2">
          <select class="form-select" name="type" onchange="this.form.submit()">
            <option value="">All types</option>
            <?php $__currentLoopData = ['general', 'delay', 'breakdown']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($type); ?>" <?php echo e(request('type') === $type ? 'selected' : ''); ?>><?php echo e(ucfirst($type)); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div class="col-auto">
          <button type="submit" class="btn btn-outline-secondary">Filter</button>
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
            <th class="ps-4 py-3">#</th>
            <th class="py-3">Reported by</th>
            <th class="py-3">Type</th>
            <th class="py-3">Subject</th>
            <th class="py-3">Request</th>
            <th class="py-3">Status</th>
            <th class="py-3 text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php $__empty_1 = true; $__currentLoopData = $issues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
              <td class="ps-4"><?php echo e($item->id); ?></td>
              <td><?php echo e($item->user?->name ?? '—'); ?></td>
              <td><?php echo e(ucfirst($item->type ?: 'general')); ?></td>
              <td><?php echo e($item->subject); ?></td>
              <td>
                <?php if($item->pickup_request_id): ?>
                  <a href="<?php echo e(route('pickup-requests.show', $item->pickup_request_id)); ?>">#<?php echo e($item->pickup_request_id); ?></a>
                <?php else: ?>
                  —
                <?php endif; ?>
              </td>
              <td><?php echo e($item->statusLabel()); ?></td>
              <td class="text-center">
                <div class="action-btns">
                  <a href="<?php echo e(route('issues.show', $item)); ?>" class="action-btn action-btn-view" title="View">
                    <i data-lucide="eye"></i>
                  </a>
                  <form action="<?php echo e(route('issues.destroy', $item)); ?>" method="POST"
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
            <tr><td colspan="7" class="text-center py-5 text-muted">No complaints found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php if (isset($component)) { $__componentOriginal38def3cf8acaa014b33af3e7e7449e67 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38def3cf8acaa014b33af3e7e7449e67 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app-pagination','data' => ['paginator' => $issues,'label' => 'complaints']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($issues),'label' => 'complaints']); ?>
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

<?php echo $__env->make('layout.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Pick-Drop\resources\views/pickdrop/issues/index.blade.php ENDPATH**/ ?>