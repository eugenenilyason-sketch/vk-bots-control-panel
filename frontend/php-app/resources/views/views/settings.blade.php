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
            {{ strtoupper(substr(auth()->user()->username ?? 'U', 0, 1)) }}
        </div>
    </div>
</header>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Профиль</h2>
    </div>
    <div class="form-group">
        <label>Email</label>
        <input type="email" value="{{ auth()->user()->email ?? '' }}" readonly style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-tertiary);">
    </div>
    <div class="form-group">
        <label>Имя пользователя</label>
        <input type="text" value="{{ auth()->user()->username ?? '' }}" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-tertiary);">
    </div>
    <button class="btn btn-primary">Сохранить</button>
</div>
@endsection
