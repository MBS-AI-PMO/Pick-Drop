<?php $__env->startSection('content'); ?>


<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Institution Management</h4>
    <p class="text-secondary mb-0">Manage schools, colleges, universities, offices and other pickup destinations</p>
  </div>
</div>


<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" action="<?php echo e(route('schools.index')); ?>" id="schoolFilterForm">
      <div class="row g-2 align-items-center">

        
        <div class="col-12 col-md-4">
          <div class="input-group">
            <div class="input-group-text bg-transparent border-end-0">
              <i data-lucide="search" style="width:16px;height:16px;"></i>
            </div>
            <input type="text" name="search" class="form-control border-start-0 ps-0"
                   id="schoolSearch" placeholder="Search institutions..."
                   value="<?php echo e(request('search')); ?>">
          </div>
        </div>

        
        <div class="col-12 col-md-3">
          <select class="form-select" name="category" id="categoryFilter" onchange="this.form.submit()">
            <option value="">All Categories</option>
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($value); ?>" <?php echo e(request('category') === $value ? 'selected' : ''); ?>><?php echo e($label); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>

        
        <div class="col-auto">
          <button type="submit" class="btn btn-outline-secondary">
            <i data-lucide="filter" style="width:15px;height:15px;" class="me-1"></i> Filter
          </button>
          <?php if(request('search') || request('category')): ?>
            <a href="<?php echo e(route('schools.index')); ?>" class="btn btn-outline-danger ms-1">
              <i data-lucide="x" style="width:15px;height:15px;"></i>
            </a>
          <?php endif; ?>
        </div>

        
        <div class="col"></div>

        
        <div class="col-auto">
          <button type="button" class="btn btn-primary" onclick="openAddSchoolModal()">
            <i data-lucide="plus" class="icon-sm me-1"></i> Add Institution
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
            <th class="py-3">Institution</th>
            <th class="py-3">Category</th>
            <th class="py-3">City</th>
            <th class="py-3">Contact</th>
            <th class="py-3">Status</th>
            <th class="py-3 text-end pe-4">Actions</th>
          </tr>
        </thead>
        <tbody id="schoolsTableBody">
          <?php $__empty_1 = true; $__currentLoopData = $schools; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $school): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <?php
            $schoolData = [
              'id' => $school->id,
              'name' => $school->name,
              'category' => $school->category,
              'category_label' => $school->categoryLabel(),
              'city_id' => $school->city_id,
              'city' => $school->city?->name,
              'address' => $school->address,
              'phone' => $school->phone,
              'email' => $school->email,
              'status' => $school->status,
            ];
          ?>
          <tr>
            <td class="ps-4 py-3 text-muted"><?php echo e($schools->firstItem() + $loop->index); ?></td>
            <td class="ps-2">
              <div class="d-flex align-items-center gap-2">
                <div class="w-36px h-36px rounded-2 d-flex align-items-center justify-content-center flex-shrink-0"
                     style="background:rgba(var(--bs-primary-rgb),0.1);">
                  <i data-lucide="school" class="text-primary" style="width:16px;height:16px;"></i>
                </div>
                <div>
                  <p class="mb-0 fw-semibold fs-14px"><?php echo e($school->name); ?></p>
                  <small class="text-secondary"><?php echo e($school->address ?: 'No address added'); ?></small>
                </div>
              </div>
            </td>
            <td>
              <span class="badge rounded-pill px-3 py-1" style="background:#eef2ff;color:#4338ca;"><?php echo e($school->categoryLabel()); ?></span>
            </td>
            <td><span class="fs-13px"><?php echo e($school->city?->name ?: '—'); ?></span></td>
            <td>
              <?php if($school->phone || $school->email): ?>
                <?php if($school->phone): ?>
                  <div class="d-flex align-items-center gap-1 fs-13px">
                    <i data-lucide="phone" class="text-secondary" style="width:13px;height:13px;"></i><?php echo e($school->phone); ?>

                  </div>
                <?php endif; ?>
                <?php if($school->email): ?>
                  <div class="d-flex align-items-center gap-1 fs-13px">
                    <i data-lucide="mail" class="text-secondary" style="width:13px;height:13px;"></i><?php echo e($school->email); ?>

                  </div>
                <?php endif; ?>
              <?php else: ?>
                <span class="text-secondary fs-13px">—</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if(strtolower($school->status) === 'active'): ?>
                <span class="badge rounded-pill px-3 py-1" style="background:#eef4ff;color:#3f6fd9;">Active</span>
              <?php else: ?>
                <span class="badge rounded-pill px-3 py-1" style="background:#f3f4f6;color:#6b7280;"><?php echo e($school->status); ?></span>
              <?php endif; ?>
            </td>
            <td class="text-end pe-4">
              <div class="action-btns">
                <button type="button" class="action-btn action-btn-view" title="View"
                        onclick="openViewSchoolModal(<?php echo e(Js::from($schoolData)); ?>)">
                  <i data-lucide="eye"></i>
                </button>
                <button type="button" class="action-btn action-btn-edit" title="Edit"
                        onclick="openEditSchoolModal(<?php echo e(Js::from($schoolData)); ?>)">
                  <i data-lucide="edit-2"></i>
                </button>
                <form action="<?php echo e(route('schools.destroy', $school)); ?>" method="POST"
                      onsubmit="confirmSchoolDelete(event, this, <?php echo e(Js::from($school->name)); ?>)">
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
            <td colspan="7" class="text-center py-5">
              <div class="mb-3 text-muted">
                <i data-lucide="school" style="width:48px;height:48px;opacity:0.4;"></i>
              </div>
              <h6 class="text-secondary">No Institutions Found</h6>
              <p class="text-muted small mb-0">Try adjusting your search or filters, or add a new institution.</p>
            </td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
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



