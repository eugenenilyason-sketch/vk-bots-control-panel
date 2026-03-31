@extends('layouts.app')

@section('title', 'Админка')

@section('content')
<header class="header">
    <h1>🛡️ Админ-панель</h1>
    <div class="user-info">
        <div style="text-align: right;">
            <div style="font-weight: 600;">{{ auth()->user()->username ?? 'Admin' }}</div>
            <div style="font-size: 12px; color: var(--text-secondary);">
                {{ auth()->user()->role }}
            </div>
        </div>
        <div class="user-avatar">
            {{ strtoupper(substr(auth()->user()->username ?? 'A', 0, 1)) }}
        </div>
    </div>
</header>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Пользователей</div>
        <div class="stat-value">2</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Ботов</div>
        <div class="stat-value">0</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Доход</div>
        <div class="stat-value">0₽</div>
    </div>
</div>
@endsection
