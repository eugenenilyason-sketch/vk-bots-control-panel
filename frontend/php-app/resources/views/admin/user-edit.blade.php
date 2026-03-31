@extends('layouts.app')

@section('title', 'Редактирование пользователя')

@section('content')
<header class="header">
    <h1>✏️ Редактирование пользователя</h1>
    <a href="{{ route('admin.users') }}" class="btn btn-secondary">← Назад</a>
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

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Информация</h2>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 24px;">
        <div style="background: var(--bg-tertiary); padding: 16px; border-radius: 8px;">
            <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">ID</div>
            <div style="font-weight: 600; font-size: 14px;">{{ $user->id }}</div>
        </div>
        <div style="background: var(--bg-tertiary); padding: 16px; border-radius: 8px;">
            <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Email</div>
            <div style="font-weight: 600;">{{ $user->email }}</div>
        </div>
        <div style="background: var(--bg-tertiary); padding: 16px; border-radius: 8px;">
            <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Имя</div>
            <div style="font-weight: 600;">{{ $user->username ?? '—' }}</div>
        </div>
        <div style="background: var(--bg-tertiary); padding: 16px; border-radius: 8px;">
            <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Дата регистрации</div>
            <div style="font-weight: 600;">{{ $user->created_at->format('d.m.Y H:i') }}</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Редактирование</h2>
    </div>
    
    <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
        @csrf
        
        <div class="form-group">
            <label for="role">Роль</label>
            <select id="role" name="role" required style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-tertiary);">
                <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>Пользователь</option>
                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Администратор</option>
                <option value="superadmin" {{ $user->role === 'superadmin' ? 'selected' : '' }}>Суперадминистратор</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="balance">Баланс (₽)</label>
            <input type="number" id="balance" name="balance" value="{{ old('balance', $user->balance) }}" min="0" step="0.01" required style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-tertiary);">
        </div>
        
        <div class="form-group">
            <label style="display: flex; align-items: center; gap: 8px;">
                <input type="checkbox" name="is_active" value="1" {{ $user->is_active ? 'checked' : '' }} style="width: 18px; height: 18px;">
                Активен
            </label>
        </div>
        
        <div class="form-group">
            <label style="display: flex; align-items: center; gap: 8px;">
                <input type="checkbox" name="is_blocked" value="1" {{ $user->is_blocked ? 'checked' : '' }} style="width: 18px; height: 18px;">
                Заблокирован
            </label>
            <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Заблокированные пользователи не могут войти</div>
        </div>

        <div style="display: flex; gap: 12px; margin-top: 24px;">
            <button type="submit" class="btn btn-primary" style="flex: 1;">Сохранить</button>
            <a href="{{ route('admin.users') }}" class="btn btn-secondary" style="flex: 1;">Отмена</a>
        </div>
    </form>
</div>

<!-- Change Password -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">🔐 Изменить пароль</h2>
    </div>

    <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
        @csrf
        <input type="hidden" name="role" value="{{ $user->role }}">
        <input type="hidden" name="balance" value="{{ $user->balance }}">
        <input type="hidden" name="is_active" value="{{ $user->is_active ? '1' : '0' }}">
        <input type="hidden" name="is_blocked" value="{{ $user->is_blocked ? '1' : '0' }}">

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label for="password">Новый пароль</label>
                <input type="password" id="password" name="password" placeholder="Минимум 6 символов" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-tertiary); color: var(--text-primary);">
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Оставьте пустым чтобы не менять</div>
                @error('password')
                <div style="color: var(--status-error); font-size: 13px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Подтверждение пароля</label>
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Повторите пароль" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-tertiary); color: var(--text-primary);">
                @error('password_confirmation')
                <div style="color: var(--status-error); font-size: 13px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div style="margin-top: 16px;">
            <button type="submit" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 8px;">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                Изменить пароль
            </button>
        </div>
    </form>
</div>

<!-- Danger Zone -->
<div class="card" style="border: 1px solid var(--status-error);">
    <div class="card-header">
        <h2 class="card-title" style="color: var(--status-error);">Действия</h2>
    </div>
    
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px;">
        <div>
            @if($user->is_blocked)
            <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 4px;">Разблокировать пользователя</h3>
            <p style="color: var(--text-muted); font-size: 14px;">Пользователь сможет войти в систему</p>
            @else
            <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 4px;">Заблокировать пользователя</h3>
            <p style="color: var(--text-muted); font-size: 14px;">Пользователь не сможет войти в систему</p>
            @endif
        </div>
        
        @if($user->is_blocked)
        <form action="{{ route('admin.users.unblock', $user->id) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary">✅ Разблокировать</button>
        </form>
        @else
        <form action="{{ route('admin.users.block', $user->id) }}" method="POST" onsubmit="return confirm('Заблокировать пользователя?');">
            @csrf
            <button type="submit" class="btn btn-danger">🚫 Заблокировать</button>
        </form>
        @endif
    </div>
</div>
@endsection
