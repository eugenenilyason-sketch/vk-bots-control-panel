@extends('layouts.app')

@section('title', 'Оплата')

@section('content')
<header class="header">
    <h1>Оплата</h1>
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

<!-- Stats -->
<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-label">Всего пополнено</div>
        <div class="stat-value">{{ number_format($totalSpent ?? 0, 0) }}₽</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">В обработке</div>
        <div class="stat-value">{{ $pendingCount ?? 0 }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Текущий баланс</div>
        <div class="stat-value">{{ number_format(auth()->user()->balance ?? 0, 0) }}₽</div>
    </div>
</div>

<!-- Actions -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Пополнить баланс</h2>
        <a href="{{ route('payments.create') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Пополнить
        </a>
    </div>
    
    <div style="text-align: center; padding: 30px; color: var(--text-muted);">
        <div style="font-size: 48px; margin-bottom: 16px;">💳</div>
        <p>Выберите сумму и способ оплаты для пополнения баланса</p>
    </div>
</div>

<!-- History -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">История платежей</h2>
        
        <!-- Filters -->
        <form action="{{ route('payments.index') }}" method="GET" style="display: flex; gap: 8px;">
            <select name="status" class="btn btn-secondary" style="padding: 8px 12px;">
                <option value="">Все статусы</option>
                <option value="succeeded" {{ request('status') === 'succeeded' ? 'selected' : '' }}>Успешно</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>В обработке</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Ошибка</option>
            </select>
            <button type="submit" class="btn btn-secondary">Фильтр</button>
            @if(request('status'))
            <a href="{{ route('payments.index') }}" class="btn btn-secondary">Сброс</a>
            @endif
        </form>
    </div>
    
    @if(isset($payments) && $payments->count() > 0)
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Сумма</th>
                    <th>Метод</th>
                    <th>Тип</th>
                    <th>Статус</th>
                    <th>Дата</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $payment)
                <tr>
                    <td style="font-weight: 600;">{{ number_format($payment->amount, 0) }}₽</td>
                    <td>{{ $payment->provider ?? '—' }}</td>
                    <td>
                        <span class="badge badge-info">{{ $payment->type ?? 'deposit' }}</span>
                    </td>
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
    
    <!-- Pagination -->
    @if($payments->hasPages())
    <div style="margin-top: 24px;">
        {{ $payments->links() }}
    </div>
    @endif
    @else
    <div style="text-align: center; padding: 40px; color: var(--text-muted);">
        <div style="font-size: 48px; margin-bottom: 16px;">📋</div>
        <p>История платежей пуста</p>
        <a href="{{ route('payments.create') }}" class="btn btn-primary" style="margin-top: 16px;">Пополнить баланс</a>
    </div>
    @endif
</div>
@endsection
