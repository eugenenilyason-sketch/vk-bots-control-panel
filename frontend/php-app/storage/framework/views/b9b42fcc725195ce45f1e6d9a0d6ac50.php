<?php $__env->startSection('title', 'Оплата'); ?>

<?php $__env->startSection('content'); ?>
<header class="header">
    <h1>Оплата</h1>
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

<!-- Stats -->
<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-label">Всего пополнено</div>
        <div class="stat-value"><?php echo e(number_format($totalSpent ?? 0, 0)); ?>₽</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">В обработке</div>
        <div class="stat-value"><?php echo e($pendingCount ?? 0); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Текущий баланс</div>
        <div class="stat-value"><?php echo e(number_format(auth()->user()->balance ?? 0, 0)); ?>₽</div>
    </div>
</div>

<!-- Actions -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Пополнить баланс</h2>
        <a href="<?php echo e(route('payments.create')); ?>" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Пополнить
        </a>
    </div>
    
    <div style="text-align: center; padding: 30px; color: var(--text-muted);">
        <div style="font-size: 48px; margin-bottom: 16px;">💳</div>
        <p>Выберите сумму и способ оплаты для пополнения баланса</p>
    </div>
</div>

<!-- History -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">История платежей</h2>
        
        <!-- Filters -->
        <form action="<?php echo e(route('payments.index')); ?>" method="GET" style="display: flex; gap: 8px;">
            <select name="status" class="btn btn-secondary" style="padding: 8px 12px;">
                <option value="">Все статусы</option>
                <option value="succeeded" <?php echo e(request('status') === 'succeeded' ? 'selected' : ''); ?>>Успешно</option>
                <option value="pending" <?php echo e(request('status') === 'pending' ? 'selected' : ''); ?>>В обработке</option>
                <option value="failed" <?php echo e(request('status') === 'failed' ? 'selected' : ''); ?>>Ошибка</option>
            </select>
            <button type="submit" class="btn btn-secondary">Фильтр</button>
            <?php if(request('status')): ?>
            <a href="<?php echo e(route('payments.index')); ?>" class="btn btn-secondary">Сброс</a>
            <?php endif; ?>
        </form>
    </div>
    
    <?php if(isset($payments) && $payments->count() > 0): ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Сумма</th>
                    <th>Метод</th>
                    <th>Тип</th>
                    <th>Статус</th>
                    <th>Дата</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td style="font-weight: 600;"><?php echo e(number_format($payment->amount, 0)); ?>₽</td>
                    <td><?php echo e($payment->provider ?? '—'); ?></td>
                    <td>
                        <span class="badge badge-info"><?php echo e($payment->type ?? 'deposit'); ?></span>
                    </td>
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
    
    <!-- Pagination -->
    <?php if($payments->hasPages()): ?>
    <div style="margin-top: 24px;">
        <?php echo e($payments->links()); ?>

    </div>
    <?php endif; ?>
    <?php else: ?>
    <div style="text-align: center; padding: 40px; color: var(--text-muted);">
        <div style="font-size: 48px; margin-bottom: 16px;">📋</div>
        <p>История платежей пуста</p>
        <a href="<?php echo e(route('payments.create')); ?>" class="btn btn-primary" style="margin-top: 16px;">Пополнить баланс</a>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/payments/index.blade.php ENDPATH**/ ?>