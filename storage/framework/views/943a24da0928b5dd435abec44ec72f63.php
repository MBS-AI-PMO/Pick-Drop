<?php $__env->startSection('content'); ?>


<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">User Management</h4>
    <p class="text-secondary mb-0">Manage all system users</p>
  </div>
</div>


<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" action="<?php echo e(route('users.index')); ?>" id="filterForm">
      <div class="row g-2 align-items-center">

        
        <div class="col-12 col-md-4">
          <div class="input-group">
            <div class="input-group-text bg-transparent border-end-0">
              <i data-lucide="search" style="width:16px;height:16px;"></i>
            </div>
            <input type="text" name="search" class="form-control border-start-0 ps-0"
                   id="userSearch" placeholder="Search users..."
                   value="<?php echo e(request('search')); ?>">
          </div>
        </div>

        
        <div class="col-12 col-md-3">
          <select class="form-select" name="role" id="roleFilter" onchange="this.form.submit()">
            <option value="">All Roles</option>
            <option value="Driver" <?php echo e(request('role') === 'Driver' ? 'selected' : ''); ?>>Driver</option>
            <option value="Parent" <?php echo e(request('role') === 'Parent' ? 'selected' : ''); ?>>Parent</option>
            <option value="Self"   <?php echo e(request('role') === 'Self'   ? 'selected' : ''); ?>>Self</option>
          </select>
        </div>

        
        <div class="col-auto">
          <button type="submit" class="btn btn-outline-secondary">
            <i data-lucide="filter" style="width:15px;height:15px;" class="me-1"></i> Filter
          </button>
          <?php if(request('search') || request('role')): ?>
            <a href="<?php echo e(route('users.index')); ?>" class="btn btn-outline-danger ms-1">
              <i data-lucide="x" style="width:15px;height:15px;"></i>
            </a>
          <?php endif; ?>
        </div>

        
        <div class="col"></div>

        
        <div class="col-auto">
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i data-lucide="plus" class="icon-sm me-1"></i> Add User
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
            <th class="py-3">Name</th>
            <th class="py-3">Email</th>
            <th class="py-3">Role</th>
            <th class="py-3">Details</th>
            <th class="py-3">Status</th>
            <th class="py-3">Verified</th>
            <th class="py-3 text-center">Actions</th>
          </tr>
        </thead>
        <tbody id="usersTableBody">

          <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <tr>
            <td class="ps-4 py-3 text-muted"><?php echo e($users->firstItem() + $loop->index); ?></td>
            <td class="ps-2 py-3 fw-semibold"><?php echo e($user->name); ?></td>
            <td class="py-3 text-secondary"><?php echo e($user->email); ?></td>
            <td class="py-3">
              <?php
                  $roleClass = match(strtolower($user->role)) {
                      'admin'   => 'danger',
                      'driver'  => 'info',
                      'parent'  => 'primary',
                      default   => 'secondary'
                  };
              ?>
              <span class="badge bg-<?php echo e($roleClass); ?>-subtle text-<?php echo e($roleClass); ?>"><?php echo e($user->role); ?></span>
            </td>
            <td class="py-3 text-secondary"><?php echo e(is_array($user->details) ? json_encode($user->details) : ($user->details ?: '—')); ?></td>
            <td class="py-3">
              <?php $accountStatus = trim((string) ($user->status ?? '')); ?>
              <?php if(strtolower($accountStatus) === 'active'): ?>
                <span class="badge rounded-pill px-3 py-1" style="background:#eef4ff;color:#3f6fd9;">Active</span>
              <?php elseif($accountStatus !== ''): ?>
                <span class="badge rounded-pill px-3 py-1" style="background:#f3f4f6;color:#6b7280;"><?php echo e($accountStatus); ?></span>
              <?php else: ?>
                <span class="badge rounded-pill px-3 py-1" style="background:#f3f4f6;color:#6b7280;">Pending</span>
              <?php endif; ?>
            </td>
            <td class="py-3">
              <?php
                $roleKey = strtolower((string) $user->role);
                $showsVerification = in_array($roleKey, ['driver', 'parent', 'self'], true);
                $kycStatus = $user->kycStatus();
                $verifiedLabel = $user->verificationBadgeLabel();
              ?>
              <?php if($showsVerification): ?>
                <?php if($kycStatus === 'approved'): ?>
                  <span class="badge rounded-pill px-3 py-1" style="background:#e8f7ee;color:#157347;"><?php echo e($verifiedLabel); ?></span>
                <?php elseif($kycStatus === 'pending'): ?>
                  <span class="badge rounded-pill px-3 py-1" style="background:#fff7e6;color:#b54708;"><?php echo e($verifiedLabel); ?></span>
                <?php elseif($kycStatus === 'rejected'): ?>
                  <span class="badge rounded-pill px-3 py-1" style="background:#fdecec;color:#b42318;"><?php echo e($verifiedLabel); ?></span>
                <?php else: ?>
                  <span class="badge rounded-pill px-3 py-1" style="background:#fdecec;color:#b42318;"><?php echo e($verifiedLabel); ?></span>
                <?php endif; ?>
              <?php else: ?>
                <span class="text-muted">—</span>
              <?php endif; ?>
            </td>
            <td class="py-3 text-center">
              <?php
                $userPayload = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->status,
                    'details' => is_array($user->details) ? null : $user->details,
                ];
              ?>
              <div class="action-btns">
                <button type="button" class="action-btn action-btn-view" title="View"
                        onclick="openViewModal(event, <?php echo e(json_encode($userPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE)); ?>, <?php echo e(json_encode($verifiedLabel, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE)); ?>)">
                  <i data-lucide="eye"></i>
                </button>
                <button type="button" class="action-btn action-btn-edit" title="Edit"
                        onclick="openEditModal(event, <?php echo e(json_encode($userPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE)); ?>)">
                  <i data-lucide="edit-2"></i>
                </button>
                <form action="<?php echo e(route('users.destroy', $user->id)); ?>" method="POST"
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
            <td colspan="8" class="text-center py-5">
              <div class="mb-3 text-muted">
                <i data-lucide="users" style="width:48px;height:48px;opacity:0.4;"></i>
              </div>
              <h6 class="text-secondary">No Users Found</h6>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app-pagination','data' => ['paginator' => $users,'label' => 'users']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($users),'label' => 'users']); ?>
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



