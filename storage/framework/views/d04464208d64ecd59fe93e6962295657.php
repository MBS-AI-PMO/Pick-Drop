<?php $__env->startSection('content'); ?>


<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Route Management</h4>
    <p class="text-secondary mb-0">Create and manage transportation routes</p>
  </div>
</div>


<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" action="<?php echo e(route('routes.index')); ?>" id="routeFilterForm">
      <div class="row g-2 align-items-center">

        
        <div class="col-12 col-md-4">
          <div class="input-group">
            <div class="input-group-text bg-transparent border-end-0">
              <i data-lucide="search" style="width:16px;height:16px;"></i>
            </div>
            <input type="text" name="search" class="form-control border-start-0 ps-0"
                   id="routeSearch" placeholder="Search routes..."
                   value="<?php echo e(request('search')); ?>">
          </div>
        </div>

        
        <div class="col-12 col-md-3">
          <select class="form-select" name="city" id="cityFilter" onchange="this.form.submit()">
            <option value="all" <?php echo e(request('city', 'all') === 'all' ? 'selected' : ''); ?>>All Cities</option>
            <?php $__currentLoopData = ($availableCities ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $city): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($city); ?>" <?php echo e(request('city') === $city ? 'selected' : ''); ?>><?php echo e($city); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>

        
        <div class="col-auto">
          <button type="submit" class="btn btn-outline-secondary">
            <i data-lucide="filter" style="width:15px;height:15px;" class="me-1"></i> Filter
          </button>
          <?php if(request('search') || (request('city') && request('city') !== 'all')): ?>
            <a href="<?php echo e(route('routes.index')); ?>" class="btn btn-outline-danger ms-1">
              <i data-lucide="x" style="width:15px;height:15px;"></i>
            </a>
          <?php endif; ?>
        </div>

        
        <div class="col"></div>

        
        <div class="col-auto">
          <a href="<?php echo e(route('routes.create')); ?>" class="btn btn-primary">
            <i data-lucide="plus" class="icon-sm me-1"></i> Create Route
          </a>
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
            <th class="ps-4 py-3">Route</th>
            <th class="py-3">Destination</th>
            <th class="py-3">Vehicle</th>
            <th class="py-3">Students</th>
            <th class="py-3">Timing</th>
            <th class="py-3">Stops</th>
            <th class="py-3">Shift</th>
            <th class="py-3 text-end pe-4">Actions</th>
          </tr>
        </thead>
        <tbody id="routesTableBody">

          <?php $__empty_1 = true; $__currentLoopData = $routes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $route): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <?php
            $routeViewData = [
              'id' => $route->id,
              'code' => $route->code ?? ('#R-'.$route->id),
              'name' => $route->name,
              'destination' => $route->destination,
              'city' => $route->city->name ?? '—',
              'area' => $route->area->name ?? '—',
              'vehicle' => $route->vehicle
                ? $route->vehicle->name.' ('.$route->vehicle->license_plate.')'
                : '—',
              'timing' => ($route->start_time && $route->end_time)
                ? \Carbon\Carbon::parse($route->start_time)->format('g:i A').' – '.\Carbon\Carbon::parse($route->end_time)->format('g:i A')
                : '—',
              'shift' => $route->shift ? ucfirst($route->shift) : '—',
              'stops_count' => $route->stops_count,
              'description' => $route->description,
            ];
          ?>
          <tr>
            <td class="ps-4 py-3 text-muted"><?php echo e($routes->firstItem() + $loop->index); ?></td>
            <td class="ps-2">
              <div class="d-flex align-items-center gap-2">
                <div class="w-36px h-36px rounded-2 d-flex align-items-center justify-content-center flex-shrink-0"
                     style="background:rgba(var(--bs-primary-rgb),0.1);">
                  <i data-lucide="map" class="text-primary" style="width:16px;height:16px;"></i>
                </div>
                <div>
                  <p class="mb-0 fw-semibold fs-14px"><?php echo e($route->code ?? ('#R-'.$route->id)); ?></p>
                  <small class="text-secondary"><?php echo e($route->name); ?></small>
                </div>
              </div>
            </td>
            <td>
              <span class="fs-13px"><?php echo e($route->destination ?? '—'); ?></span>
            </td>
            <td>
              <?php if($route->vehicle): ?>
                <div class="d-flex align-items-center gap-1">
                  <i data-lucide="bus" class="text-info" style="width:14px;height:14px;"></i>
                  <span class="fs-13px"><?php echo e($route->vehicle->name); ?> (<?php echo e($route->vehicle->license_plate); ?>)</span>
                </div>
              <?php else: ?>
                <span class="text-secondary fs-13px">—</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if($route->start_time && $route->end_time): ?>
                <span class="fs-13px">
                  <?php echo e(\Carbon\Carbon::parse($route->start_time)->format('g:i A')); ?>

                  –
                  <?php echo e(\Carbon\Carbon::parse($route->end_time)->format('g:i A')); ?>

                </span>
              <?php else: ?>
                <span class="fs-13px text-secondary">—</span>
              <?php endif; ?>
            </td>
            <td>
              <button class="btn btn-sm btn-light px-2 py-1 fs-12px"
                      data-bs-toggle="modal"
                      data-bs-target="#viewStopsModal"
                      data-route-id="<?php echo e($route->id); ?>">
                <i data-lucide="map-pin" style="width:12px;height:12px;" class="me-1"></i>
                <?php echo e($route->stops_count); ?> Stops
              </button>
            </td>
            <td>
              <?php $shift = strtolower($route->shift); ?>
              <?php if($shift === 'morning'): ?>
                <span class="badge rounded-pill px-3 py-1" style="background:#dbeafe;color:#1d4ed8;">Morning</span>
              <?php elseif($shift === 'afternoon'): ?>
                <span class="badge rounded-pill px-3 py-1" style="background:#fef3c7;color:#92400e;">Afternoon</span>
              <?php else: ?>
                <span class="badge rounded-pill px-3 py-1" style="background:#f3f4f6;color:#6b7280;"><?php echo e($route->shift); ?></span>
              <?php endif; ?>
            </td>
            <td class="text-end pe-4">
              <div class="action-btns">
                <button type="button" class="action-btn action-btn-view" title="View"
                        onclick='openViewRouteModal(<?php echo json_encode($routeViewData, 15, 512) ?>)'>
                  <i data-lucide="eye"></i>
                </button>
                <a href="<?php echo e(route('routes.edit', $route)); ?>" class="action-btn action-btn-edit" title="Edit">
                  <i data-lucide="edit-2"></i>
                </a>
                <form action="<?php echo e(route('routes.destroy', $route)); ?>" method="POST"
                      onsubmit="confirmRouteDelete(event, this)">
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
            <td colspan="8" class="text-center py-5">
              <div class="mb-3 text-muted">
                <i data-lucide="map" style="width:48px;height:48px;opacity:0.4;"></i>
              </div>
              <h6 class="text-secondary">No Routes Found</h6>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app-pagination','data' => ['paginator' => $routes,'label' => 'routes']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($routes),'label' => 'routes']); ?>
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



