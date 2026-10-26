<?php $__env->startSection('content'); ?>

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">SOS alerts</h4>
    <p class="text-secondary mb-0">Emergency reports from parents, self users, and drivers</p>
  </div>
  <?php if(($counts['all'] ?? 0) > 0): ?>
    <form action="<?php echo e(route('sos.clear')); ?>" method="POST">
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
    <a href="<?php echo e(route('sos.index', ['status' => 'open'])); ?>" class="dashboard-stat-link">
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
    <a href="<?php echo e(route('sos.index')); ?>" class="dashboard-stat-link">
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
</div>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-4 py-3">Time</th>
            <th class="py-3">From</th>
            <th class="py-3">Request</th>
            <th class="py-3">Message</th>
            <th class="py-3">Status</th>
            <th class="py-3 text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php $__empty_1 = true; $__currentLoopData = $alerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
              <td class="ps-4"><?php echo e($item->created_at?->format('d M Y, h:i A')); ?></td>
              <td><?php echo e($item->user?->name ?? '—'); ?></td>
              <td>
                <?php if($item->pickup_request_id): ?>
                  <a href="<?php echo e(route('pickup-requests.show', $item->pickup_request_id)); ?>">#<?php echo e($item->pickup_request_id); ?></a>
                <?php else: ?>
                  —
                <?php endif; ?>
              </td>
              <td><?php echo e($item->message ?: '—'); ?></td>
              <td><?php echo e(ucfirst($item->status)); ?></td>
              <td class="text-center">
                <div class="action-btns">
                  <a href="<?php echo e(route('sos.show', $item)); ?>" class="action-btn action-btn-view" title="View">
                    <i data-lucide="eye"></i>
                  </a>
                  <form action="<?php echo e(route('sos.destroy', $item)); ?>" method="POST"
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
            <tr><td colspan="6" class="text-center py-5 text-muted">No SOS alerts.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php if (isset($component)) { $__componentOriginal38def3cf8acaa014b33af3e7e7449e67 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38def3cf8acaa014b33af3e7e7449e67 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app-pagination','data' => ['paginator' => $alerts,'label' => 'alerts']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($alerts),'label' => 'alerts']); ?>
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

<?php echo $__env->make('layout.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\Pick-Drop\resources\views/pickdrop/sos/index.blade.php ENDPATH**/ ?>