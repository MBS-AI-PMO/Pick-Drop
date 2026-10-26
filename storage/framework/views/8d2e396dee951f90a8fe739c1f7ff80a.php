<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1"><?php echo e($school->name); ?></h4>
    <p class="text-secondary mb-0"><?php echo e($school->categoryLabel()); ?> · <?php echo e($school->city?->name ?: 'No city'); ?> · <?php echo e($school->students->count()); ?> students</p>
  </div>
  <div class="d-flex gap-2">
    <a href="<?php echo e(route('schools.index')); ?>" class="btn btn-outline-secondary">Back</a>
    <form action="<?php echo e(route('schools.destroy', $school)); ?>" method="POST" onsubmit="confirmDelete(event, this)">
      <?php echo csrf_field(); ?>
      <?php echo method_field('DELETE'); ?>
      <button type="submit" class="btn btn-outline-danger">Delete</button>
    </form>
  </div>
</div>
<div class="row g-3">
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header"><h6 class="mb-0">Edit</h6></div>
      <div class="card-body">
        <form method="POST" action="<?php echo e(route('schools.update', $school)); ?>" id="schoolEditForm" novalidate>
          <?php echo csrf_field(); ?>
          <?php echo method_field('PUT'); ?>
          <div class="mb-3">
            <label class="form-label">Name <span class="text-danger">*</span></label>
            <input class="form-control <?php $__errorArgs = ['name', 'editSchool'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="name" value="<?php echo e(old('name', $school->name)); ?>" maxlength="255" required>
            <?php $__errorArgs = ['name', 'editSchool'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          </div>
          <div class="mb-3">
            <label class="form-label">Category <span class="text-danger">*</span></label>
            <select class="form-select <?php $__errorArgs = ['category', 'editSchool'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="category" required>
              <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($value); ?>" <?php echo e(old('category', $school->category) === $value ? 'selected' : ''); ?>><?php echo e($label); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <?php $__errorArgs = ['category', 'editSchool'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          </div>
          <div class="mb-3">
            <label class="form-label">Phone</label>
            <input class="form-control <?php $__errorArgs = ['phone', 'editSchool'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" type="tel" inputmode="tel" name="phone" value="<?php echo e(old('phone', $school->phone)); ?>" maxlength="50" placeholder="e.g. +92 300 1234567" autocomplete="off">
            <?php $__errorArgs = ['phone', 'editSchool'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input class="form-control <?php $__errorArgs = ['email', 'editSchool'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" type="email" inputmode="email" name="email" value="<?php echo e(old('email', $school->email)); ?>" maxlength="255" placeholder="name@example.com" autocomplete="off">
            <?php $__errorArgs = ['email', 'editSchool'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          </div>
          <div class="mb-3">
            <label class="form-label">Address</label>
            <input class="form-control <?php $__errorArgs = ['address', 'editSchool'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="address" value="<?php echo e(old('address', $school->address)); ?>" maxlength="255">
            <?php $__errorArgs = ['address', 'editSchool'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          </div>
          <div class="mb-3">
            <label class="form-label">Status</label>
            <select class="form-select <?php $__errorArgs = ['status', 'editSchool'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="status">
              <option value="Active" <?php echo e(old('status', $school->status) === 'Active' ? 'selected' : ''); ?>>Active</option>
              <option value="Inactive" <?php echo e(old('status', $school->status) === 'Inactive' ? 'selected' : ''); ?>>Inactive</option>
            </select>
            <?php $__errorArgs = ['status', 'editSchool'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          </div>
          <button class="btn btn-dark w-100" type="submit">Update</button>
        </form>
      </div>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header"><h6 class="mb-0">Students</h6></div>
      <div class="card-body p-0">
        <table class="table mb-0">
          <thead class="table-light"><tr><th class="ps-4">Child</th><th>Parent</th><th>Grade</th></tr></thead>
          <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $school->students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
              <tr>
                <td class="ps-4"><?php echo e($student->name); ?></td>
                <td><?php echo e($student->parent?->name); ?></td>
                <td><?php echo e($student->grade ?: '—'); ?></td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <tr><td colspan="3" class="text-center py-4 text-muted">No students linked.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('custom-scripts'); ?>
  <?php echo $__env->make('pickdrop.partials.form-validation', [
    'formId' => 'schoolEditForm',
    'bag' => 'editSchool',
    'labels' => ['name' => 'Name', 'category' => 'Category', 'city_id' => 'City', 'address' => 'Address', 'phone' => 'Phone', 'email' => 'Email', 'status' => 'Status'],
  ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layout.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\Pick-Drop\resources\views/pickdrop/schools/show.blade.php ENDPATH**/ ?>