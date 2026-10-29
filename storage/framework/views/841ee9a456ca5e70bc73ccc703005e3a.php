<?php
  $point = $point ?? null;
?>
<label class="form-label">City</label>
<select name="city_id" class="form-select mb-3" required>
  <option value="">Select city</option>
  <?php $__currentLoopData = $cities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $city): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <option value="<?php echo e($city->id); ?>" <?php if((int) old('city_id', $point?->city_id) === (int) $city->id): echo 'selected'; endif; ?>><?php echo e($city->name); ?></option>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>

<label class="form-label">Area (optional)</label>
<select name="area_id" class="form-select mb-3">
  <option value="">Any area</option>
  <?php $__currentLoopData = $areas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $area): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <option value="<?php echo e($area->id); ?>"
            data-city="<?php echo e($area->city_id); ?>"
            <?php if((int) old('area_id', $point?->area_id) === (int) $area->id): echo 'selected'; endif; ?>>
      <?php echo e($area->name); ?>

    </option>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>

<label class="form-label">Point name</label>
<input type="text" name="name" class="form-control mb-3" value="<?php echo e(old('name', $point?->name)); ?>" required placeholder="e.g. G-10 Markaz Gate 2">

<label class="form-label">Type</label>
<select name="type" class="form-select mb-3" required>
  <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <option value="<?php echo e($value); ?>" <?php if(old('type', $point?->type ?? 'both') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>

<label class="form-label">Address</label>
<input type="text" name="address" class="form-control mb-3" value="<?php echo e(old('address', $point?->address)); ?>">

<div class="row g-2 mb-3">
  <div class="col-6">
    <label class="form-label">Latitude</label>
    <input type="number" step="any" name="latitude" class="form-control" value="<?php echo e(old('latitude', $point?->latitude)); ?>">
  </div>
  <div class="col-6">
    <label class="form-label">Longitude</label>
    <input type="number" step="any" name="longitude" class="form-control" value="<?php echo e(old('longitude', $point?->longitude)); ?>">
  </div>
</div>

<label class="form-label">Status</label>
<select name="status" class="form-select">
  <option value="Active" <?php if(old('status', $point?->status ?? 'Active') === 'Active'): echo 'selected'; endif; ?>>Active</option>
  <option value="Inactive" <?php if(old('status', $point?->status) === 'Inactive'): echo 'selected'; endif; ?>>Inactive</option>
</select>
<?php /**PATH C:\xampp\htdocs\Pick-Drop\resources\views/pickdrop/locations/partials/point-form.blade.php ENDPATH**/ ?>