<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="addUserModalLabel">Add New User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-2">
        <form action="<?php echo e(route('users.store')); ?>" method="POST">
          <?php echo csrf_field(); ?>
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" placeholder="Enter full name" required>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
              <input type="email" name="email" class="form-control" placeholder="Enter email address" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
              <select name="role" class="form-select" required>
                <option value="">Select role</option>
                <option value="Driver">Driver</option>
                <option value="Parent">Parent</option>
                <option value="Self">Self</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
              <select name="status" class="form-select" required>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
                <option value="Pending">Pending</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Details</label>
              <input type="text" name="details" class="form-control" placeholder="e.g. Child: Emma / Vehicle: Bus #45">
            </div>
            <div class="col-12">
              <label for="addUserPassword" class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
              <div class="password-field">
                <input
                  type="password"
                  name="password"
                  id="addUserPassword"
                  class="form-control"
                  placeholder="Enter password"
                  required
                  autocomplete="new-password"
                >
                <button class="password-toggle" type="button" id="toggleAddUserPassword" aria-label="Show password">
                  <svg class="icon-eye" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg>
                  <svg class="icon-eye-off" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/><path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/><path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-4.97"/><path d="m2 2 20 20"/></svg>
                </button>
              </div>
            </div>
          </div>
          <div class="modal-footer border-0 pt-4 pb-0 px-0">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary px-4">Add User</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="editUserModalLabel">Edit User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-2">
        <form id="editUserForm" method="POST">
          <?php echo csrf_field(); ?>
          <?php echo method_field('PUT'); ?>
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
              <input type="text" name="name" id="editUserName" class="form-control" required>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
              <input type="email" name="email" id="editUserEmail" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
              <select name="role" id="editUserRole" class="form-select" required>
                <option value="Driver">Driver</option>
                <option value="Parent">Parent</option>
                <option value="Self">Self</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
              <select name="status" id="editUserStatus" class="form-select" required>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
                <option value="Pending">Pending</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Details</label>
              <input type="text" name="details" id="editUserDetails" class="form-control">
            </div>
            <div class="col-12">
              <label for="editUserPassword" class="form-label fw-semibold">Password
                <small class="text-muted fw-normal">(Leave blank to keep current)</small>
              </label>
              <div class="password-field">
                <input
                  type="password"
                  name="password"
                  id="editUserPassword"
                  class="form-control"
                  placeholder="Update password (optional)"
                  autocomplete="new-password"
                >
                <button class="password-toggle" type="button" id="toggleEditUserPassword" aria-label="Show password">
                  <svg class="icon-eye" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg>
                  <svg class="icon-eye-off" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/><path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/><path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-4.97"/><path d="m2 2 20 20"/></svg>
                </button>
              </div>
            </div>
          </div>
          <div class="modal-footer border-0 pt-4 pb-0 px-0">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary px-4">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="viewUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold">User Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3 view-user-grid">
          <div class="col-12">
            <label class="form-label mb-1">Full Name</label>
            <div class="fw-semibold text-break" id="viewUserName">—</div>
          </div>
          <div class="col-12">
            <label class="form-label mb-1">Email Address</label>
            <div class="fw-semibold text-break" id="viewUserEmail">—</div>
          </div>
          <div class="col-sm-6">
            <label class="form-label mb-1">Role</label>
            <div class="fw-semibold text-break" id="viewUserRole">—</div>
          </div>
          <div class="col-sm-6">
            <label class="form-label mb-1">Status</label>
            <div class="fw-semibold text-break" id="viewUserStatus">—</div>
          </div>
          <div class="col-12">
            <label class="form-label mb-1">Verified</label>
            <div class="fw-semibold text-break" id="viewUserVerified">—</div>
          </div>
          <div class="col-12">
            <label class="form-label mb-1">Details</label>
            <div class="fw-semibold text-break" id="viewUserDetails">—</div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
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

  <?php if($errors->any()): ?>
  Swal.fire({
      icon: 'error', title: 'Validation Error',
      html: `<?php echo implode('<br>', $errors->all()); ?>`,
      toast: true, position: 'top-end', showConfirmButton: false,
      timer: 5000, timerProgressBar: true
  });
  <?php endif; ?>

  // Submit search on Enter key
  document.getElementById('userSearch').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
      document.getElementById('filterForm').submit();
    }
  });

  // Add / Edit User: show / hide password (inline SVG eye / eye-off)
  function bindPasswordToggle(passwordId, toggleId, modalId) {
    const passwordField = document.getElementById(passwordId);
    const togglePassword = document.getElementById(toggleId);
    const modal = modalId ? document.getElementById(modalId) : null;

    if (!passwordField || !togglePassword) {
      return;
    }

    function setVisible(isVisible) {
      passwordField.setAttribute('type', isVisible ? 'text' : 'password');
      togglePassword.classList.toggle('is-visible', isVisible);
      togglePassword.setAttribute('aria-label', isVisible ? 'Hide password' : 'Show password');
    }

    togglePassword.addEventListener('click', function () {
      setVisible(passwordField.getAttribute('type') === 'password');
    });

    if (modal) {
      modal.addEventListener('hidden.bs.modal', function () {
        passwordField.value = '';
        setVisible(false);
      });
    }
  }

  bindPasswordToggle('addUserPassword', 'toggleAddUserPassword', 'addUserModal');
  bindPasswordToggle('editUserPassword', 'toggleEditUserPassword', 'editUserModal');

  // Edit Modal
  const editModal = new bootstrap.Modal(document.getElementById('editUserModal'));
  const viewUserModal = new bootstrap.Modal(document.getElementById('viewUserModal'));

  function openViewModal(event, user, verifiedLabel) {
    event.preventDefault();
    document.getElementById('viewUserName').textContent = user.name || '—';
    document.getElementById('viewUserEmail').textContent = user.email || '—';
    document.getElementById('viewUserRole').textContent = user.role || '—';
    document.getElementById('viewUserStatus').textContent = user.status || 'Pending';
    document.getElementById('viewUserVerified').textContent = verifiedLabel || '—';
    document.getElementById('viewUserDetails').textContent = (typeof user.details === 'object' && user.details)
      ? JSON.stringify(user.details)
      : (user.details || '—');
    viewUserModal.show();
  }

  const userUpdateBase = <?php echo json_encode(rtrim(url('/users'), '/'), 512) ?>;

  function selectSavedValue(select, value) {
    const wanted = (value || '').toString().trim().toLowerCase();
    const match = Array.from(select.options).find(function (option) {
      return option.value.toLowerCase() === wanted;
    });
    select.value = match ? match.value : '';
  }

  function openEditModal(event, user) {
    event.preventDefault();
    document.getElementById('editUserName').value    = user.name    || '';
    document.getElementById('editUserEmail').value   = user.email   || '';
    selectSavedValue(document.getElementById('editUserRole'), user.role || 'Parent');
    selectSavedValue(document.getElementById('editUserStatus'), user.status || 'Active');
    document.getElementById('editUserDetails').value = (typeof user.details === 'object' && user.details)
      ? ''
      : (user.details || '');
    const editPassword = document.getElementById('editUserPassword');
    const editToggle = document.getElementById('toggleEditUserPassword');
    if (editPassword) {
      editPassword.value = '';
      editPassword.setAttribute('type', 'password');
    }
    if (editToggle) {
      editToggle.classList.remove('is-visible');
      editToggle.setAttribute('aria-label', 'Show password');
    }
    document.getElementById('editUserForm').action   = userUpdateBase + '/' + user.id;
    editModal.show();
  }

  // Delete Confirmation
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

<?php echo $__env->make('layout.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\Pick-Drop\resources\views/pickdrop/users/index.blade.php ENDPATH**/ ?>