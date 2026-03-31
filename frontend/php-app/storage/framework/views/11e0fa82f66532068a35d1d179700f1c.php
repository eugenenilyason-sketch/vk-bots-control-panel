<?php $__env->startSection('title', 'Боты'); ?>

<?php $__env->startSection('content'); ?>
<header class="header">
    <h1>Боты</h1>
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

<!-- Create Bot Button -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Мои боты</h2>
        <a href="<?php echo e(route('bots.create')); ?>" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Создать бота
        </a>
    </div>
    
    <?php if(isset($bots) && $bots->count() > 0): ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Название</th>
                    <th>Статус</th>
                    <th>Сообщений</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $bots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td style="font-weight: 600;"><?php echo e($bot->name); ?></td>
                    <td>
                        <span class="badge <?php echo e($bot->status === 'active' ? 'badge-success' : ($bot->status === 'inactive' ? 'badge-warning' : 'badge-error')); ?>">
                            <?php echo e($bot->status); ?>

                        </span>
                    </td>
                    <td><?php echo e($bot->messages_sent ?? 0); ?> отпр. / <?php echo e($bot->messages_received ?? 0); ?> получ.</td>
                    <td>
                        <div style="display: flex; gap: 8px;">
                            <?php if($bot->status === 'active'): ?>
                            <form action="<?php echo e(route('bots.stop', $bot->id)); ?>" method="POST" style="display: inline;">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn btn-secondary btn-icon" title="Остановить">⏸️</button>
                            </form>
                            <?php else: ?>
                            <form action="<?php echo e(route('bots.start', $bot->id)); ?>" method="POST" style="display: inline;">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn btn-primary btn-icon" title="Запустить">▶️</button>
                            </form>
                            <?php endif; ?>
                            
                            <a href="<?php echo e(route('bots.edit', $bot->id)); ?>" class="btn btn-secondary btn-icon" title="Редактировать">✏️</a>
                            
                            <form action="<?php echo e(route('bots.destroy', $bot->id)); ?>" method="POST" style="display: inline;" onsubmit="return confirm('Вы уверены?');">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-secondary btn-icon" style="color: var(--status-error);" title="Удалить">🗑️</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div style="text-align: center; padding: 40px; color: var(--text-muted);">
        <div style="font-size: 48px; margin-bottom: 16px;">🤖</div>
        <p>У вас пока нет ботов</p>
        <a href="<?php echo e(route('bots.create')); ?>" class="btn btn-primary" style="margin-top: 16px;">Создать первого бота</a>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/bots/index.blade.php ENDPATH**/ ?>