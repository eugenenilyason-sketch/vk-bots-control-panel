@extends('layouts.app')

@section('title', 'Настройки системы')

@section('content')
<header class="header">
    <h1>⚙️ Настройки системы</h1>
    <div style="display: flex; gap: 12px;">
        <a href="{{ route('admin') }}" class="btn btn-secondary">← Админка</a>
        <a href="{{ route('admin.users') }}" class="btn btn-secondary">Пользователи</a>
    </div>
</header>

<!-- Flash Messages -->
@if(session('success'))
<div class="status success">
    {{ session('success') }}
</div>
@endif

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

<!-- Registration Settings -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">🔐 Регистрация</h2>
    </div>
    
    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf
        
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 16px 0;">
            <div style="flex: 1;">
                <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 4px;">Разрешить регистрацию</h3>
                <p style="color: var(--text-muted); font-size: 14px;">
                    Если отключено, новые пользователи не смогут зарегистрироваться на сайте
                </p>
            </div>
            
            <label style="display: flex; align-items: center; cursor: pointer;">
                <input type="checkbox" name="registration_enabled" value="1" {{ $settings['registration_enabled'] ? 'checked' : '' }} style="width: 20px; height: 20px; margin-right: 8px;">
                <span style="font-size: 14px; font-weight: 600;">{{ $settings['registration_enabled'] ? 'Включено' : 'Отключено' }}</span>
            </label>
        </div>
        
        <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border-color);">
            <button type="submit" class="btn btn-primary">💾 Сохранить настройки</button>
        </div>
    </form>
</div>

<!-- Current Status -->
<div class="card" style="border: 1px solid var(--accent-blue);">
    <div class="card-header">
        <h2 class="card-title" style="color: var(--accent-blue);">ℹ️ Текущий статус</h2>
    </div>
    
    <div style="padding: 16px;">
        @if($settings['registration_enabled'])
        <div style="display: flex; align-items: center; gap: 12px; padding: 16px; background: rgba(34, 197, 94, 0.1); border-radius: 8px;">
            <span style="font-size: 24px;">✅</span>
            <div>
                <strong style="color: var(--status-success);">Регистрация включена</strong>
                <p style="margin: 4px 0 0 0; color: var(--text-secondary); font-size: 14px;">
                    Новые пользователи могут регистрироваться на сайте
                </p>
            </div>
        </div>
        @else
        <div style="display: flex; align-items: center; gap: 12px; padding: 16px; background: rgba(239, 68, 68, 0.1); border-radius: 8px;">
            <span style="font-size: 24px;">⛔</span>
            <div>
                <strong style="color: var(--status-error);">Регистрация отключена</strong>
                <p style="margin: 4px 0 0 0; color: var(--text-secondary); font-size: 14px;">
                    Новые пользователи не могут регистрироваться на сайте
                </p>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Info -->
<div class="card" style="border: 1px solid var(--accent-blue);">
    <div class="card-header">
        <h2 class="card-title" style="color: var(--accent-blue);">ℹ️ Информация</h2>
    </div>
    
    <div style="padding: 16px; color: var(--text-secondary); line-height: 1.8;">
        <ul style="margin-left: 20px;">
            <li>Только суперадмин может изменять настройки</li>
            <li>При отключении регистрации форма регистрации показывает сообщение</li>
            <li>Существующие пользователи могут входить в систему</li>
        </ul>
    </div>
</div>
@endsection
