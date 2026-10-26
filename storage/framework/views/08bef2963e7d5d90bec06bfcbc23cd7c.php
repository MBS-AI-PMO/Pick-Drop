<?php $__env->startSection('content'); ?>


<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Vehicle Categories</h4>
    <p class="text-secondary mb-0">Manage vehicle categories and capacities</p>
  </div>
</div>


<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" action="<?php echo e(route('vehicle-categories.index')); ?>" id="filterForm">
      <div class="row g-2 align-items-center">

        
        <div class="col-12 col-md-4">
          <div class="input-group">
            <div class="input-group-text bg-transparent border-end-0">
              <i data-lucide="search" style="width:16px;height:16px;"></i>
            </div>
            <input type="text" name="search" class="form-control border-start-0 ps-0"
                   id="categorySearch" placeholder="Search categories..."
                   value="<?php echo e(request('search')); ?>">
          </div>
        </div>

        
        <div class="col-12 col-md-3">
          <select class="form-select" name="status" id="statusFilter" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <option value="1" <?php echo e(request('status') === '1' ? 'selected' : ''); ?>>Active</option>
            <option value="0" <?php echo e(request('status') === '0' ? 'selected' : ''); ?>>Inactive</option>
          </select>
        </div>

        
        <div class="col-auto">
          <button type="submit" class="btn btn-outline-secondary">
            <i data-lucide="filter" style="width:15px;height:15px;" class="me-1"></i> Filter
          </button>
          <?php if(request('search') || request('status') !== null && request('status') !== ''): ?>
            <a href="<?php echo e(route('vehicle-categories.index')); ?>" class="btn btn-outline-danger ms-1">
              <i data-lucide="x" style="width:15px;height:15px;"></i>
            </a>
          <?php endif; ?>
        </div>

        
        <div class="col"></div>

        
        <div class="col-auto">
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i data-lucide="plus" class="icon-sm me-1"></i> Add Category
          </button>
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
            <th class="py-3">Category Name</th>
            <th class="py-3">Passenger Capacity</th>
            <th class="py-3">Status</th>
            <th class="py-3 text-center">Actions</th>
          </tr>
        </thead>
        <tbody id="categoriesTableBody">
          <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
              <td class="ps-4 py-3 text-muted"><?php echo e($categories->firstItem() + $loop->index); ?></td>
              <td class="py-3 fw-semibold"><?php echo e($category->vehicle_name); ?></td>
              <td class="py-3 text-secondary"><?php echo e($category->passenger_capacity); ?> Seats</td>
              <td class="py-3">
                <?php if($category->status == 1): ?>
                  <span class="badge rounded-pill px-3 py-1" style="background:#eef4ff;color:#3f6fd9;">Active</span>
                <?php else: ?>
                  <span class="badge rounded-pill px-3 py-1" style="background:#f3f4f6;color:#6b7280;">Inactive</span>
                <?php endif; ?>
              </td>
              <td class="py-3 text-center">
                <div class="action-btns">
                  <button type="button" class="action-btn action-btn-view" title="View"
                          onclick='openViewModal(<?php echo json_encode($category, 15, 512) ?>)'>
                    <i data-lucide="eye"></i>
                  </button>
                  <button type="button" class="action-btn action-btn-edit" title="Edit"
                          onclick='openEditModal(<?php echo json_encode($category, 15, 512) ?>)'>
                    <i data-lucide="edit-2"></i>
                  </button>
                  <form action="<?php echo e(route('vehicle-categories.destroy', $category->id)); ?>" method="POST"
                        onsubmit="confirmDelete(event, this)">
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
              <td colspan="5" class="text-center py-5">
                <div class="mb-3 text-muted">
                  <i data-lucide="layers" style="width:48px;height:48px;opacity:0.4;"></i>
                </div>
                <h6 class="text-secondary">No Categories Found</h6>
                <p class="text-muted small mb-0">Try adjusting your search or filters.</p>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php if (isset($component)) { $__componentOriginal38def3cf8acaa014b33af3e7e7449e67 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38def3cf8acaa014b33af3e7e7449e67 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app-pagination','data' => ['paginator' => $categories,'label' => 'categories']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($categories),'label' => 'categories']); ?>
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


<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold">Add Category</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="<?php echo e(route('vehicle-categories.store')); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <div class="modal-body pt-2">
          <div class="mb-3">
            <label class="form-label fw-semibold">Vehicle Name <span class="text-danger">*</span></label>
            <input type="text" name="vehicle_name" class="form-control" required placeholder="e.g. Minibus">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Passenger Capacity <span class="text-danger">*</span></label>
            <input type="number" min="1" name="passenger_capacity" class="form-control" required placeholder="e.g. 14">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Status</label>
            <select name="status" class="form-select">
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary px-4">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>


<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold">Edit Category</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editCategoryForm" method="POST">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <div class="modal-body pt-2">
          <div class="mb-3">
            <label class="form-label fw-semibold">Vehicle Name <span class="text-danger">*</span></label>
            <input type="text" name="vehicle_name" id="edit_vehicle_name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Passenger Capacity <span class="text-danger">*</span></label>
            <input type="number" min="1" name="passenger_capacity" id="edit_passenger_capacity" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Status</label>
            <select name="status" id="edit_status" class="form-select">
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary px-4">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="viewCategoryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold">Category Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-2">
        <div class="row g-3 view-detail-grid">
          <div class="col-12">
            <label class="form-label mb-1">Vehicle Name</label>
            <div class="fw-semibold text-break" id="viewCategoryName">—</div>
          </div>
          <div class="col-md-6">
            <label class="form-label mb-1">Passenger Capacity</label>
            <div class="fw-semibold text-break" id="viewCategoryCapacity">—</div>
          </div>
          <div class="col-md-6">
            <label class="form-label mb-1">Status</label>
            <div class="fw-semibold text-break" id="viewCategoryStatus">—</div>
          </div>
        </div>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('custom-scripts'); ?>
<script>
  <?php if(session('success')): ?>
  Swal.fire({
      icon: 'success', title: 'Success', text: "<?php echo e(session('success')); ?>",
      toast: true, position: 'top-end', showConfirmButton: false,
      timer: 3000, timerProgressBar: true
  });
  <?php endif; ?>

  // Submit search on Enter
  document.getElementById('categorySearch').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') document.getElementById('filterForm').submit();
  });

  const editModal = new bootstrap.Modal(document.getElementById('editCategoryModal'));

  function openViewModal(category) {
    document.getElementById('viewCategoryName').textContent = category.vehicle_name || '—';
    document.getElementById('viewCategoryCapacity').textContent = category.passenger_capacity
      ? category.passenger_capacity + ' Seats'
      : '—';
    document.getElementById('viewCategoryStatus').textContent = Number(category.status) === 1 ? 'Active' : 'Inactive';
    new bootstrap.Modal(document.getElementById('viewCategoryModal')).show();
  }

  // route() keeps the correct base path when the app is served from a sub-folder.
  const updateCategoryUrlTemplate = "<?php echo e(route('vehicle-categories.update', ':id')); ?>";

  function openEditModal(category) {
    document.getElementById('edit_vehicle_name').value      = category.vehicle_name;
    document.getElementById('edit_passenger_capacity').value = category.passenger_capacity;
    document.getElementById('edit_status').value            = category.status;
    document.getElementById('editCategoryForm').action      = updateCategoryUrlTemplate.replace(':id', category.id);
    editModal.show();
  }

  function confirmDelete(event, form) {
    event.preventDefault();
    Swal.fire({
      title: 'Are you sure?', text: "You won't be able to revert this!",
      icon: 'warning', showCancelButton: true,
      confirmButtonColor: '#d33', cancelButtonColor: '#6c757d',
      confirmButtonText: 'Yes, delete it!'
    }).then(result => { if (result.isConfirmed) form.submit(); });
  }
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layout.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\Pick-Drop\resources\views/pickdrop/vehicle-categories/index.blade.php ENDPATH**/ ?>