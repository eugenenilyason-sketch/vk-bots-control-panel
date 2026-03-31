@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<header class="header">
    <h1>Dashboard</h1>
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

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Баланс</div>
        <div class="stat-value">{{ number_format(auth()->user()->balance ?? 0, 0) }}₽</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Ботов</div>
        <div class="stat-value">{{ $botsCount ?? 0 }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Активных</div>
        <div class="stat-value">{{ $activeBots ?? 0 }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Статус</div>
        <div class="stat-value" style="font-size: 18px;">Active</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Мои боты</h2>
        <a href="/bots" class="btn btn-primary">Создать бота</a>
    </div>
    @if($botsCount > 0)
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Название</th>
                    <th>Статус</th>
                    <th>Сообщений</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bots as $bot)
                <tr>
                    <td style="font-weight: 600;">{{ $bot->name }}</td>
                    <td><span class="badge badge-success">{{ $bot->status }}</span></td>
                    <td>{{ $bot->messages_sent ?? 0 }} отпр.</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div style="text-align: center; padding: 40px; color: var(--text-muted);">
        <div style="font-size: 48px; margin-bottom: 16px;">🤖</div>
        <p>У вас пока нет ботов</p>
    </div>
    @endif
</div>
@endsection
