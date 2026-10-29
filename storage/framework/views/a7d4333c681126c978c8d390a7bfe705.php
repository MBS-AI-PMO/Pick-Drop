<?php
    $name = $name ?? '';
    $label = $label ?? '';
    $previewUrl = $previewUrl ?? null;
?>

<div class="col-md-4">
    <label class="form-label fw-semibold"><?php echo e($label); ?></label>
    <?php if($previewUrl): ?>
        <div class="mb-2">
            <a href="<?php echo e($previewUrl); ?>" target="_blank">
                <img src="<?php echo e($previewUrl); ?>" alt="<?php echo e($label); ?>"
                     class="img-fluid rounded border"
                     style="max-height:120px;object-fit:cover;width:100%;">
            </a>
        </div>
    <?php endif; ?>
    <input type="file" name="<?php echo e($name); ?>" class="form-control" accept="image/*">
    <small class="text-muted">Leave empty to keep current file.</small>
    <?php $__errorArgs = [$name];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <div class="text-danger small mt-1"><?php echo e($message); ?></div>
    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
</div>
<?php /**PATH C:\xampp\htdocs\Pick-Drop\resources\views/pickdrop/partials/verification-doc-input.blade.php ENDPATH**/ ?>