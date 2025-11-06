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
    <div style="max-width: 800px; margin: 0 auto; padding: 24px;">
        <!-- Header -->
        <div style="margin-bottom: 24px;">
            <a href="<?php echo e(route('users.index')); ?>" 
               style="display: inline-flex; align-items: center; gap: 8px; color: #6b7280; text-decoration: none; margin-bottom: 16px;">
                <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Zurück zur Übersicht
            </a>
            <h1 style="font-size: 28px; font-weight: 600; color: #111827; margin: 0;">Benutzer bearbeiten</h1>
        </div>

        <!-- Form -->
        <div style="background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 24px;">
            <form action="<?php echo e(route('users.update', $user)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <!-- Name -->
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 500; color: #374151; margin-bottom: 8px;">
                        Name <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="text" name="name" value="<?php echo e(old('name', $user->name)); ?>" required
                           style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                    <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div style="color: #ef4444; font-size: 13px; margin-top: 4px;"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <!-- Email -->
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 500; color: #374151; margin-bottom: 8px;">
                        E-Mail <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="email" name="email" value="<?php echo e(old('email', $user->email)); ?>" required
                           style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div style="color: #ef4444; font-size: 13px; margin-top: 4px;"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <!-- Password -->
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 500; color: #374151; margin-bottom: 8px;">
                        Neues Passwort
                    </label>
                    <input type="password" name="password"
                           style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                    <div style="color: #6b7280; font-size: 13px; margin-top: 4px;">Leer lassen, um das Passwort nicht zu ändern</div>
                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div style="color: #ef4444; font-size: 13px; margin-top: 4px;"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <!-- Password Confirmation -->
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 500; color: #374151; margin-bottom: 8px;">
                        Passwort bestätigen
                    </label>
                    <input type="password" name="password_confirmation"
                           style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                </div>

                <!-- Role -->
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 500; color: #374151; margin-bottom: 8px;">
                        Rolle <span style="color: #ef4444;">*</span>
                    </label>
                    <select name="role_id" required
                            style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                        <option value="">Rolle auswählen</option>
                        <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($role->id); ?>" <?php echo e(old('role_id', $user->role_id) == $role->id ? 'selected' : ''); ?>>
                                <?php echo e($role->display_name); ?> (Level <?php echo e($role->level); ?>)
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php $__errorArgs = ['role_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div style="color: #ef4444; font-size: 13px; margin-top: 4px;"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <!-- Active Status -->
                <div style="margin-bottom: 24px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="is_active" value="1" <?php echo e(old('is_active', $user->is_active) ? 'checked' : ''); ?>

                               style="width: 18px; height: 18px; cursor: pointer;">
                        <span style="font-weight: 500; color: #374151;">Benutzer ist aktiv</span>
                    </label>
                </div>

                <!-- Actions -->
                <div style="display: flex; gap: 12px; justify-content: flex-end; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                    <a href="<?php echo e(route('users.index')); ?>" 
                       style="padding: 10px 20px; background: #f3f4f6; color: #374151; border-radius: 8px; text-decoration: none; font-weight: 500;">
                        Abbrechen
                    </a>
                    <button type="submit" 
                            style="padding: 10px 20px; background: #111827; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 500;">
                        Änderungen speichern
                    </button>
                </div>
            </form>
        </div>
    </div>
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

<?php /**PATH C:\xampp\htdocs\Day2Day-Manager\resources\views/users/edit.blade.php ENDPATH**/ ?>