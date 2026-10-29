<?php $__env->startSection('content'); ?>

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Pickup / Drop points</h4>
    <p class="text-secondary mb-0">Admin master points apps can pick when booking (optional presets)</p>
  </div>
  <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPointModal">
    <i data-lucide="plus" class="icon-sm me-1"></i> Add point
  </button>
</div>

<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" action="<?php echo e(route('locations.points.index')); ?>" class="row g-2 align-items-center">
      <div class="col-md-3">
        <input type="text" name="search" class="form-control" placeholder="Search point..." value="<?php echo e(request('search')); ?>">
      </div>
      <div class="col-md-3">
        <select name="city_id" class="form-select" onchange="this.form.submit()">
          <option value="">All cities</option>
          <?php $__currentLoopData = $cities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $city): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($city->id); ?>" <?php if((string) request('city_id') === (string) $city->id): echo 'selected'; endif; ?>><?php echo e($city->name); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div class="col-md-2">
        <select name="type" class="form-select" onchange="this.form.submit()">
          <option value="">All types</option>
          <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($value); ?>" <?php if(request('type') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div class="col-auto">
        <button class="btn btn-outline-primary">Filter</button>
      </div>
      <?php if(request()->hasAny(['search','city_id','type'])): ?>
        <div class="col-auto">
          <a href="<?php echo e(route('locations.points.index')); ?>" class="btn btn-outline-secondary">Reset</a>
        </div>
      <?php endif; ?>
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
            <th class="py-3">Name</th>
            <th class="py-3">City / Area</th>
            <th class="py-3">Type</th>
            <th class="py-3">Coords</th>
            <th class="py-3">Status</th>
            <th class="py-3 text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php $__empty_1 = true; $__currentLoopData = $points; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $point): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
              <td class="ps-4 py-3 text-muted"><?php echo e($points->firstItem() + $loop->index); ?></td>
              <td class="py-3">
                <div class="fw-semibold"><?php echo e($point->name); ?></div>
                <?php if($point->address): ?>
                  <small class="text-secondary"><?php echo e($point->address); ?></small>
                <?php endif; ?>
              </td>
              <td class="py-3">
                <?php echo e($point->city?->name ?? '—'); ?>

                <div class="text-secondary small"><?php echo e($point->area?->name ?? 'Any area'); ?></div>
              </td>
              <td class="py-3"><?php echo e($point->typeLabel()); ?></td>
              <td class="py-3 text-secondary">
                <?php if($point->latitude && $point->longitude): ?>
                  <?php echo e($point->latitude); ?>, <?php echo e($point->longitude); ?>

                <?php else: ?>
                  —
                <?php endif; ?>
              </td>
              <td class="py-3">
                <span class="badge <?php echo e(strcasecmp($point->status, 'Active') === 0 ? 'bg-success' : 'bg-secondary'); ?>"><?php echo e($point->status); ?></span>
              </td>
              <td class="py-3 text-center">
                <button type="button" class="action-btn" title="Edit"
                        data-bs-toggle="modal" data-bs-target="#editPointModal<?php echo e($point->id); ?>">
                  <i data-lucide="pencil"></i>
                </button>
                <form method="POST" action="<?php echo e(route('locations.points.destroy', $point)); ?>" class="d-inline"
                      onsubmit="return confirm('Remove this point?')">
                  <?php echo csrf_field(); ?>
                  <?php echo method_field('DELETE'); ?>
                  <button class="action-btn action-btn-delete" type="submit" title="Delete">
                    <i data-lucide="trash-2"></i>
                  </button>
                </form>
              </td>
            </tr>

            <div class="modal fade" id="editPointModal<?php echo e($point->id); ?>" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <form method="POST" action="<?php echo e(route('locations.points.update', $point)); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="modal-header">
                      <h5 class="modal-title">Edit point</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                      <?php echo $__env->make('pickdrop.locations.partials.point-form', ['point' => $point, 'cities' => $cities, 'areas' => $areas, 'types' => $types], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                      <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
              <td colspan="7" class="text-center py-5 text-muted">No pickup/drop points yet.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php if (isset($component)) { $__componentOriginal38def3cf8acaa014b33af3e7e7449e67 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38def3cf8acaa014b33af3e7e7449e67 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app-pagination','data' => ['paginator' => $points,'label' => 'points']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($points),'label' => 'points']); ?>
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

<div class="modal fade" id="addPointModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="<?php echo e(route('locations.points.store')); ?>">
        <?php echo csrf_field(); ?>
        <div class="modal-header">
          <h5 class="modal-title">Add pickup/drop point</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <?php echo $__env->make('pickdrop.locations.partials.point-form', ['point' => null, 'cities' => $cities, 'areas' => $areas, 'types' => $types], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Add point</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Pick-Drop\resources\views/pickdrop/locations/points.blade.php ENDPATH**/ ?>