<?php $__env->startSection('title', 'Настройки'); ?>

<?php $__env->startSection('content'); ?>
<header class="header">
    <h1>Настройки</h1>
    <div class="user-info">
        <div style="text-align: right;">
            <div style="font-weight: 600;"><?php echo e(auth()->user()->username ?? 'User'); ?></div>
            <div style="font-size: 12px; color: var(--text-secondary);">
                <?php echo e(auth()->user()->role); ?>

            </div>
        </div>
        <div class="user-avatar">
            <?php echo e(strtoupper(substr(auth()->user()->username ?? auth()->user()->email ?? 'U', 0, 1))); ?>

        </div>
    </div>
</header>

<!-- Flash Messages -->
<?php if(session('success')): ?>
<div class="status success">
    <?php echo e(session('success')); ?>

</div>
<?php endif; ?>

<?php if(session('error')): ?>
<div class="status error">
    <?php echo e(session('error')); ?>

</div>
<?php endif; ?>

<?php if($errors->any()): ?>
<div class="status error">
    <ul style="margin: 0; padding-left: 20px;">
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <li><?php echo e($error); ?></li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>
</div>
<?php endif; ?>

<!-- Profile Settings -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Профиль</h2>
    </div>
    
    <form action="<?php echo e(route('settings.update')); ?>" method="POST">
        <?php echo csrf_field(); ?>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo e(old('email', auth()->user()->email)); ?>" required>
            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <div style="color: var(--status-error); font-size: 13px; margin-top: 4px;"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        
        <div class="form-group">
            <label for="username">Имя пользователя</label>
            <input type="text" id="username" name="username" value="<?php echo e(old('username', auth()->user()->username)); ?>" required>
            <?php $__errorArgs = ['username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <div style="color: var(--status-error); font-size: 13px; margin-top: 4px;"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        
        <div class="form-group">
            <label>Роль</label>
            <input type="text" value="<?php echo e(auth()->user()->role); ?>" readonly style="background: var(--bg-tertiary); cursor: not-allowed;">
            <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Роль нельзя изменить</div>
        </div>
        
        <div class="form-group">
            <label>Баланс</label>
            <input type="text" value="<?php echo e(number_format(auth()->user()->balance, 0)); ?>₽" readonly style="background: var(--bg-tertiary); cursor: not-allowed;">
            <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Пополните баланс через раздел Оплата</div>
        </div>
        
        <button type="submit" class="btn btn-primary">Сохранить изменения</button>
    </form>
</div>

<!-- Change Password -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Безопасность</h2>
    </div>
    
    <form action="<?php echo e(route('settings.password')); ?>" method="POST">
        <?php echo csrf_field(); ?>
        
        <div class="form-group">
            <label for="current_password">Текущий пароль</label>
            <input type="password" id="current_password" name="current_password" required>
            <?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <div style="color: var(--status-error); font-size: 13px; margin-top: 4px;"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        
        <div class="form-group">
            <label for="password">Новый пароль</label>
            <input type="password" id="password" name="password" required minlength="6">
            <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Минимум 6 символов</div>
            <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <div style="color: var(--status-error); font-size: 13px; margin-top: 4px;"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        
        <div class="form-group">
            <label for="password_confirmation">Подтверждение пароля</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required minlength="6">
            <?php $__errorArgs = ['password_confirmation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <div style="color: var(--status-error); font-size: 13px; margin-top: 4px;"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        
        <button type="submit" class="btn btn-primary">Изменить пароль</button>
    </form>
</div>

<!-- Account Info -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Информация об аккаунте</h2>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
        <div style="background: var(--bg-tertiary); padding: 16px; border-radius: 8px;">
            <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Дата регистрации</div>
            <div style="font-weight: 600;"><?php echo e(auth()->user()->created_at->format('d.m.Y')); ?></div>
        </div>
        <div style="background: var(--bg-tertiary); padding: 16px; border-radius: 8px;">
            <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Последнее обновление</div>
            <div style="font-weight: 600;"><?php echo e(auth()->user()->updated_at->format('d.m.Y H:i')); ?></div>
        </div>
        <div style="background: var(--bg-tertiary); padding: 16px; border-radius: 8px;">
            <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">VK ID</div>
            <div style="font-weight: 600;"><?php echo e(auth()->user()->vk_id ?? 'Не привязан'); ?></div>
        </div>
        <div style="background: var(--bg-tertiary); padding: 16px; border-radius: 8px;">
            <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Статус</div>
            <div style="font-weight: 600;">
                <span class="badge <?php echo e(auth()->user()->is_blocked ? 'badge-error' : 'badge-success'); ?>">
                    <?php echo e(auth()->user()->is_blocked ? 'Заблокирован' : 'Активен'); ?>

                </span>
            </div>
        </div>
    </div>
</div>

<!-- Danger Zone -->
<div class="card" style="border: 1px solid var(--status-error);">
    <div class="card-header">
        <h2 class="card-title" style="color: var(--status-error);">Опасная зона</h2>
    </div>
    
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px;">
        <div>
            <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 4px;">Выйти из аккаунта</h3>
            <p style="color: var(--text-muted); font-size: 14px;">Вы выйдете из всех устройств</p>
        </div>
        <form action="<?php echo e(route('logout')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn btn-danger">
                <span>🚪</span>
                Выйти
            </button>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/settings.blade.php ENDPATH**/ ?>