<?php $__env->startSection('title', 'Пополнить баланс'); ?>

<?php $__env->startSection('content'); ?>
<header class="header">
    <h1>💳 Пополнить баланс</h1>
    <a href="<?php echo e(route('payments.index')); ?>" class="btn btn-secondary">← Назад</a>
</header>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Выберите сумму</h2>
    </div>
    
    <form action="<?php echo e(route('payments.store')); ?>" method="POST">
        <?php echo csrf_field(); ?>
        
        <div class="form-group">
            <label for="amount">Сумма пополнения (₽)</label>
            <input type="number" id="amount" name="amount" value="<?php echo e(old('amount', 1000)); ?>" min="100" max="100000" required autofocus>
            <?php $__errorArgs = ['amount'];
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
            <label>Способ оплаты</label>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-top: 8px;">
                <?php if(isset($methods) && count($methods) > 0): ?>
                    <?php $__currentLoopData = $methods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $method): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label style="padding: 20px; border: 2px solid var(--border-color); border-radius: 8px; cursor: pointer; text-align: center; transition: all 0.2s; <?php echo e(old('method') === $method['id'] ? 'border-color: var(--accent-primary); background: rgba(16, 185, 129, 0.1);' : ''); ?>">
                        <input type="radio" name="method" value="<?php echo e($method['id']); ?>" <?php echo e(old('method') === $method['id'] || (!old('method') && $loop->first) ? 'checked' : ''); ?> style="display: none;">
                        <div style="font-size: 40px; margin-bottom: 8px;"><?php echo e($method['icon']); ?></div>
                        <div style="font-weight: 600; margin-bottom: 4px;"><?php echo e($method['title']); ?></div>
                        <div style="font-size: 12px; color: var(--text-muted);"><?php echo e($method['description']); ?></div>
                    </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php else: ?>
                    <div style="grid-column: 1/-1; text-align: center; padding: 40px; color: var(--text-muted);">
                        <div style="font-size: 48px; margin-bottom: 16px;">⚠️</div>
                        <p>Нет доступных способов оплаты</p>
                        <p style="font-size: 13px;">Обратитесь к администратору</p>
                    </div>
                <?php endif; ?>
            </div>
            <?php $__errorArgs = ['method'];
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
        
        <div style="background: var(--bg-tertiary); padding: 20px; border-radius: 8px; margin: 24px 0;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                <span style="color: var(--text-secondary);">Сумма:</span>
                <span id="amountDisplay">1000₽</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                <span style="color: var(--text-secondary);">Комиссия:</span>
                <span style="color: var(--accent-primary);">0₽</span>
            </div>
            <div style="border-top: 1px solid var(--border-color); padding-top: 12px; margin-top: 12px;">
                <div style="display: flex; justify-content: space-between; font-weight: 700; font-size: 18px;">
                    <span>Итого:</span>
                    <span id="totalDisplay">1000₽</span>
                </div>
            </div>
        </div>
        
        <div style="display: flex; gap: 12px;">
            <button type="submit" class="btn btn-primary" style="flex: 1;">Пополнить баланс</button>
            <a href="<?php echo e(route('payments.index')); ?>" class="btn btn-secondary" style="flex: 1;">Отмена</a>
        </div>
    </form>
</div>

<!-- Info -->
<div class="card" style="border: 1px solid var(--accent-blue);">
    <div class="card-header">
        <h2 class="card-title" style="color: var(--accent-blue);">ℹ️ Информация</h2>
    </div>
    
    <div style="padding: 16px; color: var(--text-secondary); line-height: 1.8;">
        <ul style="margin-left: 20px;">
            <li>Минимальная сумма: <strong>100₽</strong></li>
            <li>Максимальная сумма: <strong>100000₽</strong></li>
            <li>Комиссия: <strong>0₽</strong></li>
            <li>Зачисление: <strong>Мгновенно</strong></li>
        </ul>
    </div>
</div>

<script>
document.getElementById('amount').addEventListener('input', function() {
    const amount = this.value || 0;
    document.getElementById('amountDisplay').textContent = amount + '₽';
    document.getElementById('totalDisplay').textContent = amount + '₽';
});

// Выделение выбранного метода
document.querySelectorAll('input[name="method"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        document.querySelectorAll('label').forEach(function(label) {
            label.style.borderColor = 'var(--border-color)';
            label.style.background = 'transparent';
        });
        
        if (this.checked) {
            this.closest('label').style.borderColor = 'var(--accent-primary)';
            this.closest('label').style.background = 'rgba(16, 185, 129, 0.1)';
        }
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/payments/create.blade.php ENDPATH**/ ?>