<div class="modal fade" id="viewRouteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold">Route Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-2">
        <div class="row g-3 view-detail-grid">
          <div class="col-6">
            <label class="form-label mb-1">Code</label>
            <div class="fw-semibold text-break" id="viewRouteCode">—</div>
          </div>
          <div class="col-6">
            <label class="form-label mb-1">Name</label>
            <div class="fw-semibold text-break" id="viewRouteName">—</div>
          </div>
          <div class="col-6">
            <label class="form-label mb-1">City</label>
            <div class="fw-semibold text-break" id="viewRouteCity">—</div>
          </div>
          <div class="col-6">
            <label class="form-label mb-1">Area</label>
            <div class="fw-semibold text-break" id="viewRouteArea">—</div>
          </div>
          <div class="col-12">
            <label class="form-label mb-1">Destination</label>
            <div class="fw-semibold text-break" id="viewRouteDestination">—</div>
          </div>
          <div class="col-6">
            <label class="form-label mb-1">Vehicle</label>
            <div class="fw-semibold text-break" id="viewRouteVehicle">—</div>
          </div>
          <div class="col-6">
            <label class="form-label mb-1">Timing</label>
            <div class="fw-semibold text-break" id="viewRouteTiming">—</div>
          </div>
          <div class="col-6">
            <label class="form-label mb-1">Shift</label>
            <div class="fw-semibold text-break" id="viewRouteShift">—</div>
          </div>
          <div class="col-6">
            <label class="form-label mb-1">Stops</label>
            <div class="fw-semibold text-break" id="viewRouteStops">—</div>
          </div>
          <div class="col-12">
            <label class="form-label mb-1">Description</label>
            <div class="fw-semibold text-break" id="viewRouteDescription">—</div>
          </div>
        </div>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="viewStopsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="stopsModalTitle">Route Stops</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-2">
        <p class="text-secondary fs-13px mb-3" id="stopsModalSubtitle">
          Stops on this route (added via mobile app by parents/students).
        </p>
        <div class="d-flex flex-column gap-3" id="stopsModalBody">
          
          <p class="text-muted fs-13px mb-0">No stop details loaded.</p>
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
  const routeSearch = document.getElementById('routeSearch');
  if (routeSearch) {
    routeSearch.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') document.getElementById('routeFilterForm').submit();
    });
  }

  function openViewRouteModal(route) {
    document.getElementById('viewRouteCode').textContent = route.code || '—';
    document.getElementById('viewRouteName').textContent = route.name || '—';
    document.getElementById('viewRouteCity').textContent = route.city || '—';
    document.getElementById('viewRouteArea').textContent = route.area || '—';
    document.getElementById('viewRouteDestination').textContent = route.destination || '—';
    document.getElementById('viewRouteVehicle').textContent = route.vehicle || '—';
    document.getElementById('viewRouteTiming').textContent = route.timing || '—';
    document.getElementById('viewRouteShift').textContent = route.shift || '—';
    document.getElementById('viewRouteStops').textContent = (route.stops_count ?? 0) + ' Stops';
    document.getElementById('viewRouteDescription').textContent = route.description || '—';
    new bootstrap.Modal(document.getElementById('viewRouteModal')).show();
    if (typeof lucide !== 'undefined') lucide.createIcons();
  }

  function confirmRouteDelete(event, form) {
    event.preventDefault();
    Swal.fire({
      title: 'Are you sure?',
      text: "This will permanently delete the route.",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Yes, delete it!'
    }).then(result => { if (result.isConfirmed) form.submit(); });
  }
</script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('style'); ?>
<style>
  .w-36px { width: 36px; }
  .h-36px { height: 36px; }
  .w-8px  { width: 8px; min-width: 8px; }
  .h-8px  { height: 8px; }
  .fs-14px { font-size: 14px; }
  .fs-12px { font-size: 12px; }
  .route-line {
    width: 2px;
    background: #dee2e6;
    min-height: 20px;
    margin: 4px auto;
  }
</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layout.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Pick-Drop\resources\views/pickdrop/routes/index.blade.php ENDPATH**/ ?>