<?php $__currentLoopData = ['add' => ['id' => 'addSchoolModal', 'bag' => 'addSchool', 'title' => 'Add Institution', 'submit' => 'Add Institution'], 'edit' => ['id' => 'editSchoolModal', 'bag' => 'editSchool', 'title' => 'Edit Institution', 'submit' => 'Save Changes']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mode => $cfg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php $bag = $errors->getBag($cfg['bag']); ?>
<div class="modal fade" id="<?php echo e($cfg['id']); ?>" tabindex="-1" aria-labelledby="<?php echo e($cfg['id']); ?>Label" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="<?php echo e($cfg['id']); ?>Label"><?php echo e($cfg['title']); ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-2">
        <form method="POST" id="<?php echo e($mode); ?>SchoolForm"
              action="<?php echo e($mode === 'add' ? route('schools.store') : '#'); ?>" novalidate>
          <?php echo csrf_field(); ?>
          <?php if($mode === 'edit'): ?>
            <?php echo method_field('PUT'); ?>
            <input type="hidden" name="_school_id" id="editSchoolId" value="<?php echo e(old('_school_id')); ?>">
          <?php endif; ?>
          <input type="hidden" name="status" id="<?php echo e($mode); ?>SchoolStatusHidden" value="Active" <?php if($mode === 'edit'): ?> disabled <?php endif; ?>>
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label fw-semibold">Institution Name <span class="text-danger">*</span></label>
              <input type="text" name="name" id="<?php echo e($mode); ?>SchoolName"
                     class="form-control <?php echo e($bag->has('name') ? 'is-invalid' : ''); ?>"
                     value="<?php echo e($bag->any() ? old('name') : ''); ?>" maxlength="255" placeholder="e.g. Beaconhouse School" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
              <select name="category" id="<?php echo e($mode); ?>SchoolCategory" class="form-select <?php echo e($bag->has('category') ? 'is-invalid' : ''); ?>" required>
                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($value); ?>" <?php echo e(($bag->any() ? old('category') : 'school') === $value ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">City</label>
              <select name="city_id" id="<?php echo e($mode); ?>SchoolCity" class="form-select <?php echo e($bag->has('city_id') ? 'is-invalid' : ''); ?>">
                <option value="">Select city</option>
                <?php $__currentLoopData = $cities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $city): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($city->id); ?>" <?php echo e($bag->any() && (string) old('city_id') === (string) $city->id ? 'selected' : ''); ?>><?php echo e($city->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Address</label>
              <input type="text" name="address" id="<?php echo e($mode); ?>SchoolAddress"
                     class="form-control <?php echo e($bag->has('address') ? 'is-invalid' : ''); ?>"
                     value="<?php echo e($bag->any() ? old('address') : ''); ?>" maxlength="255" placeholder="Street, area">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Phone</label>
              <input type="tel" inputmode="tel" name="phone" id="<?php echo e($mode); ?>SchoolPhone"
                     class="form-control <?php echo e($bag->has('phone') ? 'is-invalid' : ''); ?>"
                     value="<?php echo e($bag->any() ? old('phone') : ''); ?>" maxlength="50" placeholder="e.g. +92 300 1234567" autocomplete="off">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Email</label>
              <input type="email" inputmode="email" name="email" id="<?php echo e($mode); ?>SchoolEmail"
                     class="form-control <?php echo e($bag->has('email') ? 'is-invalid' : ''); ?>"
                     value="<?php echo e($bag->any() ? old('email') : ''); ?>" maxlength="255" placeholder="name@example.com" autocomplete="off">
            </div>
            <?php if($mode === 'edit'): ?>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Status</label>
              <select name="status" id="editSchoolStatus" class="form-select <?php echo e($bag->has('status') ? 'is-invalid' : ''); ?>">
                <option value="Active" <?php echo e($bag->any() && old('status') === 'Active' ? 'selected' : ''); ?>>Active</option>
                <option value="Inactive" <?php echo e($bag->any() && old('status') === 'Inactive' ? 'selected' : ''); ?>>Inactive</option>
              </select>
            </div>
            <?php endif; ?>
          </div>
          <div class="modal-footer border-0 pt-4 pb-0 px-0">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary px-4"><?php echo e($cfg['submit']); ?></button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>



