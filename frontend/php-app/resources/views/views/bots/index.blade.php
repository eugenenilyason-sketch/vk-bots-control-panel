@extends('layouts.app')

@section('title', 'Боты')

@section('content')
<header class="header">
    <h1>Боты</h1>
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
        <h2 class="card-title">Мои боты</h2>
        <button class="btn btn-primary" onclick="alert('Создание бота')">+ Создать бота</button>
    </div>
    @if($bots ?? false)
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Название</th>
                    <th>Статус</th>
                    <th>Сообщений</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bots as $bot)
                <tr>
                    <td style="font-weight: 600;">{{ $bot->name }}</td>
                    <td><span class="badge badge-success">{{ $bot->status }}</span></td>
                    <td>{{ $bot->messages_sent ?? 0 }}</td>
                    <td><button class="btn btn-secondary btn-icon">✏️</button></td>
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
