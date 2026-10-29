<?php $__env->startSection('content'); ?>


<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Vehicle Management</h4>
    <p class="text-secondary mb-0">Monitor and manage all vehicles</p>
  </div>
</div>


<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" action="<?php echo e(route('vehicles.index')); ?>" id="filterForm">
      <div class="row g-2 align-items-center">

        
        <div class="col-12 col-md-4">
          <div class="input-group">
            <div class="input-group-text bg-transparent border-end-0">
              <i data-lucide="search" style="width:16px;height:16px;"></i>
            </div>
            <input type="text" name="search" class="form-control border-start-0 ps-0"
                   id="vehicleSearch" placeholder="Search vehicles..."
                   value="<?php echo e(request('search')); ?>">
          </div>
        </div>

        
        <div class="col-12 col-md-3">
          <select class="form-select" name="status" id="statusFilter" onchange="this.form.submit()">
            <option value="all" <?php echo e(request('status', 'all') === 'all' ? 'selected' : ''); ?>>All Statuses</option>
            <option value="Active"       <?php echo e(request('status') === 'Active'       ? 'selected' : ''); ?>>Active</option>
            <option value="Inactive"     <?php echo e(request('status') === 'Inactive'     ? 'selected' : ''); ?>>Inactive</option>
            <option value="Maintenance"  <?php echo e(request('status') === 'Maintenance'  ? 'selected' : ''); ?>>Maintenance</option>
          </select>
        </div>

        
        <div class="col-auto">
          <button type="submit" class="btn btn-outline-secondary">
            <i data-lucide="filter" style="width:15px;height:15px;" class="me-1"></i> Filter
          </button>
          <?php if(request('search') || (request('status') && request('status') !== 'all')): ?>
            <a href="<?php echo e(route('vehicles.index')); ?>" class="btn btn-outline-danger ms-1">
              <i data-lucide="x" style="width:15px;height:15px;"></i>
            </a>
          <?php endif; ?>
        </div>

        
        <div class="col"></div>

        
        <div class="col-auto">
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVehicleModal">
            <i data-lucide="plus" class="icon-sm me-1"></i> Add Vehicle
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
            <th class="py-3">Vehicle Name</th>
            <th class="py-3">Plate No.</th>
            <th class="py-3">Type</th>
            <th class="py-3">Capacity</th>
            <th class="py-3">Driver</th>
            <th class="py-3">Route</th>
            <th class="py-3">Status</th>
            <th class="py-3 text-center">Actions</th>
          </tr>
        </thead>
        <tbody id="vehiclesTableBody">
          <?php $__empty_1 = true; $__currentLoopData = $vehicles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vehicle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <tr>
            <td class="ps-4 py-3 text-muted"><?php echo e($vehicles->firstItem() + $loop->index); ?></td>
            <td class="py-3 fw-semibold"><?php echo e($vehicle->name); ?></td>
            <td class="py-3 text-secondary"><?php echo e($vehicle->license_plate); ?></td>
            <td class="py-3 text-secondary"><?php echo e(optional($vehicle->category)->vehicle_name ?? '—'); ?></td>
            <td class="py-3 text-secondary">
              <?php echo e(optional($vehicle->category)->passenger_capacity ? optional($vehicle->category)->passenger_capacity . ' Seats' : '—'); ?>

            </td>
            <td class="py-3 text-secondary"><?php echo e(optional($vehicle->driver)->name ?? '—'); ?></td>
            <td class="py-3 text-secondary"><?php echo e($vehicle->route_id ?: '—'); ?></td>
            <td class="py-3">
              <?php $s = strtolower($vehicle->status); ?>
              <?php if($s === 'active'): ?>
                <span class="badge rounded-pill px-3 py-1" style="background:#eef4ff;color:#3f6fd9;">Active</span>
              <?php elseif($s === 'maintenance'): ?>
                <span class="badge rounded-pill px-3 py-1" style="background:#fef3c7;color:#92400e;">Maintenance</span>
              <?php else: ?>
                <span class="badge rounded-pill px-3 py-1" style="background:#f3f4f6;color:#6b7280;"><?php echo e($vehicle->status); ?></span>
              <?php endif; ?>
            </td>
            <td class="py-3 text-center">
              <div class="action-btns">