<div class="modal fade" id="viewSchoolModal" tabindex="-1" aria-labelledby="viewSchoolModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="viewSchoolModalLabel">Institution Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-2">
        <div class="row g-3 view-detail-grid">
          <div class="col-12">
            <label class="form-label mb-1">Institution Name</label>
            <div class="fw-semibold text-break" id="vSchoolName">—</div>
          </div>
          <div class="col-6">
            <label class="form-label mb-1">Category</label>
            <div class="fw-semibold text-break" id="vSchoolCategory">—</div>
          </div>
          <div class="col-6">
            <label class="form-label mb-1">City</label>
            <div class="fw-semibold text-break" id="vSchoolCity">—</div>
          </div>
          <div class="col-12">
            <label class="form-label mb-1">Address</label>
            <div class="fw-semibold text-break" id="vSchoolAddress">—</div>
          </div>
          <div class="col-6">
            <label class="form-label mb-1">Phone</label>
            <div class="fw-semibold text-break" id="vSchoolPhone">—</div>
          </div>
          <div class="col-6">
            <label class="form-label mb-1">Email</label>
            <div class="fw-semibold text-break" id="vSchoolEmail">—</div>
          </div>
          <div class="col-6">
            <label class="form-label mb-1">Status</label>
            <span class="badge rounded-pill px-3 py-1" id="vSchoolStatusBadge">—</span>
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
  const addSchoolModal = new bootstrap.Modal(document.getElementById('addSchoolModal'));
  const editSchoolModal = new bootstrap.Modal(document.getElementById('editSchoolModal'));
  const viewSchoolModal = new bootstrap.Modal(document.getElementById('viewSchoolModal'));
  const updateSchoolUrlTemplate = "<?php echo e(route('schools.update', ':id')); ?>";

  document.getElementById('schoolSearch')?.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') document.getElementById('schoolFilterForm').submit();
  });

  function openAddSchoolModal() {
    const form = document.getElementById('addSchoolForm');
    // reset() would restore values rendered from a previous failed attempt, so clear explicitly.
    form.querySelectorAll('input[type="text"], input[type="tel"], input[type="email"]').forEach(el => { el.value = ''; });
    document.getElementById('addSchoolCategory').value = 'school';
    document.getElementById('addSchoolCity').value = '';
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
    addSchoolModal.show();
  }

  function openEditSchoolModal(school) {
    const form = document.getElementById('editSchoolForm');
    form.action = updateSchoolUrlTemplate.replace(':id', school.id);
    document.getElementById('editSchoolId').value = school.id;
    document.getElementById('editSchoolName').value = school.name || '';
    document.getElementById('editSchoolCategory').value = school.category || 'school';
    document.getElementById('editSchoolCity').value = school.city_id || '';
    document.getElementById('editSchoolAddress').value = school.address || '';
    document.getElementById('editSchoolPhone').value = school.phone || '';
    document.getElementById('editSchoolEmail').value = school.email || '';
    document.getElementById('editSchoolStatus').value = school.status || 'Active';
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
    editSchoolModal.show();
  }

  function openViewSchoolModal(school) {
    document.getElementById('viewSchoolModalLabel').textContent = 'Institution — ' + (school.name || '');
    document.getElementById('vSchoolName').textContent = school.name || '—';
    document.getElementById('vSchoolCategory').textContent = school.category_label || '—';
    document.getElementById('vSchoolCity').textContent = school.city || '—';
    document.getElementById('vSchoolAddress').textContent = school.address || '—';
    document.getElementById('vSchoolPhone').textContent = school.phone || '—';
    document.getElementById('vSchoolEmail').textContent = school.email || '—';
    const badge = document.getElementById('vSchoolStatusBadge');
    badge.textContent = school.status || '—';
    const active = (school.status || '').toLowerCase() === 'active';
    badge.style.background = active ? '#eef4ff' : '#f3f4f6';
    badge.style.color = active ? '#3f6fd9' : '#6b7280';
    if (typeof lucide !== 'undefined') lucide.createIcons();
    viewSchoolModal.show();
  }

  function confirmSchoolDelete(event, form, name) {
    event.preventDefault();
    Swal.fire({
      title: 'Delete institution?',
      text: (name ? name + ' ' : 'This institution ') + 'will be removed.',
      icon: 'warning', showCancelButton: true,
      confirmButtonColor: '#d33', cancelButtonColor: '#6c757d',
      confirmButtonText: 'Yes, delete it'
    }).then(result => { if (result.isConfirmed) form.submit(); });
  }

  // Re-open the form that failed validation (values and messages are already rendered).
  <?php if($errors->getBag('addSchool')->any()): ?>
    addSchoolModal.show();
  <?php elseif($errors->getBag('editSchool')->any()): ?>
    (function () {
      const form = document.getElementById('editSchoolForm');
      const id = document.getElementById('editSchoolId').value;
      if (id) form.action = updateSchoolUrlTemplate.replace(':id', id);
      editSchoolModal.show();
    })();
  <?php endif; ?>
</script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('style'); ?>
<style>
  .w-36px { width: 36px; }
  .h-36px { height: 36px; }
  .fs-12px { font-size: 12px; }
  .fs-13px { font-size: 13px; }
  .fs-14px { font-size: 14px; }
</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layout.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Pick-Drop\resources\views/pickdrop/schools/index.blade.php ENDPATH**/ ?>