<?php $__env->startSection('content'); ?>

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Login logs</h4>
    <p class="text-secondary mb-0">Record of every successful or failed login across admin and apps.</p>
  </div>
  <?php if(($logs->total() ?? 0) > 0): ?>
    <form action="<?php echo e(route('login-logs.clear')); ?>" method="POST">
      <?php echo csrf_field(); ?>
      <button type="submit" class="btn btn-outline-danger">
        <i data-lucide="trash-2" class="icon-xs"></i>
        Clear all
      </button>
    </form>
  <?php endif; ?>
</div>

<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" action="<?php echo e(route('login-logs.index')); ?>" class="row g-2 align-items-center">
      <div class="col-12 col-md-4">
        <input type="text" name="search" class="form-control" placeholder="Search name, email, role, IP..."
               value="<?php echo e(request('search')); ?>">
      </div>
      <div class="col-12 col-md-2">
        <select class="form-select" name="channel" onchange="this.form.submit()">
          <option value="">All channels</option>
          <option value="web" <?php echo e(request('channel') === 'web' ? 'selected' : ''); ?>>Admin panel</option>
          <option value="driver-api" <?php echo e(request('channel') === 'driver-api' ? 'selected' : ''); ?>>Driver app</option>
          <option value="parent-api" <?php echo e(request('channel') === 'parent-api' ? 'selected' : ''); ?>>Parent / Self app</option>
        </select>
      </div>
      <div class="col-12 col-md-2">
        <select class="form-select" name="status" onchange="this.form.submit()">
          <option value="">All statuses</option>
          <option value="success" <?php echo e(request('status') === 'success' ? 'selected' : ''); ?>>Success</option>
          <option value="failed" <?php echo e(request('status') === 'failed' ? 'selected' : ''); ?>>Failed</option>
          <option value="denied" <?php echo e(request('status') === 'denied' ? 'selected' : ''); ?>>Denied</option>
        </select>
      </div>
      <div class="col-auto">
        <button type="submit" class="btn btn-outline-secondary">Filter</button>
        <?php if(request()->hasAny(['search', 'channel', 'status'])): ?>
          <a href="<?php echo e(route('login-logs.index')); ?>" class="btn btn-outline-danger">Clear</a>
        <?php endif; ?>
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
            <th class="ps-4 py-3">Time</th>
            <th class="py-3">User</th>
            <th class="py-3">Role</th>
            <th class="py-3">Channel</th>
            <th class="py-3">IP</th>
            <th class="py-3">Status</th>
            <th class="py-3 text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
              <td class="ps-4"><?php echo e($log->created_at?->format('d M Y, h:i A')); ?></td>
              <td>
                <div class="fw-semibold"><?php echo e($log->name ?: '—'); ?></div>
                <div class="text-secondary fs-12px"><?php echo e($log->email ?: '—'); ?></div>
              </td>
              <td><?php echo e($log->role ?: '—'); ?></td>
              <td><?php echo e($log->channelLabel()); ?></td>
              <td><?php echo e($log->ip_address ?: '—'); ?></td>
              <td>
                <?php if($log->status === 'success'): ?>
                  <span class="badge bg-success"><?php echo e($log->statusLabel()); ?></span>
                <?php elseif($log->status === 'denied'): ?>
                  <span class="badge bg-warning text-dark"><?php echo e($log->statusLabel()); ?></span>
                <?php else: ?>
                  <span class="badge bg-danger"><?php echo e($log->statusLabel()); ?></span>
                <?php endif; ?>
              </td>
              <td class="text-center">
                <div class="action-btns">
                  <form action="<?php echo e(route('login-logs.destroy', $log)); ?>" method="POST"
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
              <td colspan="7" class="text-center py-5 text-muted">No login logs yet.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php if (isset($component)) { $__componentOriginal38def3cf8acaa014b33af3e7e7449e67 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38def3cf8acaa014b33af3e7e7449e67 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app-pagination','data' => ['paginator' => $logs,'label' => 'logs']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($logs),'label' => 'logs']); ?>
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

<?php echo $__env->make('layout.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Pick-Drop\resources\views/pickdrop/login-logs/index.blade.php ENDPATH**/ ?>