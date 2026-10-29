<?php $__env->startSection('content'); ?>

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Edit Driver Verification</h4>
    <p class="text-secondary mb-0"><?php echo e($verification->full_name); ?> · <?php echo e($verification->user?->email); ?></p>
  </div>
  <div class="d-flex gap-2">
    <a href="<?php echo e(route('driver-verifications.show', $verification)); ?>" class="btn btn-outline-secondary">Cancel</a>
  </div>
</div>

<form method="POST" action="<?php echo e(route('driver-verifications.update', $verification)); ?>" enctype="multipart/form-data">
  <?php echo csrf_field(); ?>
  <?php echo method_field('PUT'); ?>

  <div class="card mb-3">
    <div class="card-header"><h6 class="mb-0">Personal Details</h6></div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
          <input type="text" name="full_name" class="form-control" value="<?php echo e(old('full_name', $verification->full_name)); ?>" required>
          <?php $__errorArgs = ['full_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Father Name</label>
          <input type="text" name="father_name" class="form-control" value="<?php echo e(old('father_name', $verification->father_name)); ?>">
          <?php $__errorArgs = ['father_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Date of Birth <span class="text-danger">*</span></label>
          <input type="date" name="date_of_birth" class="form-control"
                 value="<?php echo e(old('date_of_birth', optional($verification->date_of_birth)->format('Y-m-d'))); ?>" required>
          <?php $__errorArgs = ['date_of_birth'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">City <span class="text-danger">*</span></label>
          <select name="city_id" class="form-select" required>
            <option value="">Select city</option>
            <?php $__currentLoopData = $cities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $city): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($city->id); ?>" <?php if((string) old('city_id', $verification->city_id) === (string) $city->id): echo 'selected'; endif; ?>>
                <?php echo e($city->name); ?>

              </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
          <?php $__errorArgs = ['city_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Address <span class="text-danger">*</span></label>
          <textarea name="address" class="form-control" rows="2" required><?php echo e(old('address', $verification->address)); ?></textarea>
          <?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header"><h6 class="mb-0">Identity Documents</h6></div>
    <div class="card-body">
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold">CNIC Number <span class="text-danger">*</span></label>
          <input type="text" name="cnic_number" class="form-control" value="<?php echo e(old('cnic_number', $verification->cnic_number)); ?>" required>
          <?php $__errorArgs = ['cnic_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
      </div>
      <div class="row g-3">
        <?php echo $__env->make('pickdrop.partials.verification-doc-input', [
          'name' => 'cnic_front',
          'label' => 'CNIC Front',
          'previewUrl' => route('driver-verifications.document', [$verification, 'cnic_front']),
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('pickdrop.partials.verification-doc-input', [
          'name' => 'cnic_back',
          'label' => 'CNIC Back',
          'previewUrl' => route('driver-verifications.document', [$verification, 'cnic_back']),
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('pickdrop.partials.verification-doc-input', [
          'name' => 'selfie_photo',
          'label' => 'Selfie / Face Photo',
          'previewUrl' => route('driver-verifications.document', [$verification, 'selfie_photo']),
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header"><h6 class="mb-0">Driving License</h6></div>
    <div class="card-body">
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold">License Number <span class="text-danger">*</span></label>
          <input type="text" name="license_number" class="form-control" value="<?php echo e(old('license_number', $verification->license_number)); ?>" required>
          <?php $__errorArgs = ['license_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">License Expiry <span class="text-danger">*</span></label>
          <input type="date" name="license_expiry" class="form-control"
                 value="<?php echo e(old('license_expiry', optional($verification->license_expiry)->format('Y-m-d'))); ?>" required>
          <?php $__errorArgs = ['license_expiry'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
      </div>
      <div class="row g-3">
        <?php echo $__env->make('pickdrop.partials.verification-doc-input', [
          'name' => 'license_front',
          'label' => 'License Front',
          'previewUrl' => route('driver-verifications.document', [$verification, 'license_front']),
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('pickdrop.partials.verification-doc-input', [
          'name' => 'license_back',
          'label' => 'License Back',
          'previewUrl' => route('driver-verifications.document', [$verification, 'license_back']),
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
      </div>
    </div>
  </div>

  <div class="d-flex justify-content-end gap-2 mb-4">
    <a href="<?php echo e(route('driver-verifications.show', $verification)); ?>" class="btn btn-light">Cancel</a>
    <button type="submit" class="btn btn-primary px-4">
      <i data-lucide="save" class="icon-xs"></i>
      Save Changes
    </button>
  </div>
</form>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('custom-scripts'); ?>
<script>
  <?php if(session('error')): ?>
  Swal.fire({
    icon: 'error',
    title: 'Error',
    text: <?php echo json_encode(session('error'), 15, 512) ?>,
    confirmButtonText: 'OK',
    confirmButtonColor: '#e63946',
    customClass: { popup: 'rounded-4' }
  });
  <?php endif; ?>

</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layout.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Pick-Drop\resources\views/pickdrop/driver-verifications/edit.blade.php ENDPATH**/ ?>