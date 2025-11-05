<?php $__env->startSection('title', 'MOCO Integration'); ?>

<?php $__env->startSection('content'); ?>
<div style="width: 100%; margin: 0; padding: 20px;">
    <div style="background: #eff6ff; border: 1px solid #bfdbfe; color: #1d4ed8; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px;">
        <strong>Hinweis:</strong> Diese Umgebung nutzt manuelle Synchronisation. Bitte führen Sie bei Bedarf den MOCO-Sync über die Buttons oder Artisan-Kommandos aus.
    </div>
    <?php if(session('success')): ?>
        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px;">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px;">
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    <!-- Top Tabs (consistent minimal link row) -->
    <div style="margin-bottom: 16px; border-bottom: 1px solid #e5e7eb;">
        <nav style="display: flex; gap: 20px;">
            <a href="<?php echo e(route('moco.index')); ?>" style="padding: 10px 4px; border-bottom: 2px solid <?php echo e(request()->routeIs('moco.index') ? '#3b82f6' : 'transparent'); ?>; color: <?php echo e(request()->routeIs('moco.index') ? '#1d4ed8' : '#6b7280'); ?>; text-decoration: none; font-weight: 500;">Dashboard</a>
            <a href="<?php echo e(route('moco.statistics')); ?>" style="padding: 10px 4px; border-bottom: 2px solid <?php echo e(request()->routeIs('moco.statistics') ? '#3b82f6' : 'transparent'); ?>; color: <?php echo e(request()->routeIs('moco.statistics') ? '#1d4ed8' : '#6b7280'); ?>; text-decoration: none; font-weight: 500;">Statistiken</a>
            <a href="<?php echo e(route('moco.logs')); ?>" style="padding: 10px 4px; border-bottom: 2px solid <?php echo e(request()->routeIs('moco.logs') ? '#3b82f6' : 'transparent'); ?>; color: <?php echo e(request()->routeIs('moco.logs') ? '#1d4ed8' : '#6b7280'); ?>; text-decoration: none; font-weight: 500;">Sync-History</a>
            <a href="<?php echo e(route('moco.mappings')); ?>" style="padding: 10px 4px; border-bottom: 2px solid <?php echo e(request()->routeIs('moco.mappings') ? '#3b82f6' : 'transparent'); ?>; color: <?php echo e(request()->routeIs('moco.mappings') ? '#1d4ed8' : '#6b7280'); ?>; text-decoration: none; font-weight: 500;">Mappings</a>
        </nav>
    </div>

    <?php echo $__env->yieldContent('content'); ?>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Day2Day-Manager\resources\views/moco/layout.blade.php ENDPATH**/ ?>