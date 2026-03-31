@extends('layouts.app')

@section('title', 'Добавить платёжный метод')

@section('content')
<header class="header">
    <h1>💳 Добавить платёжный метод</h1>
    <a href="{{ route('admin.payment-methods') }}" class="btn btn-secondary">← Назад</a>
</header>

<!-- Flash Messages -->
@if(session('error'))
<div class="status error">
    {{ session('error') }}
</div>
@endif

@if($errors->any())
<div class="status error">
    <ul style="margin: 0; padding-left: 20px;">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Основная информация</h2>
    </div>
    
    <form action="{{ route('admin.payment-methods.store') }}" method="POST">
        @csrf
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label for="name">ID метода *</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required placeholder="например: paypal" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-tertiary); color: var(--text-primary);">
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Латиница, без пробелов</div>
                @error('name')
                <div style="color: var(--status-error); font-size: 13px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="display_name">Отображаемое имя *</label>
                <input type="text" id="display_name" name="display_name" value="{{ old('display_name') }}" required placeholder="например: PayPal" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-tertiary); color: var(--text-primary);">
                @error('display_name')
                <div style="color: var(--status-error); font-size: 13px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label for="type">Тип *</label>
                <select id="type" name="type" required style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-tertiary); color: var(--text-primary);">
                    <option value="p2p" {{ old('type') === 'p2p' ? 'selected' : '' }}>P2P</option>
                    <option value="card" {{ old('type') === 'card' ? 'selected' : '' }}>Карта</option>
                    <option value="qr" {{ old('type') === 'qr' ? 'selected' : '' }}>QR (СБП)</option>
                    <option value="crypto" {{ old('type') === 'crypto' ? 'selected' : '' }}>Крипта</option>
                </select>
                @error('type')
                <div style="color: var(--status-error); font-size: 13px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="icon">Иконка</label>
                <input type="text" id="icon" name="icon" value="{{ old('icon', '💳') }}" placeholder="💳" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-tertiary); color: var(--text-primary);">
            </div>
            
            <div class="form-group">
                <label for="sort_order">Порядок</label>
                <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-tertiary); color: var(--text-primary);">
            </div>
        </div>
        
        <div class="form-group">
            <label for="description">Описание</label>
            <textarea id="description" name="description" rows="3" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-tertiary); color: var(--text-primary);">{{ old('description') }}</textarea>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label for="min_amount">Мин. сумма (₽)</label>
                <input type="number" id="min_amount" name="min_amount" value="{{ old('min_amount', 100) }}" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-tertiary); color: var(--text-primary);">
            </div>
            
            <div class="form-group">
                <label for="max_amount">Макс. сумма (₽)</label>
                <input type="number" id="max_amount" name="max_amount" value="{{ old('max_amount', 100000) }}" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-tertiary); color: var(--text-primary);">
            </div>
            
            <div class="form-group">
                <label for="commission">Комиссия (%)</label>
                <input type="number" id="commission" name="commission" value="{{ old('commission', 0) }}" step="0.01" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-tertiary); color: var(--text-primary);">
            </div>
        </div>
        
        <div class="form-group">
            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                <input type="checkbox" name="is_enabled" value="1" {{ old('is_enabled') ? 'checked' : '' }} style="width: 18px; height: 18px;">
                <span>Включен (виден пользователям)</span>
            </label>
        </div>
        
        <div style="display: flex; gap: 12px; margin-top: 24px;">
            <button type="submit" class="btn btn-primary" style="flex: 1;">💾 Создать метод</button>
            <a href="{{ route('admin.payment-methods') }}" class="btn btn-secondary" style="flex: 1;">Отмена</a>
        </div>
    </form>
</div>

<!-- API Settings -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">🔐 API настройки (зашифровано)</h2>
    </div>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
        <div class="form-group">
            <label for="api_key">API Key</label>
            <input type="text" id="api_key" name="api_key" placeholder="Введите API ключ" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-tertiary); color: var(--text-primary);">
        </div>
        
        <div class="form-group">
            <label for="api_secret">API Secret</label>
            <input type="password" id="api_secret" name="api_secret" placeholder="Введите API секрет" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-tertiary); color: var(--text-primary);">
        </div>
        
        <div class="form-group">
            <label for="merchant_id">Merchant ID</label>
            <input type="text" id="merchant_id" name="merchant_id" placeholder="Введите Merchant ID" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-tertiary); color: var(--text-primary);">
        </div>
    </div>
</div>

<!-- Info -->
<div class="card" style="border: 1px solid var(--accent-blue);">
    <div class="card-header">
        <h2 class="card-title" style="color: var(--accent-blue);">ℹ️ Информация</h2>
    </div>
    
    <div style="padding: 16px; color: var(--text-secondary); line-height: 1.8;">
        <ul style="margin-left: 20px;">
            <li><strong>ID метода</strong> — уникальное имя (латиница)</li>
            <li><strong>API ключи</strong> — шифруются перед сохранением</li>
            <li><strong>Включен</strong> — если снято, метод не виден пользователям</li>
        </ul>
    </div>
</div>
@endsection
