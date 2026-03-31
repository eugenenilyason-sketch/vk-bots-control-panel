@extends('layouts.app')

@section('title', 'Админ-панель')

@section('content')
<header class="header">
    <h1>🛡️ Админ-панель</h1>
    <div class="user-info">
        <div style="text-align: right;">
            <div style="font-weight: 600;">{{ auth()->user()->username }}</div>
            <div style="font-size: 12px; color: var(--text-secondary);">
                {{ auth()->user()->role }}
            </div>
        </div>
        <div class="user-avatar">
            {{ strtoupper(substr(auth()->user()->username ?? 'A', 0, 1)) }}
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

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Пользователей</div>
        <div class="stat-value">{{ $stats['totalUsers'] ?? 0 }}</div>
        <div style="font-size: 12px; color: var(--text-muted);">
            <span style="color: var(--accent-primary);">{{ $stats['activeUsers'] ?? 0 }}</span> активных
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Ботов</div>
        <div class="stat-value">{{ $stats['totalBots'] ?? 0 }}</div>
        <div style="font-size: 12px; color: var(--text-muted);">
            <span style="color: var(--accent-primary);">{{ $stats['activeBots'] ?? 0 }}</span> активных
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Доход</div>
        <div class="stat-value">{{ number_format($stats['totalRevenue'] ?? 0, 0) }}₽</div>
        <div style="font-size: 12px; color: var(--text-muted);">
            {{ $stats['totalPayments'] ?? 0 }} платежей
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-label">В обработке</div>
        <div class="stat-value">{{ $stats['pendingPayments'] ?? 0 }}</div>
        <div style="font-size: 12px; color: var(--text-muted);">Платежей</div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Управление</h2>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
        <a href="{{ route('admin.users') }}" class="btn btn-primary" style="padding: 24px; text-decoration: none;">
            <div style="font-size: 32px; margin-bottom: 8px;">👥</div>
            <div style="font-weight: 600;">Пользователи</div>
            <div style="font-size: 12px; color: var(--text-muted);">Управление</div>
        </a>
        
        @if(auth()->user()->role === 'superadmin')
        <a href="{{ route('admin.payment-methods') }}" class="btn btn-primary" style="padding: 24px; text-decoration: none;">
            <div style="font-size: 32px; margin-bottom: 8px;">💳</div>
            <div style="font-weight: 600;">Платёжные методы</div>
            <div style="font-size: 12px; color: var(--text-muted);">Настройка</div>
        </a>
        
        <a href="{{ route('admin.settings') }}" class="btn btn-primary" style="padding: 24px; text-decoration: none;">
            <div style="font-size: 32px; margin-bottom: 8px;">⚙️</div>
            <div style="font-weight: 600;">Настройки</div>
            <div style="font-size: 12px; color: var(--text-muted);">Система</div>
        </a>
        @endif
        
        <a href="{{ route('bots.index') }}" class="btn btn-secondary" style="padding: 24px; text-decoration: none;">
            <div style="font-size: 32px; margin-bottom: 8px;">🤖</div>
            <div style="font-weight: 600;">Боты</div>
            <div style="font-size: 12px; color: var(--text-muted);">Просмотр</div>
        </a>
        
        <a href="{{ route('payments.index') }}" class="btn btn-secondary" style="padding: 24px; text-decoration: none;">
            <div style="font-size: 32px; margin-bottom: 8px;">💳</div>
            <div style="font-weight: 600;">Платежи</div>
            <div style="font-size: 12px; color: var(--text-muted);">История</div>
        </a>
    </div>
</div>

<!-- Recent Users -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Новые пользователи</h2>
        <a href="{{ route('admin.users') }}" class="btn btn-secondary">Все</a>
    </div>
    
    @if(isset($recentUsers) && $recentUsers->count() > 0)
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Имя</th>
                    <th>Роль</th>
                    <th>Баланс</th>
                    <th>Дата</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentUsers as $user)
                <tr>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->username ?? '—' }}</td>
                    <td><span class="badge badge-info">{{ $user->role }}</span></td>
                    <td>{{ number_format($user->balance, 0) }}₽</td>
                    <td style="color: var(--text-muted);">{{ $user->created_at->format('d.m.Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div style="text-align: center; padding: 40px; color: var(--text-muted);">
        <p>Нет новых пользователей</p>
    </div>
    @endif
</div>

<!-- Recent Payments -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Последние платежи</h2>
        <a href="{{ route('payments.index') }}" class="btn btn-secondary">Все</a>
    </div>
    
    @if(isset($recentPayments) && $recentPayments->count() > 0)
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Пользователь</th>
                    <th>Сумма</th>
                    <th>Метод</th>
                    <th>Статус</th>
                    <th>Дата</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentPayments as $payment)
                <tr>
                    <td>{{ $payment->user->email ?? '—' }}</td>
                    <td style="font-weight: 600;">{{ number_format($payment->amount, 0) }}₽</td>
                    <td>{{ $payment->provider ?? '—' }}</td>
                    <td>
                        <span class="badge {{ $payment->status === 'succeeded' ? 'badge-success' : ($payment->status === 'pending' ? 'badge-warning' : 'badge-error') }}">
                            {{ $payment->status }}
                        </span>
                    </td>
                    <td style="color: var(--text-muted);">{{ $payment->created_at->format('d.m.Y H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div style="text-align: center; padding: 40px; color: var(--text-muted);">
        <p>Нет платежей</p>
    </div>
    @endif
</div>
@endsection
