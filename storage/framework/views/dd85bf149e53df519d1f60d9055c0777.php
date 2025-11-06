<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div style="max-width: 1400px; margin: 0 auto; padding: 24px;">
        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h1 style="font-size: 28px; font-weight: 600; color: #111827; margin: 0;">Benutzerverwaltung</h1>
            <a href="<?php echo e(route('users.create')); ?>" 
               style="display: inline-flex; align-items: center; gap: 8px; background: #111827; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 500;">
                <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Neuer Benutzer
            </a>
        </div>

        <!-- DEBUG INFO -->
        <div style="padding: 20px; background: #fef3c7; border: 2px solid #f59e0b; border-radius: 8px; margin-bottom: 20px;">
            <strong style="color: #92400e;">DEBUG:</strong><br>
            <div style="color: #78350f; margin-top: 8px;">
                Users Count: <?php echo e($users->count()); ?><br>
                Roles Count: <?php echo e($roles->count()); ?><br>
                First User: <?php echo e($users->first()->name ?? 'NONE'); ?><br>
            </div>
        </div>

        <?php if(session('success')): ?>
            <div style="background: #10b981; color: white; padding: 16px; border-radius: 8px; margin-bottom: 24px;">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
            <div style="background: #ef4444; color: white; padding: 16px; border-radius: 8px; margin-bottom: 24px;">
                <?php echo e(session('error')); ?>

            </div>
        <?php endif; ?>

        <!-- Users Table -->
        <div style="background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background: #f9fafb; border-bottom: 1px solid #e5e7eb;">
                    <tr>
                        <th style="padding: 16px; text-align: left; font-weight: 600; color: #6b7280; font-size: 14px;">Name</th>
                        <th style="padding: 16px; text-align: left; font-weight: 600; color: #6b7280; font-size: 14px;">E-Mail</th>
                        <th style="padding: 16px; text-align: left; font-weight: 600; color: #6b7280; font-size: 14px;">Rolle</th>
                        <th style="padding: 16px; text-align: center; font-weight: 600; color: #6b7280; font-size: 14px;">Status</th>
                        <th style="padding: 16px; text-align: left; font-weight: 600; color: #6b7280; font-size: 14px;">Letzter Login</th>
                        <th style="padding: 16px; text-align: right; font-weight: 600; color: #6b7280; font-size: 14px;">Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr style="border-bottom: 1px solid #e5e7eb;">
                            <td style="padding: 16px;">
                                <div style="font-weight: 500; color: #111827;"><?php echo e($user->name); ?></div>
                            </td>
                            <td style="padding: 16px;">
                                <div style="color: #6b7280;"><?php echo e($user->email); ?></div>
                            </td>
                            <td style="padding: 16px;">
                                <?php if($user->role): ?>
                                    <span style="display: inline-block; padding: 4px 12px; border-radius: 6px; font-size: 13px; font-weight: 500;
                                        background: <?php echo e($user->role->name === 'admin' ? '#dbeafe' : ($user->role->name === 'management' ? '#fef3c7' : '#e5e7eb')); ?>;
                                        color: <?php echo e($user->role->name === 'admin' ? '#1e40af' : ($user->role->name === 'management' ? '#92400e' : '#374151')); ?>;">
                                        <?php echo e($user->role->display_name); ?>

                                    </span>
                                <?php else: ?>
                                    <span style="color: #9ca3af;">Keine Rolle</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 16px; text-align: center;">
                                <button onclick="toggleActive(<?php echo e($user->id); ?>)" 
                                        id="status-btn-<?php echo e($user->id); ?>"
                                        style="padding: 4px 12px; border-radius: 6px; font-size: 13px; font-weight: 500; border: none; cursor: pointer;
                                            background: <?php echo e($user->is_active ? '#d1fae5' : '#fee2e2'); ?>;
                                            color: <?php echo e($user->is_active ? '#065f46' : '#991b1b'); ?>;">
                                    <span id="status-text-<?php echo e($user->id); ?>"><?php echo e($user->is_active ? 'Aktiv' : 'Inaktiv'); ?></span>
                                </button>
                            </td>
                            <td style="padding: 16px;">
                                <div style="color: #6b7280; font-size: 14px;">
                                    <?php echo e($user->last_login_at ? $user->last_login_at->format('d.m.Y H:i') : 'Nie'); ?>

                                </div>
                            </td>
                            <td style="padding: 16px; text-align: right;">
                                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                    <a href="<?php echo e(route('users.edit', $user)); ?>" 
                                       style="padding: 6px 12px; background: #f3f4f6; color: #374151; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500;">
                                        Bearbeiten
                                    </a>
                                    <?php if($user->id !== auth()->id()): ?>
                                        <form action="<?php echo e(route('users.destroy', $user)); ?>" method="POST" style="display: inline;"
                                              onsubmit="return confirm('Benutzer wirklich löschen?');">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" 
                                                    style="padding: 6px 12px; background: #fee2e2; color: #991b1b; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;">
                                                Löschen
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" style="padding: 48px; text-align: center; color: #9ca3af;">
                                Keine Benutzer gefunden
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function toggleActive(userId) {
            fetch(`/users/${userId}/toggle-active`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const btn = document.getElementById(`status-btn-${userId}`);
                    const text = document.getElementById(`status-text-${userId}`);
                    
                    if (data.is_active) {
                        btn.style.background = '#d1fae5';
                        btn.style.color = '#065f46';
                        text.textContent = 'Aktiv';
                    } else {
                        btn.style.background = '#fee2e2';
                        btn.style.color = '#991b1b';
                        text.textContent = 'Inaktiv';
                    }
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Fehler beim Ändern des Status');
            });
        }
    </script>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>

<?php /**PATH C:\xampp\htdocs\Day2Day-Manager\resources\views/users/index.blade.php ENDPATH**/ ?>