<?php if($vehicle->driver_id): ?>
<form action="<?php echo e(route('vehicles.unassign', $vehicle->id)); ?>" method="POST"
      onsubmit="confirmUnassign(event, this, <?php echo e(Js::from(optional($vehicle->driver)->name)); ?>, <?php echo e(Js::from($vehicle->name)); ?>)">
    <?php echo csrf_field(); ?>
    <button type="submit" class="action-btn action-btn-warn" title="Unassign Driver">
        <i data-lucide="user-minus"></i>
    </button>
</form>
<?php else: ?>
<button type="button" class="action-btn action-btn-add" title="Assign Driver"
    onclick="openAssignDriverModal(<?php echo e(Js::from(['id' => $vehicle->id, 'name' => $vehicle->name, 'license_plate' => $vehicle->license_plate])); ?>)">
    <i data-lucide="user-plus"></i>
</button>
<?php endif; ?>
                <button type="button" class="action-btn action-btn-view" title="View"
                  onclick='openDetailsModal(<?php echo json_encode(collect($vehicle->toArray())->merge([
                    "type_name"   => optional($vehicle->category)->vehicle_name, "capacity_val"=> optional($vehicle->category)->passenger_capacity, "driver_name" => optional($vehicle->driver)->name
                  ])) ?>, false)'>
                  <i data-lucide="eye"></i>
                </button>
                <button type="button" class="action-btn action-btn-edit" title="Edit"
                  onclick='openDetailsModal(<?php echo json_encode(collect($vehicle->toArray())->merge([
                    "type_name"   => optional($vehicle->category)->vehicle_name, "capacity_val"=> optional($vehicle->category)->passenger_capacity, "driver_name" => optional($vehicle->driver)->name
                  ])) ?>, true)'>
                  <i data-lucide="edit-2"></i>
                </button>
                <form action="<?php echo e(route('vehicles.destroy', $vehicle->id)); ?>" method="POST"
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
            <td colspan="9" class="text-center py-5">
              <div class="mb-3 text-muted">
                <i data-lucide="truck" style="width:48px;height:48px;opacity:0.4;"></i>
              </div>
              <h6 class="text-secondary">No Vehicles Found</h6>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app-pagination','data' => ['paginator' => $vehicles,'label' => 'vehicles']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($vehicles),'label' => 'vehicles']); ?>
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



