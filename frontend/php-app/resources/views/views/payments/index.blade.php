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
            {{ strtoupper(substr(auth()->user()->username ?? 'U', 0, 1)) }}
        </div>
    </div>
</header>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Пополнить баланс</h2>
    </div>
    <div style="text-align: center; padding: 40px; color: var(--text-muted);">
        <div style="font-size: 48px; margin-bottom: 16px;">💳</div>
        <p>Способы оплаты будут доступны позже</p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">История платежей</h2>
    </div>
    <div style="text-align: center; padding: 40px; color: var(--text-muted);">
        <p>История пуста</p>
    </div>
</div>
@endsection
