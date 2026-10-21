<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Institutions</h4>
    <p class="text-secondary mb-0">Admin-managed list of schools, colleges, universities, offices and other pickup destinations</p>
  </div>
</div>
<div class="row g-3">
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">Add institution</h6>
        <p class="text-secondary small mb-0 mt-1">Admin only. Parents and drivers can select from this list, not add to it.</p>
      </div>
      <div class="card-body">
        <form method="POST" action="<?php echo e(route('schools.store')); ?>">
          <?php echo csrf_field(); ?>
          <label class="form-label">Name</label>
          <input class="form-control mb-3" name="name" value="<?php echo e(old('name')); ?>" required>
          <label class="form-label">Category</label>
          <select class="form-select mb-3" name="category" required>
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($value); ?>" <?php echo e(old('category', 'school') === $value ? 'selected' : ''); ?>><?php echo e($label); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
          <label class="form-label">City</label>
          <select class="form-select mb-3" name="city_id">
            <option value="">—</option>
            <?php $__currentLoopData = $cities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $city): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($city->id); ?>" <?php echo e((string) old('city_id') === (string) $city->id ? 'selected' : ''); ?>><?php echo e($city->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
          <label class="form-label">Address</label>
          <input class="form-control mb-3" name="address" value="<?php echo e(old('address')); ?>">
          <label class="form-label">Phone</label>
          <input class="form-control mb-3" name="phone" value="<?php echo e(old('phone')); ?>">
          <label class="form-label">Email</label>
          <input class="form-control mb-3" name="email" value="<?php echo e(old('email')); ?>">
          <input type="hidden" name="status" value="Active">
          <button class="btn btn-dark w-100" type="submit">Save</button>
        </form>
      </div>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="card">
      <div class="card-body border-bottom py-3">
        <form method="GET" action="<?php echo e(route('schools.index')); ?>" class="row g-2 align-items-end">
          <div class="col-md-5">
            <label class="form-label mb-1">Search</label>
            <input class="form-control" type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search by name">
          </div>
          <div class="col-md-4">
            <label class="form-label mb-1">Category</label>
            <select class="form-select" name="category" onchange="this.form.submit()">
              <option value="">All categories</option>
              <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($value); ?>" <?php echo e(request('category') === $value ? 'selected' : ''); ?>><?php echo e($label); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
          </div>
          <div class="col-md-3">
            <button class="btn btn-outline-secondary w-100" type="submit">Filter</button>
          </div>
        </form>
      </div>
      <div class="card-body p-0">
        <table class="table mb-0">
          <thead class="table-light">
            <tr>
              <th class="ps-4">Name</th>
              <th>Category</th>
              <th>City</th>
              <th>Students</th>
              <th>Status</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $schools; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $school): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
              <tr>
                <td class="ps-4"><?php echo e($school->name); ?></td>
                <td><?php echo e($school->categoryLabel()); ?></td>
                <td><?php echo e($school->city?->name ?: '—'); ?></td>
                <td><?php echo e($school->students_count); ?></td>
                <td><?php echo e($school->status); ?></td>
                <td class="text-center">
                  <div class="action-btns">
                    <a href="<?php echo e(route('schools.show', $school)); ?>" class="action-btn action-btn-view" title="View">
                      <i data-lucide="eye"></i>
                    </a>
                    <form action="<?php echo e(route('schools.destroy', $school)); ?>" method="POST"
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
              <tr><td colspan="6" class="text-center py-4 text-muted">No institutions yet.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      <?php if (isset($component)) { $__componentOriginal38def3cf8acaa014b33af3e7e7449e67 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38def3cf8acaa014b33af3e7e7449e67 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app-pagination','data' => ['paginator' => $schools,'label' => 'institutions']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($schools),'label' => 'institutions']); ?>
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Pick-Drop\resources\views/pickdrop/schools/index.blade.php ENDPATH**/ ?>