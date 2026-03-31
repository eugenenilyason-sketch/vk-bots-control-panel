@extends('layouts.app')

@section('title', 'Пополнить баланс')

@section('content')
<header class="header">
    <h1>Пополнить баланс</h1>
    <a href="{{ route('payments.index') }}" class="btn btn-secondary">← Назад</a>
</header>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Выберите сумму</h2>
    </div>
    
    <form action="{{ route('payments.store') }}" method="POST">
        @csrf
        
        <div class="form-group">
            <label for="amount">Сумма пополнения (₽)</label>
            <input type="number" id="amount" name="amount" value="{{ old('amount', 1000) }}" min="100" max="100000" required autofocus>
            @error('amount')
            <div style="color: var(--status-error); font-size: 13px; margin-top: 4px;">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-group">
            <label>Способ оплаты</label>
            <div style="display: flex; gap: 12px; margin-top: 8px;">
                <label style="flex: 1; padding: 16px; border: 2px solid var(--border-color); border-radius: 8px; cursor: pointer; text-align: center;">
                    <input type="radio" name="method" value="yoomoney" {{ old('method') === 'yoomoney' || !old('method') ? 'checked' : '' }} style="display: none;">
                    <div style="font-size: 32px; margin-bottom: 8px;">💰</div>
                    <div style="font-weight: 600;">YooMoney</div>
                    <div style="font-size: 12px; color: var(--text-muted);">P2P перевод</div>
                </label>
                
                <label style="flex: 1; padding: 16px; border: 2px solid var(--border-color); border-radius: 8px; cursor: pointer; text-align: center;">
                    <input type="radio" name="method" value="card" {{ old('method') === 'card' ? 'checked' : '' }} style="display: none;">
                    <div style="font-size: 32px; margin-bottom: 8px;">💳</div>
                    <div style="font-weight: 600;">Банковская карта</div>
                    <div style="font-size: 12px; color: var(--text-muted);">Visa, MasterCard, MIR</div>
                </label>
            </div>
            @error('method')
            <div style="color: var(--status-error); font-size: 13px; margin-top: 4px;">{{ $message }}</div>
            @enderror
        </div>
        
        <div style="background: var(--bg-tertiary); padding: 16px; border-radius: 8px; margin: 24px 0;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                <span style="color: var(--text-secondary);">Сумма:</span>
                <span id="amountDisplay">1000₽</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                <span style="color: var(--text-secondary);">Комиссия:</span>
                <span style="color: var(--accent-primary);">0₽</span>
            </div>
            <div style="border-top: 1px solid var(--border-color); padding-top: 8px; margin-top: 8px;">
                <div style="display: flex; justify-content: space-between; font-weight: 700; font-size: 18px;">
                    <span>Итого:</span>
                    <span id="totalDisplay">1000₽</span>
                </div>
            </div>
        </div>
        
        <div style="display: flex; gap: 12px;">
            <button type="submit" class="btn btn-primary" style="flex: 1;">Пополнить баланс</button>
            <a href="{{ route('payments.index') }}" class="btn btn-secondary" style="flex: 1;">Отмена</a>
        </div>
    </form>
</div>

<script>
document.getElementById('amount').addEventListener('input', function() {
    const amount = this.value || 0;
    document.getElementById('amountDisplay').textContent = amount + '₽';
    document.getElementById('totalDisplay').textContent = amount + '₽';
});
</script>
@endsection
