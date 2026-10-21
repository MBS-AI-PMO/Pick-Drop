<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['paginator', 'label' => 'items']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['paginator', 'label' => 'items']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php if(isset($paginator) && method_exists($paginator, 'total') && $paginator->total() > 0): ?>
  <div <?php echo e($attributes->merge(['class' => 'card-footer bg-transparent app-pagination-footer'])); ?>>
    <small class="app-pagination-summary">
      Showing <?php echo e($paginator->firstItem()); ?> to <?php echo e($paginator->lastItem()); ?> of <?php echo e($paginator->total()); ?> <?php echo e($label); ?>

    </small>
    <div class="app-pagination-controls">
      <?php if($paginator->hasPages()): ?>
        <?php echo e($paginator->links('pagination::bootstrap-5')); ?>

      <?php else: ?>
        <nav class="app-pagination-nav" role="navigation" aria-label="Pagination">
          <ul class="pagination mb-0">
            <li class="page-item disabled" aria-disabled="true" aria-label="Previous">
              <span class="page-link" aria-hidden="true">&lsaquo;</span>
            </li>
            <li class="page-item active" aria-current="page">
              <span class="page-link">1</span>
            </li>
            <li class="page-item disabled" aria-disabled="true" aria-label="Next">
              <span class="page-link" aria-hidden="true">&rsaquo;</span>
            </li>
          </ul>
        </nav>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\Pick-Drop\resources\views/components/app-pagination.blade.php ENDPATH**/ ?>