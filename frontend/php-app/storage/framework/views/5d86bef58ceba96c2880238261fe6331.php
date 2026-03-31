<?php $__env->startSection('title', 'Админ-панель'); ?>

<?php $__env->startSection('content'); ?>
<header class="header">
    <h1>🛡️ Админ-панель</h1>
    <div class="user-info">
        <div style="text-align: right;">
            <div style="font-weight: 600;"><?php echo e(auth()->user()->username); ?></div>
            <div style="font-size: 12px; color: var(--text-secondary);">
                <?php echo e(auth()->user()->role); ?>

            </div>
        </div>
        <div class="user-avatar">
            <?php echo e(strtoupper(substr(auth()->user()->username ?? 'A', 0, 1))); ?>

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

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Пользователей</div>
        <div class="stat-value"><?php echo e($stats['totalUsers'] ?? 0); ?></div>
        <div style="font-size: 12px; color: var(--text-muted);">
            <span style="color: var(--accent-primary);"><?php echo e($stats['activeUsers'] ?? 0); ?></span> активных
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Ботов</div>
        <div class="stat-value"><?php echo e($stats['totalBots'] ?? 0); ?></div>
        <div style="font-size: 12px; color: var(--text-muted);">
            <span style="color: var(--accent-primary);"><?php echo e($stats['activeBots'] ?? 0); ?></span> активных
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Доход</div>
        <div class="stat-value"><?php echo e(number_format($stats['totalRevenue'] ?? 0, 0)); ?>₽</div>
        <div style="font-size: 12px; color: var(--text-muted);">
            <?php echo e($stats['totalPayments'] ?? 0); ?> платежей
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-label">В обработке</div>
        <div class="stat-value"><?php echo e($stats['pendingPayments'] ?? 0); ?></div>
        <div style="font-size: 12px; color: var(--text-muted);">Платежей</div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Управление</h2>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
        <a href="<?php echo e(route('admin.users')); ?>" class="btn btn-primary" style="padding: 24px; text-decoration: none;">
            <div style="font-size: 32px; margin-bottom: 8px;">👥</div>
            <div style="font-weight: 600;">Пользователи</div>
            <div style="font-size: 12px; color: var(--text-muted);">Управление</div>
        </a>
        
        <?php if(auth()->user()->role === 'superadmin'): ?>
        <a href="<?php echo e(route('admin.payment-methods')); ?>" class="btn btn-primary" style="padding: 24px; text-decoration: none;">
            <div style="font-size: 32px; margin-bottom: 8px;">💳</div>
            <div style="font-weight: 600;">Платёжные методы</div>
            <div style="font-size: 12px; color: var(--text-muted);">Настройка</div>
        </a>
        
        <a href="<?php echo e(route('admin.settings')); ?>" class="btn btn-primary" style="padding: 24px; text-decoration: none;">
            <div style="font-size: 32px; margin-bottom: 8px;">⚙️</div>
            <div style="font-weight: 600;">Настройки</div>
            <div style="font-size: 12px; color: var(--text-muted);">Система</div>
        </a>
        <?php endif; ?>
        
        <a href="<?php echo e(route('bots.index')); ?>" class="btn btn-secondary" style="padding: 24px; text-decoration: none;">
            <div style="font-size: 32px; margin-bottom: 8px;">🤖</div>
            <div style="font-weight: 600;">Боты</div>
            <div style="font-size: 12px; color: var(--text-muted);">Просмотр</div>
        </a>
        
        <a href="<?php echo e(route('payments.index')); ?>" class="btn btn-secondary" style="padding: 24px; text-decoration: none;">
            <div style="font-size: 32px; margin-bottom: 8px;">💳</div>
            <div style="font-weight: 600;">Платежи</div>
            <div style="font-size: 12px; color: var(--text-muted);">История</div>
        </a>
    </div>
</div>

<!-- Recent Users -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Новые пользователи</h2>
        <a href="<?php echo e(route('admin.users')); ?>" class="btn btn-secondary">Все</a>
    </div>
    
    <?php if(isset($recentUsers) && $recentUsers->count() > 0): ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Имя</th>
                    <th>Роль</th>
                    <th>Баланс</th>
                    <th>Дата</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $recentUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($user->email); ?></td>
                    <td><?php echo e($user->username ?? '—'); ?></td>
                    <td><span class="badge badge-info"><?php echo e($user->role); ?></span></td>
                    <td><?php echo e(number_format($user->balance, 0)); ?>₽</td>
                    <td style="color: var(--text-muted);"><?php echo e($user->created_at->format('d.m.Y')); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div style="text-align: center; padding: 40px; color: var(--text-muted);">
        <p>Нет новых пользователей</p>
    </div>
    <?php endif; ?>
</div>

<!-- Recent Payments -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Последние платежи</h2>
        <a href="<?php echo e(route('payments.index')); ?>" class="btn btn-secondary">Все</a>
    </div>
    
    <?php if(isset($recentPayments) && $recentPayments->count() > 0): ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Пользователь</th>
                    <th>Сумма</th>
                    <th>Метод</th>
                    <th>Статус</th>
                    <th>Дата</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $recentPayments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($payment->user->email ?? '—'); ?></td>
                    <td style="font-weight: 600;"><?php echo e(number_format($payment->amount, 0)); ?>₽</td>
                    <td><?php echo e($payment->provider ?? '—'); ?></td>
                    <td>
                        <span class="badge <?php echo e($payment->status === 'succeeded' ? 'badge-success' : ($payment->status === 'pending' ? 'badge-warning' : 'badge-error')); ?>">
                            <?php echo e($payment->status); ?>

                        </span>
                    </td>
                    <td style="color: var(--text-muted);"><?php echo e($payment->created_at->format('d.m.Y H:i')); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div style="text-align: center; padding: 40px; color: var(--text-muted);">
        <p>Нет платежей</p>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin.blade.php ENDPATH**/ ?>