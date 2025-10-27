<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'persons' => [],
    'maxPersons' => 5,
    'showCount' => true,
    'emptyText' => 'Keine Personen zugewiesen',
    'variant' => 'default' // 'default', 'tooltip', 'detail'
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
    'persons' => [],
    'maxPersons' => 5,
    'showCount' => true,
    'emptyText' => 'Keine Personen zugewiesen',
    'variant' => 'default' // 'default', 'tooltip', 'detail'
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $personsList = is_array($persons) ? $persons : explode(', ', $persons);
    $personsList = array_filter($personsList); // Entferne leere Einträge
    $totalCount = count($personsList);
    $displayPersons = $maxPersons > 0 ? array_slice($personsList, 0, $maxPersons) : $personsList;
    $remainingCount = $totalCount - count($displayPersons);
?>

<?php if($totalCount === 0): ?>
    <span style="color: #6b7280; font-style: italic;"><?php echo e($emptyText); ?></span>
<?php else: ?>
    <div style="display: flex; flex-wrap: wrap; gap: 4px; align-items: center;">
        <?php if($variant === 'detail'): ?>
            
            <?php $__currentLoopData = $displayPersons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $person): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $initials = collect(explode(' ', $person))->map(fn($name) => substr($name, 0, 1))->implode('');
                    $colors = ['#3b82f6', '#8b5cf6', '#06b6d4', '#10b981', '#f59e0b', '#ef4444'];
                    $color = $colors[array_search($person, $displayPersons) % count($colors)];
                ?>
                <div style="display: flex; align-items: center; gap: 8px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 10px;">
                    <div style="width: 24px; height: 24px; background: <?php echo e($color); ?>; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 10px; font-weight: 600;">
                        <?php echo e($initials); ?>

                    </div>
                    <span style="font-size: 13px; font-weight: 500; color: #374151;"><?php echo e($person); ?></span>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php else: ?>
            
            <span style="color: #111827; font-weight: 600; font-size: <?php echo e($variant === 'tooltip' ? '12px' : '14px'); ?>;">
                <?php echo e(implode(', ', $displayPersons)); ?>

                <?php if($remainingCount > 0): ?>
                    <span style="color: #6b7280; font-weight: 400;">(+<?php echo e($remainingCount); ?> weitere)</span>
                <?php endif; ?>
            </span>
        <?php endif; ?>
        
        <?php if($showCount && $variant !== 'detail'): ?>
            <span style="color: #6b7280; font-size: <?php echo e($variant === 'tooltip' ? '11px' : '12px'); ?>; margin-left: 4px;">
                (<?php echo e($totalCount); ?>)
            </span>
        <?php endif; ?>
    </div>
<?php endif; ?>













<?php /**PATH C:\xampp\htdocs\mein-projekt\resources\views/components/assigned-persons.blade.php ENDPATH**/ ?>