<div class="modal fade" id="addVehicleModal" tabindex="-1" aria-labelledby="addVehicleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="addVehicleModalLabel">Add New Vehicle</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-2">
        <form action="<?php echo e(route('vehicles.store')); ?>" method="POST">
          <?php echo csrf_field(); ?>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Vehicle Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" placeholder="e.g. Bus #46" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Plate Number <span class="text-danger">*</span></label>
              <input type="text" name="license_plate" class="form-control" placeholder="e.g. ABC-1234" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Type</label>
              <select name="vehicle_type_id" id="add_vehicle_type" class="form-select">
                <option value="">Select type</option>
                <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($type->id); ?>" data-capacity="<?php echo e($type->passenger_capacity); ?>">
                    <?php echo e($type->vehicle_name); ?>

                  </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Capacity</label>
              <input type="text" id="add_vehicle_capacity" class="form-control" placeholder="Auto-filled" disabled>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Assign Driver</label>
              <select name="driver_id" class="form-select">
                <option value="">Select driver</option>
                <?php $__currentLoopData = $drivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($driver->id); ?>"><?php echo e($driver->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Assign Route</label>
              <input type="text" name="route_id" class="form-control" placeholder="e.g. Route #R-123">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Status</label>
              <select name="status" class="form-select">
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
                <option value="Maintenance">Maintenance</option>
              </select>
            </div>
          </div>
          <div class="modal-footer border-0 pt-4 pb-0 px-0">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary px-4">Add Vehicle</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="assignDriverModal" tabindex="-1" aria-labelledby="assignDriverModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="assignDriverModalLabel">Assign Driver</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-2">
        <form id="assignDriverForm" method="POST">
          <?php echo csrf_field(); ?>
          <div class="row g-3">
            <div class="col-6">
              <label class="form-label mb-1 text-muted">Vehicle</label>
              <div class="fw-semibold text-break" id="assignVehicleName">—</div>
            </div>
            <div class="col-6">
              <label class="form-label mb-1 text-muted">Plate Number</label>
              <div class="fw-semibold text-break" id="assignVehiclePlate">—</div>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Driver <span class="text-danger">*</span></label>
              <select name="driver_id" id="assignDriverSelect" class="form-select" required>
                <option value="">Select driver</option>
                <?php $__currentLoopData = $availableDrivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($driver->id); ?>"><?php echo e($driver->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </select>
              <?php if($availableDrivers->isEmpty()): ?>
                <small class="text-muted d-block mt-1">No unassigned drivers available.</small>
              <?php endif; ?>
            </div>
          </div>
          <div class="modal-footer border-0 pt-4 pb-0 px-0">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary px-4" <?php if($availableDrivers->isEmpty()): echo 'disabled'; endif; ?>>Assign Driver</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="viewVehicleModal" tabindex="-1" aria-labelledby="viewVehicleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="viewVehicleModalLabel">Vehicle Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">

        
        <div id="vehicleViewMode">
          <div class="row g-3 view-detail-grid">
            <div class="col-6">
              <label class="form-label mb-1">Vehicle Name</label>
              <div class="fw-semibold text-break" id="vDetailName">—</div>
            </div>
            <div class="col-6">
              <label class="form-label mb-1">Plate Number</label>
              <div class="fw-semibold text-break" id="vDetailPlate">—</div>
            </div>
            <div class="col-6">
              <label class="form-label mb-1">Type</label>
              <div class="fw-semibold text-break" id="vDetailType">—</div>
            </div>
            <div class="col-6">
              <label class="form-label mb-1">Capacity</label>
              <div class="fw-semibold text-break" id="vDetailCapacity">—</div>
            </div>
            <div class="col-6">
              <label class="form-label mb-1">Driver</label>
              <div class="fw-semibold text-break" id="vDetailDriver">—</div>
            </div>
            <div class="col-6">
              <label class="form-label mb-1">Route</label>
              <div class="fw-semibold text-break" id="vDetailRoute">—</div>
            </div>
            <div class="col-6">
              <label class="form-label mb-1">Status</label>
              <span class="badge rounded-pill px-3 py-1" id="vDetailStatusBadge">—</span>
            </div>
          </div>
          <div class="mt-4">
            <button type="button" class="btn btn-light w-100" data-bs-dismiss="modal">Close</button>
          </div>
        </div>

        
        <div id="vehicleEditMode" style="display:none;">
          <form id="editVehicleForm" method="POST">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Vehicle Name <span class="text-danger">*</span></label>
                <input type="text" name="name" id="vEditName" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Plate Number <span class="text-danger">*</span></label>
                <input type="text" name="license_plate" id="vEditPlate" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Type</label>
                <select name="vehicle_type_id" id="vEditType" class="form-select">
                  <option value="">Select type</option>
                  <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($type->id); ?>"><?php echo e($type->vehicle_name); ?></option>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Status</label>
                <select name="status" id="vEditStatus" class="form-select">
                  <option value="Active">Active</option>
                  <option value="Inactive">Inactive</option>
                  <option value="Maintenance">Maintenance</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold">Assign Driver</label>
                <select name="driver_id" id="vEditDriver" class="form-select">
                  <option value="">Select driver</option>
                  <?php $__currentLoopData = $drivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($driver->id); ?>"><?php echo e($driver->name); ?></option>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold">Assign Route</label>
                <input type="text" name="route_id" id="vEditRoute" class="form-control">
              </div>
            </div>
            <div class="mt-4 d-flex gap-2">
              <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary px-4 flex-grow-1">Save Changes</button>
            </div>
          </form>
        </div>

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

  <?php if(session('error')): ?>
  Swal.fire({
      icon: 'error', title: 'Error', text: <?php echo json_encode(session('error'), 15, 512) ?>,
      toast: true, position: 'top-end', showConfirmButton: false,
      timer: 4000, timerProgressBar: true
  });
  <?php endif; ?>

  // Submit search on Enter
  document.getElementById('vehicleSearch').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') document.getElementById('filterForm').submit();
  });

  // Auto-fill capacity when type selected (Add modal)
  document.getElementById('add_vehicle_type').addEventListener('change', function () {
    const cap = this.options[this.selectedIndex].getAttribute('data-capacity');
    document.getElementById('add_vehicle_capacity').value = cap ? cap + ' Seats' : '';
  });

  // View/Edit modal
  const viewModal = new bootstrap.Modal(document.getElementById('viewVehicleModal'));

  const updateVehicleUrlTemplate = "<?php echo e(route('vehicles.update', ':id')); ?>";

  function openDetailsModal(vehicle, startInEdit = false) {
    toggleEditMode(!!startInEdit);

    document.getElementById('viewVehicleModalLabel').textContent = 'Vehicle — ' + vehicle.name;

    document.getElementById('vDetailName').textContent     = vehicle.name        || '—';
    document.getElementById('vDetailPlate').textContent    = vehicle.license_plate || '—';
    document.getElementById('vDetailType').textContent     = vehicle.category?.vehicle_name   || '—';
    document.getElementById('vDetailCapacity').textContent = vehicle.category?.passenger_capacity ? vehicle.category.passenger_capacity + ' Seats' : '—';
    document.getElementById('vDetailDriver').textContent   = vehicle.driver_name || '—';
    document.getElementById('vDetailRoute').textContent    = vehicle.route_id    || '—';

    const badge = document.getElementById('vDetailStatusBadge');
    badge.textContent = vehicle.status || '—';
    const s = (vehicle.status || '').toLowerCase();
    if (s === 'active') {
      badge.style.background = '#eef4ff'; badge.style.color = '#3f6fd9';
    } else if (s === 'maintenance') {
      badge.style.background = '#fef3c7'; badge.style.color = '#92400e';
    } else {
      badge.style.background = '#f3f4f6'; badge.style.color = '#6b7280';
    }

    document.getElementById('editVehicleForm').action   = updateVehicleUrlTemplate.replace(':id', vehicle.id);

    document.getElementById('vEditName').value    = vehicle.name          || '';
    document.getElementById('vEditPlate').value   = vehicle.license_plate || '';
    // DB column is vehicle_category_id; keep fallback for older payloads
    document.getElementById('vEditType').value    = vehicle.vehicle_category_id || vehicle.vehicle_type_id || '';
    document.getElementById('vEditDriver').value  = vehicle.driver_id     || '';
    document.getElementById('vEditRoute').value   = vehicle.route_id      || '';
    document.getElementById('vEditStatus').value  = vehicle.status        || 'Active';

    if (typeof lucide !== 'undefined') lucide.createIcons();
    viewModal.show();
  }

  // Dedicated Assign Driver form (separate from the View/Edit modal)
  const assignModal = new bootstrap.Modal(document.getElementById('assignDriverModal'));
  const assignUrlTemplate = "<?php echo e(route('vehicles.assign-driver', ':id')); ?>";

  function openAssignDriverModal(vehicle) {
    document.getElementById('assignVehicleName').textContent  = vehicle.name || '—';
    document.getElementById('assignVehiclePlate').textContent = vehicle.license_plate || '—';
    document.getElementById('assignDriverForm').action        = assignUrlTemplate.replace(':id', vehicle.id);
    document.getElementById('assignDriverSelect').value       = '';
    assignModal.show();
  }

  function toggleEditMode(isEdit) {
    document.getElementById('vehicleViewMode').style.display = isEdit ? 'none'  : 'block';
    document.getElementById('vehicleEditMode').style.display = isEdit ? 'block' : 'none';
  }

  function confirmUnassign(event, form, driverName, vehicleName) {
    event.preventDefault();
    Swal.fire({
      title: 'Unassign driver?',
      text: (driverName ? driverName : 'This driver') + ' will be removed from ' + (vehicleName || 'this vehicle') + '.',
      icon: 'warning', showCancelButton: true,
      confirmButtonColor: '#f59e0b', cancelButtonColor: '#6c757d',
      confirmButtonText: 'Yes, unassign'
    }).then(result => { if (result.isConfirmed) form.submit(); });
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

<?php $__env->startPush('style'); ?>
<style>
  .w-38px { width: 38px; }
  .h-38px { height: 38px; }
</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layout.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Pick-Drop\resources\views/pickdrop/vehicles/index.blade.php ENDPATH**/ ?>