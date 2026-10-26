
<?php
    $formErrors = isset($bag) && $bag ? $errors->getBag($bag) : $errors->getBag('default');
    $invalidLabels = collect($formErrors->keys())
        ->map(fn ($key) => $labels[$key] ?? \Illuminate\Support\Str::headline($key))
        ->unique()
        ->values();
?>
<script>
  <?php if($formErrors->any()): ?>
  Swal.fire({
    icon: 'error',
    title: <?php echo json_encode($invalidLabels->count() === 1 ? 'Fix 1 field' : 'Fix ' . $invalidLabels->count() . ' fields'); ?>,
    text: <?php echo json_encode($invalidLabels->implode(', ')); ?>,
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 5000,
    timerProgressBar: true,
    customClass: { popup: 'pd-toast' }
  });
  <?php endif; ?>

  (function () {
    const form = document.getElementById(<?php echo json_encode($formId, 15, 512) ?>);
    if (!form) return;
    const firstInvalid = form.querySelector('.is-invalid');
    if (firstInvalid) {
      firstInvalid.scrollIntoView({ block: 'center', behavior: 'smooth' });
      firstInvalid.focus({ preventScroll: true });
    }
    form.querySelectorAll('.is-invalid').forEach(function (field) {
      const clear = function () {
        field.classList.remove('is-invalid');
        const msg = field.parentElement.querySelector('.invalid-feedback');
        if (msg) msg.remove();
      };
      field.addEventListener('input', clear, { once: true });
      field.addEventListener('change', clear, { once: true });
    });
  })();
</script>
<?php /**PATH D:\xampp\htdocs\Pick-Drop\resources\views/pickdrop/partials/form-validation.blade.php ENDPATH**/ ?>