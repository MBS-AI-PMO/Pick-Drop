<?php $__env->startSection('content'); ?>

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Parent / Self Verifications (KYC)</h4>
    <p class="text-secondary mb-0">Review identity documents, then users add children or commute details</p>
  </div>
</div>

<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" action="<?php echo e(route('parent-self-verifications.index')); ?>">
      <div class="row g-2 align-items-center">
        <div class="col-12 col-md-4">
          <input type="text" name="search" class="form-control"
                 placeholder="Search name, email, phone, CNIC..."
                 value="<?php echo e(request('search')); ?>">
        </div>
        <div class="col-12 col-md-2">
          <select class="form-select" name="type" onchange="this.form.submit()">
            <option value="">All Types</option>
            <option value="parent" <?php echo e(request('type') === 'parent' ? 'selected' : ''); ?>>Parent</option>
            <option value="self" <?php echo e(request('type') === 'self' ? 'selected' : ''); ?>>Self</option>
          </select>
        </div>
        <div class="col-12 col-md-3">
          <select class="form-select" name="status" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <option value="pending" <?php echo e(request('status') === 'pending' ? 'selected' : ''); ?>>Pending</option>
            <option value="approved" <?php echo e(request('status') === 'approved' ? 'selected' : ''); ?>>Approved</option>
            <option value="rejected" <?php echo e(request('status') === 'rejected' ? 'selected' : ''); ?>>Declined</option>
          </select>
        </div>
        <div class="col-auto">
          <button type="submit" class="btn btn-outline-secondary">Filter</button>
          <?php if(request('search') || request('status') || request('type')): ?>
            <a href="<?php echo e(route('parent-self-verifications.index')); ?>" class="btn btn-outline-danger ms-1">Clear</a>
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
            <th class="ps-4 py-3">#</th>
            <th class="py-3">User</th>
            <th class="py-3">Type</th>
            <th class="py-3">Contact</th>
            <th class="py-3">CNIC</th>
            <th class="py-3">City</th>
            <th class="py-3">Submitted</th>
            <th class="py-3">Status</th>
            <th class="py-3 text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php $__empty_1 = true; $__currentLoopData = $verifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
              <td class="ps-4"><?php echo e($verifications->firstItem() + $loop->index); ?></td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <?php if($item->selfie_photo): ?>
                    <img src="<?php echo e(route('parent-self-verifications.document', [$item, 'selfie_photo'])); ?>"
                         alt="Profile"
                         class="rounded-circle border flex-shrink-0"
                         style="width:36px;height:36px;object-fit:cover;">
                  <?php endif; ?>
                  <div>
                    <div class="fw-semibold"><?php echo e($item->full_name); ?></div>
                    <small class="text-muted"><?php echo e($item->user?->email); ?></small>
                  </div>
                </div>
              </td>
              <td>
                <?php if($item->account_type === 'self'): ?>
                  <span class="badge bg-info text-dark">Self</span>
                <?php else: ?>
                  <span class="badge bg-primary">Parent</span>
                <?php endif; ?>
              </td>
              <td><?php echo e($item->contactPhone() ?? '—'); ?></td>
              <td><code><?php echo e($item->cnic_number); ?></code></td>
              <td><?php echo e($item->city?->name ?? '—'); ?></td>
              <td><?php echo e($item->created_at?->format('d M Y, h:i A')); ?></td>
              <td>
                <?php if($item->status === 'pending'): ?>
                  <span class="badge bg-warning text-dark">Pending</span>
                <?php elseif($item->status === 'approved'): ?>
                  <span class="badge rounded-pill px-3 py-1" style="background:#eef4ff;color:#3f6fd9;">Approved</span>
                <?php else: ?>
                  <span class="badge bg-danger">Declined</span>
                <?php endif; ?>
              </td>
              <td class="text-center">
                <div class="action-btns">
                  <a href="<?php echo e(route('parent-self-verifications.show', $item)); ?>" class="action-btn action-btn-view" title="View">
                    <i data-lucide="eye"></i>
                  </a>
                  <a href="<?php echo e(route('parent-self-verifications.edit', $item)); ?>" class="action-btn action-btn-edit" title="Edit">
                    <i data-lucide="edit-2"></i>
                  </a>
                  <form action="<?php echo e(route('parent-self-verifications.destroy', $item)); ?>" method="POST"
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
              <td colspan="9" class="text-center py-5 text-muted">No parent / self verifications found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php if (isset($component)) { $__componentOriginal38def3cf8acaa014b33af3e7e7449e67 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38def3cf8acaa014b33af3e7e7449e67 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app-pagination','data' => ['paginator' => $verifications,'label' => 'parent / self verifications']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($verifications),'label' => 'parent / self verifications']); ?>
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

<?php echo $__env->make('layout.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Pick-Drop\resources\views/pickdrop/parent-self-verifications/index.blade.php ENDPATH**/ ?>