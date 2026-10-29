<?php $__env->startPush('style'); ?>
<style>
  .profile-card {
    height: 100%;
  }
  .profile-card .card-body {
    display: flex;
    flex-direction: column;
    height: 100%;
    padding: 20px;
  }
  .profile-card .btn-primary {
    margin-top: auto;
    align-self: flex-start;
  }
  .profile-meta {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-top: 16px;
  }
  .profile-meta-item,
  .profile-tip-item {
    border: 1px solid #edf1f7;
    border-radius: 8px;
    background: #fbfcfe;
    padding: 12px;
  }
  .profile-meta-item span,
  .profile-tip-item span {
    display: block;
    color: #728096;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    margin-bottom: 4px;
  }
  .profile-meta-item strong,
  .profile-tip-item p {
    display: block;
    color: #172033;
    font-size: 13px;
    font-weight: 700;
    margin: 0;
  }
  .profile-tip-item p {
    font-weight: 600;
    color: #64748b;
    line-height: 1.45;
  }
  .profile-admin-row {
    min-height: 52px;
  }
  [data-bs-theme="dark"] .profile-meta-item,
  [data-bs-theme="dark"] .profile-tip-item {
    background: #171a21;
    border-color: rgba(255, 255, 255, 0.08);
  }
  [data-bs-theme="dark"] .profile-meta-item strong {
    color: #f4f7fb;
  }
  [data-bs-theme="dark"] .profile-meta-item span,
  [data-bs-theme="dark"] .profile-tip-item span,
  [data-bs-theme="dark"] .profile-tip-item p {
    color: #9aa7bb;
  }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">My Profile</h4>
    <p class="text-secondary mb-0">Update your account details and password.</p>
  </div>
</div>

<div class="row g-3 align-items-stretch">
  <div class="col-xl-6">
    <div class="card profile-card">
      <div class="card-body">
        <div class="d-flex align-items-center gap-3 mb-3">
          <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width:56px;height:56px;font-size:22px;">
            <?php echo e(strtoupper(substr($user->name, 0, 1))); ?>

          </div>
          <div>
            <h6 class="mb-1"><?php echo e($user->name); ?></h6>
            <p class="text-secondary mb-0"><?php echo e($user->email); ?></p>
          </div>
        </div>

        <div class="d-flex flex-wrap gap-2 mb-3">
          <span class="badge <?php echo e($canManageAdmins ? 'bg-danger bg-opacity-10 text-danger' : 'bg-primary bg-opacity-10 text-primary'); ?>">
            <?php echo e($user->role ?: 'Admin'); ?>

          </span>
          <span class="badge <?php echo e(($user->status ?? 'Active') === 'Active' ? 'rounded-pill px-3 py-1' : 'bg-secondary bg-opacity-10 text-secondary'); ?>" <?php if(($user->status ?? 'Active') === 'Active'): ?> style="background:#eef4ff;color:#3f6fd9;" <?php endif; ?>>
            <?php echo e($user->status ?: 'Active'); ?>

          </span>
        </div>

        <form action="<?php echo e(route('profile.update')); ?>" method="POST" class="d-flex flex-column flex-grow-1">
          <?php echo csrf_field(); ?>
          <?php echo method_field('PUT'); ?>

          <div class="mb-3">
            <label class="form-label">Full name <span class="text-danger">*</span></label>
            <input
              type="text"
              name="name"
              class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
              value="<?php echo e(old('name', $user->name)); ?>"
              required>
            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
              <div class="invalid-feedback"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          </div>

          <div class="mb-3">
            <label class="form-label">Email <span class="text-danger">*</span></label>
            <input
              type="email"
              name="email"
              class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
              value="<?php echo e(old('email', $user->email)); ?>"
              required>
            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
              <div class="invalid-feedback"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          </div>

          <div class="mb-3">
            <label class="form-label">Phone</label>
            <input
              type="text"
              name="phone"
              class="form-control <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
              value="<?php echo e(old('phone', $user->phone)); ?>"
              placeholder="Optional">
            <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
              <div class="invalid-feedback"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          </div>

          <div class="profile-meta mb-3">
            <div class="profile-meta-item">
              <span>Role</span>
              <strong><?php echo e($user->role ?: 'Admin'); ?></strong>
            </div>
            <div class="profile-meta-item">
              <span>Joined</span>
              <strong><?php echo e($user->created_at ? $user->created_at->format('M d, Y') : '—'); ?></strong>
            </div>
          </div>

          <button type="submit" class="btn btn-primary">Save Profile</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-xl-6">
    <div class="card profile-card">
      <div class="card-body">
        <h6 class="card-title mb-1">Change password</h6>
        <p class="text-secondary mb-3">Use a strong password of at least 8 characters.</p>

        <form action="<?php echo e(route('profile.password')); ?>" method="POST" class="d-flex flex-column flex-grow-1">
          <?php echo csrf_field(); ?>
          <?php echo method_field('PUT'); ?>

          <div class="mb-3">
            <label class="form-label">Current password <span class="text-danger">*</span></label>
            <input
              type="password"
              name="current_password"
              class="form-control <?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
              required>
            <?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
              <div class="invalid-feedback"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          </div>

          <div class="mb-3">
            <label class="form-label">New password <span class="text-danger">*</span></label>
            <input
              type="password"
              name="password"
              class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
              required>
            <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
              <div class="invalid-feedback"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          </div>

          <div class="mb-3">
            <label class="form-label">Confirm new password <span class="text-danger">*</span></label>
            <input
              type="password"
              name="password_confirmation"
              class="form-control"
              required>
          </div>

          <div class="profile-meta mb-3">
            <div class="profile-tip-item">
              <span>Security tip</span>
              <p>Do not share this password. Other admins cannot change Super Admin access.</p>
            </div>
            <div class="profile-tip-item">
              <span>Login access</span>
              <p>Only Super Admin and Admin accounts can sign in to this panel.</p>
            </div>
          </div>

          <button type="submit" class="btn btn-primary">Update Password</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php if(!empty($canManageAdmins)): ?>
