@extends('layouts.app')

@section('title', 'Настройки')

@section('content')
<header class="header">
    <h1>Настройки</h1>
    <div class="user-info">
        <div style="text-align: right;">
            <div style="font-weight: 600;">{{ auth()->user()->username ?? 'User' }}</div>
            <div style="font-size: 12px; color: var(--text-secondary);">
                {{ auth()->user()->role }}
            </div>
        </div>
        <div class="user-avatar">
            {{ strtoupper(substr(auth()->user()->username ?? auth()->user()->email ?? 'U', 0, 1)) }}
        </div>
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

<!-- Profile Settings -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Профиль</h2>
    </div>
    
    <form action="{{ route('settings.update') }}" method="POST">
        @csrf
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email', auth()->user()->email) }}" required>
            @error('email')
            <div style="color: var(--status-error); font-size: 13px; margin-top: 4px;">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-group">
            <label for="username">Имя пользователя</label>
            <input type="text" id="username" name="username" value="{{ old('username', auth()->user()->username) }}" required>
            @error('username')
            <div style="color: var(--status-error); font-size: 13px; margin-top: 4px;">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-group">
            <label>Роль</label>
            <input type="text" value="{{ auth()->user()->role }}" readonly style="background: var(--bg-tertiary); cursor: not-allowed;">
            <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Роль нельзя изменить</div>
        </div>
        
        <div class="form-group">
            <label>Баланс</label>
            <input type="text" value="{{ number_format(auth()->user()->balance, 0) }}₽" readonly style="background: var(--bg-tertiary); cursor: not-allowed;">
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
    
    <form action="{{ route('settings.password') }}" method="POST">
        @csrf
        
        <div class="form-group">
            <label for="current_password">Текущий пароль</label>
            <input type="password" id="current_password" name="current_password" required>
            @error('current_password')
            <div style="color: var(--status-error); font-size: 13px; margin-top: 4px;">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-group">
            <label for="password">Новый пароль</label>
            <input type="password" id="password" name="password" required minlength="6">
            <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Минимум 6 символов</div>
            @error('password')
            <div style="color: var(--status-error); font-size: 13px; margin-top: 4px;">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-group">
            <label for="password_confirmation">Подтверждение пароля</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required minlength="6">
            @error('password_confirmation')
            <div style="color: var(--status-error); font-size: 13px; margin-top: 4px;">{{ $message }}</div>
            @enderror
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
            <div style="font-weight: 600;">{{ auth()->user()->created_at->format('d.m.Y') }}</div>
        </div>
        <div style="background: var(--bg-tertiary); padding: 16px; border-radius: 8px;">
            <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Последнее обновление</div>
            <div style="font-weight: 600;">{{ auth()->user()->updated_at->format('d.m.Y H:i') }}</div>
        </div>
        <div style="background: var(--bg-tertiary); padding: 16px; border-radius: 8px;">
            <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">VK ID</div>
            <div style="font-weight: 600;">{{ auth()->user()->vk_id ?? 'Не привязан' }}</div>
        </div>
        <div style="background: var(--bg-tertiary); padding: 16px; border-radius: 8px;">
            <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Статус</div>
            <div style="font-weight: 600;">
                <span class="badge {{ auth()->user()->is_blocked ? 'badge-error' : 'badge-success' }}">
                    {{ auth()->user()->is_blocked ? 'Заблокирован' : 'Активен' }}
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
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-danger">
                <span>🚪</span>
                Выйти
            </button>
        </form>
    </div>
</div>
@endsection
