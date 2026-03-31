<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<header class="header">
    <h1>Dashboard</h1>
    <div class="user-info">
        <div style="text-align: right;">
            <div style="font-weight: 600;"><?php echo e(auth()->user()->username ?? 'User'); ?></div>
            <div style="font-size: 12px; color: var(--text-secondary);">
                <?php echo e(auth()->user()->role === 'superadmin' ? 'Суперадмин' : (auth()->user()->role === 'admin' ? 'Админ' : 'Пользователь')); ?>

            </div>
        </div>
        <div class="user-avatar">
            <?php echo e(strtoupper(substr(auth()->user()->username ?? auth()->user()->email ?? 'U', 0, 1))); ?>

        </div>
    </div>
</header>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Баланс</div>
        <div class="stat-value"><?php echo e(number_format(auth()->user()->balance ?? 0, 0)); ?>₽</div>
        <div style="font-size: 12px; color: var(--text-muted);">Доступно</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Ботов</div>
        <div class="stat-value"><?php echo e($botsCount ?? 0); ?></div>
        <div style="font-size: 12px; color: var(--text-muted);">
            <span style="color: var(--accent-primary);"><?php echo e($activeBots ?? 0); ?></span> активных
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Платежей</div>
        <div class="stat-value"><?php echo e($paymentsCount ?? 0); ?></div>
        <div style="font-size: 12px; color: var(--text-muted);">На сумму <?php echo e(number_format($totalSpent ?? 0, 0)); ?>₽</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Статус</div>
        <div class="stat-value" style="font-size: 18px;">
            <span class="badge badge-success">Active</span>
        </div>
        <div style="font-size: 12px; color: var(--text-muted);">Аккаунт</div>
    </div>
</div>

<!-- Recent Bots -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Мои боты</h2>
        <a href="/bots" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Создать бота
        </a>
    </div>
    
    <?php if(isset($recentBots) && $recentBots->count() > 0): ?>
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
                <?php $__currentLoopData = $recentBots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td style="font-weight: 600;"><?php echo e($bot->name); ?></td>
                    <td>
                        <span class="badge <?php echo e($bot->status === 'active' ? 'badge-success' : ($bot->status === 'inactive' ? 'badge-warning' : 'badge-error')); ?>">
                            <?php echo e($bot->status); ?>

                        </span>
                    </td>
                    <td><?php echo e($bot->messages_sent ?? 0); ?> отпр. / <?php echo e($bot->messages_received ?? 0); ?> получ.</td>
                    <td>
                        <a href="/bots" class="btn btn-secondary btn-icon">✏️</a>
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
        <a href="/bots" class="btn btn-primary" style="margin-top: 16px;">Создать первого бота</a>
    </div>
    <?php endif; ?>
</div>

<!-- Recent Payments -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Последние платежи</h2>
        <a href="/payments" class="btn btn-secondary">Все платежи</a>
    </div>
    
    <?php if(isset($recentPayments) && $recentPayments->count() > 0): ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Сумма</th>
                    <th>Метод</th>
                    <th>Статус</th>
                    <th>Дата</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $recentPayments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td style="font-weight: 600;"><?php echo e(number_format($payment->amount, 0)); ?>₽</td>
                    <td><?php echo e($payment->provider ?? '—'); ?></td>
                    <td>
                        <span class="badge <?php echo e($payment->status === 'succeeded' ? 'badge-success' : ($payment->status === 'pending' ? 'badge-warning' : 'badge-error')); ?>">
                            <?php echo e($payment->status); ?>

                        </span>
                    </td>
                    <td style="color: var(--text-muted);"><?php echo e($payment->created_at->format('d.m.Y')); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div style="text-align: center; padding: 40px; color: var(--text-muted);">
        <div style="font-size: 48px; margin-bottom: 16px;">💳</div>
        <p>История платежей пуста</p>
        <a href="/payments" class="btn btn-primary" style="margin-top: 16px;">Пополнить баланс</a>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/dashboard.blade.php ENDPATH**/ ?>