<div class="row g-3 align-items-stretch mt-1">
  <div class="col-xl-6">
    <div class="card profile-card">
      <div class="card-body">
        <h6 class="card-title mb-1">Create admin</h6>
        <p class="text-secondary mb-3">They can log in and use the panel. They cannot create, edit, or delete admins.</p>

        <form action="<?php echo e(route('profile.admins.store')); ?>" method="POST" class="d-flex flex-column flex-grow-1">
          <?php echo csrf_field(); ?>

          <div class="mb-3">
            <label class="form-label">Full name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control <?php $__errorArgs = ['name', 'createAdmin'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('name')); ?>" required>
            <?php $__errorArgs = ['name', 'createAdmin'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
              <div class="invalid-feedback"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          </div>

          <div class="mb-3">
            <label class="form-label">Email <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control <?php $__errorArgs = ['email', 'createAdmin'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('email')); ?>" required>
            <?php $__errorArgs = ['email', 'createAdmin'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
              <div class="invalid-feedback"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          </div>

          <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control <?php $__errorArgs = ['phone', 'createAdmin'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('phone')); ?>" placeholder="Optional">
            <?php $__errorArgs = ['phone', 'createAdmin'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
              <div class="invalid-feedback"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          </div>

          <div class="mb-3">
            <label class="form-label">Password <span class="text-danger">*</span></label>
            <input type="password" name="password" class="form-control <?php $__errorArgs = ['password', 'createAdmin'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
            <?php $__errorArgs = ['password', 'createAdmin'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
              <div class="invalid-feedback"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          </div>

          <div class="mb-3">
            <label class="form-label">Confirm password <span class="text-danger">*</span></label>
            <input type="password" name="password_confirmation" class="form-control" required>
          </div>

          <button type="submit" class="btn btn-primary">Create Admin</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-xl-6">
    <div class="card profile-card">
      <div class="card-body">
        <h6 class="card-title mb-1">Panel admins</h6>
        <p class="text-secondary mb-3">Admins can use the panel. Only you can create, edit, or remove them.</p>

        <div class="d-flex flex-column gap-2 flex-grow-1">
          <?php $__empty_1 = true; $__currentLoopData = $admins; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $admin): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="profile-meta-item profile-admin-row d-flex align-items-center justify-content-between gap-3">
              <div class="min-w-0">
                <strong class="d-block text-truncate"><?php echo e($admin->name); ?></strong>
                <span class="text-truncate d-block" style="text-transform:none;letter-spacing:0;font-weight:600;"><?php echo e($admin->email); ?></span>
              </div>
              <div class="d-flex align-items-center gap-2 flex-shrink-0">
                <span class="badge <?php echo e($admin->isSuperAdmin() ? 'bg-danger bg-opacity-10 text-danger' : 'bg-primary bg-opacity-10 text-primary'); ?>">
                  <?php echo e($admin->role); ?>

                </span>
                <?php if(! $admin->isSuperAdmin()): ?>
                  <button type="button" class="btn btn-sm btn-outline-primary" onclick='openEditAdminModal(<?php echo json_encode($admin, 15, 512) ?>)'>Edit</button>
                  <form action="<?php echo e(route('profile.admins.destroy', $admin)); ?>" method="POST" onsubmit="return confirm('Remove this admin?');">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                  </form>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="profile-tip-item">
              <span>No admins</span>
              <p>Create an admin to give panel access.</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="editAdminModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold">Edit admin</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-2">
        <form id="editAdminForm" method="POST">
          <?php echo csrf_field(); ?>
          <?php echo method_field('PUT'); ?>
          <div class="mb-3">
            <label class="form-label">Full name <span class="text-danger">*</span></label>
            <input type="text" name="name" id="editAdminName" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Email <span class="text-danger">*</span></label>
            <input type="email" name="email" id="editAdminEmail" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" id="editAdminPhone" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" id="editAdminStatus" class="form-select">
              <option value="Active">Active</option>
              <option value="Inactive">Inactive</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Password <small class="text-muted">(leave blank to keep current)</small></label>
            <input type="password" name="password" class="form-control">
          </div>
          <div class="modal-footer border-0 px-0 pb-0">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Admin</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<?php $__env->startPush('custom-scripts'); ?>
<?php if(!empty($canManageAdmins)): ?>
<script>
  function openEditAdminModal(admin) {
    const form = document.getElementById('editAdminForm');
    form.action = `/profile/admins/${admin.id}`;
    document.getElementById('editAdminName').value = admin.name || '';
    document.getElementById('editAdminEmail').value = admin.email || '';
    document.getElementById('editAdminPhone').value = admin.phone || '';
    document.getElementById('editAdminStatus').value = admin.status || 'Active';
    new bootstrap.Modal(document.getElementById('editAdminModal')).show();
  }
</script>
<?php endif; ?>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Pick-Drop\resources\views/pickdrop/profile/index.blade.php ENDPATH**/ ?>