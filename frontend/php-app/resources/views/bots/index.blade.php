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

<!-- Create Bot Button -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Мои боты</h2>
        <a href="{{ route('bots.create') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Создать бота
        </a>
    </div>
    
    @if(isset($bots) && $bots->count() > 0)
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
                    <td>
                        <span class="badge {{ $bot->status === 'active' ? 'badge-success' : ($bot->status === 'inactive' ? 'badge-warning' : 'badge-error') }}">
                            {{ $bot->status }}
                        </span>
                    </td>
                    <td>{{ $bot->messages_sent ?? 0 }} отпр. / {{ $bot->messages_received ?? 0 }} получ.</td>
                    <td>
                        <div style="display: flex; gap: 8px;">
                            @if($bot->status === 'active')
                            <form action="{{ route('bots.stop', $bot->id) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-secondary btn-icon" title="Остановить">⏸️</button>
                            </form>
                            @else
                            <form action="{{ route('bots.start', $bot->id) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-icon" title="Запустить">▶️</button>
                            </form>
                            @endif
                            
                            <a href="{{ route('bots.edit', $bot->id) }}" class="btn btn-secondary btn-icon" title="Редактировать">✏️</a>
                            
                            <form action="{{ route('bots.destroy', $bot->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Вы уверены?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-secondary btn-icon" style="color: var(--status-error);" title="Удалить">🗑️</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div style="text-align: center; padding: 40px; color: var(--text-muted);">
        <div style="font-size: 48px; margin-bottom: 16px;">🤖</div>
        <p>У вас пока нет ботов</p>
        <a href="{{ route('bots.create') }}" class="btn btn-primary" style="margin-top: 16px;">Создать первого бота</a>
    </div>
    @endif
</div>
@endsection
