<?php $__env->startSection('content'); ?>

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Edit Vehicle Verification</h4>
    <p class="text-secondary mb-0"><?php echo e($verification->vehicle_name); ?> · <?php echo e($verification->user?->name ?? 'Driver'); ?></p>
  </div>
  <div>
    <a href="<?php echo e(route('vehicle-verifications.show', $verification)); ?>" class="btn btn-outline-secondary">Cancel</a>
  </div>
</div>

<form method="POST" action="<?php echo e(route('vehicle-verifications.update', $verification)); ?>" enctype="multipart/form-data">
  <?php echo csrf_field(); ?>
  <?php echo method_field('PUT'); ?>

  <div class="card mb-3">
    <div class="card-header"><h6 class="mb-0">Vehicle Details</h6></div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Vehicle Name <span class="text-danger">*</span></label>
          <input type="text" name="vehicle_name" class="form-control" value="<?php echo e(old('vehicle_name', $verification->vehicle_name)); ?>" required>
          <?php $__errorArgs = ['vehicle_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Vehicle Category <span class="text-danger">*</span></label>
          <select name="vehicle_category_id" class="form-select" required>
            <option value="">Select category</option>
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($category->id); ?>" <?php if((string) old('vehicle_category_id', $verification->vehicle_category_id) === (string) $category->id): echo 'selected'; endif; ?>>
                <?php echo e($category->vehicle_name); ?>

              </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
          <?php $__errorArgs = ['vehicle_category_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Vehicle Model</label>
          <input type="text" name="vehicle_model" class="form-control" value="<?php echo e(old('vehicle_model', $verification->vehicle_model)); ?>">
          <?php $__errorArgs = ['vehicle_model'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Vehicle Color</label>
          <input type="text" name="vehicle_color" class="form-control" value="<?php echo e(old('vehicle_color', $verification->vehicle_color)); ?>">
          <?php $__errorArgs = ['vehicle_color'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">License Plate <span class="text-danger">*</span></label>
          <input type="text" name="license_plate" class="form-control" value="<?php echo e(old('license_plate', $verification->license_plate)); ?>" required>
          <?php $__errorArgs = ['license_plate'];
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
    <div class="card-header"><h6 class="mb-0">Vehicle Photos & Registration</h6></div>
    <div class="card-body">
      <div class="row g-3">
        <?php echo $__env->make('pickdrop.partials.verification-doc-input', [
          'name' => 'registration_card_front',
          'label' => 'Registration Card Front',
          'previewUrl' => $verification->registration_card_front ? route('vehicle-verifications.document', [$verification, 'registration_card_front']) : null,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('pickdrop.partials.verification-doc-input', [
          'name' => 'registration_card_back',
          'label' => 'Registration Card Back',
          'previewUrl' => $verification->registration_card_back ? route('vehicle-verifications.document', [$verification, 'registration_card_back']) : null,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('pickdrop.partials.verification-doc-input', [
          'name' => 'number_plate_photo',
          'label' => 'Number Plate Photo',
          'previewUrl' => $verification->number_plate_photo ? route('vehicle-verifications.document', [$verification, 'number_plate_photo']) : null,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('pickdrop.partials.verification-doc-input', [
          'name' => 'vehicle_front_photo',
          'label' => 'Vehicle Front Photo',
          'previewUrl' => $verification->vehicle_front_photo ? route('vehicle-verifications.document', [$verification, 'vehicle_front_photo']) : null,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('pickdrop.partials.verification-doc-input', [
          'name' => 'vehicle_back_photo',
          'label' => 'Vehicle Back Photo',
          'previewUrl' => $verification->vehicle_back_photo ? route('vehicle-verifications.document', [$verification, 'vehicle_back_photo']) : null,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header"><h6 class="mb-0">Owner</h6></div>
    <div class="card-body">
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Owner Name <span class="text-danger">*</span></label>
          <input type="text" name="owner_name" class="form-control" value="<?php echo e(old('owner_name', $verification->owner_name)); ?>" required>
          <?php $__errorArgs = ['owner_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Owner CNIC</label>
          <input type="text" name="owner_cnic_number" class="form-control" value="<?php echo e(old('owner_cnic_number', $verification->owner_cnic_number)); ?>">
          <?php $__errorArgs = ['owner_cnic_number'];
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
          'name' => 'owner_document_front',
          'label' => 'Owner Document Front',
          'previewUrl' => $verification->owner_document_front ? route('vehicle-verifications.document', [$verification, 'owner_document_front']) : null,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('pickdrop.partials.verification-doc-input', [
          'name' => 'owner_document_back',
          'label' => 'Owner Document Back',
          'previewUrl' => $verification->owner_document_back ? route('vehicle-verifications.document', [$verification, 'owner_document_back']) : null,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
      </div>
    </div>
  </div>

  <div class="d-flex justify-content-end gap-2 mb-4">
    <a href="<?php echo e(route('vehicle-verifications.show', $verification)); ?>" class="btn btn-light">Cancel</a>
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

<?php echo $__env->make('layout.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Pick-Drop\resources\views/pickdrop/vehicle-verifications/edit.blade.php ENDPATH**/ ?>