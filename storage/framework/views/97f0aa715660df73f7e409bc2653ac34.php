<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'percent' => 0,
    'tone' => 'blue',
]));

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

foreach (array_filter(([
    'percent' => 0,
    'tone' => 'blue',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>
<?php
    $pct = max(0, min(100, (int) round((float) $percent)));
?>
<div <?php echo e($attributes->class(['pd-stat-ring', 'is-' . $tone])); ?>>
    <svg viewBox="0 0 36 36" aria-hidden="true">
        <circle class="pd-stat-ring-track" cx="18" cy="18" r="15"></circle>
        <circle class="pd-stat-ring-bar" cx="18" cy="18" r="15" pathLength="100" stroke-dasharray="<?php echo e($pct); ?> 100"></circle>
    </svg>
    <span><?php echo e($pct); ?>%</span>
</div>
<?php /**PATH D:\xampp\htdocs\Pick-Drop\resources\views/components/stat-ring.blade.php ENDPATH**/ ?>