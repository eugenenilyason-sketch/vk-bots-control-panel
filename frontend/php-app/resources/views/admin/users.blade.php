@extends('layouts.app')

@section('title', 'Пользователи')

@section('content')
<header class="header">
    <h1>👥 Пользователи</h1>
    <a href="{{ route('admin') }}" class="btn btn-secondary">← Назад</a>
</header>

<!-- Filters -->
<div class="card">
    <form action="{{ route('admin.users') }}" method="GET" style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 12px;">
        <div>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Поиск по email или имени" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-tertiary);">
        </div>
        <div>
            <select name="role" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-tertiary);">
                <option value="">Все роли</option>
                <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>Пользователь</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Админ</option>
                <option value="superadmin" {{ request('role') === 'superadmin' ? 'selected' : '' }}>Суперадмин</option>
            </select>
        </div>
        <div>
            <select name="status" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-tertiary);">
                <option value="">Все статусы</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Активен</option>
                <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Заблокирован</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Фильтр</button>
    </form>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Список пользователей ({{ $users->total() }})</h2>
    </div>
    
    @if($users->count() > 0)
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Имя</th>
                    <th>Роль</th>
                    <th>Баланс</th>
                    <th>Статус</th>
                    <th>Дата</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->username ?? '—' }}</td>
                    <td><span class="badge badge-info">{{ $user->role }}</span></td>
                    <td>{{ number_format($user->balance, 0) }}₽</td>
                    <td>
                        @if($user->is_blocked)
                        <span class="badge badge-error">Заблокирован</span>
                        @elseif($user->is_active)
                        <span class="badge badge-success">Активен</span>
                        @else
                        <span class="badge badge-warning">Неактивен</span>
                        @endif
                    </td>
                    <td style="color: var(--text-muted);">{{ $user->created_at->format('d.m.Y') }}</td>
                    <td>
                        <div style="display: flex; gap: 8px;">
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-secondary btn-icon" title="Редактировать">✏️</a>
                            
                            @if($user->is_blocked)
                            <form action="{{ route('admin.users.unblock', $user->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-icon" title="Разблокировать">✅</button>
                            </form>
                            @else
                            <form action="{{ route('admin.users.block', $user->id) }}" method="POST" onsubmit="return confirm('Заблокировать пользователя?');">
                                @csrf
                                <button type="submit" class="btn btn-secondary btn-icon" style="color: var(--status-error);" title="Заблокировать">🚫</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    @if($users->hasPages())
    <div style="margin-top: 24px;">
        {{ $users->links() }}
    </div>
    @endif
    @else
    <div style="text-align: center; padding: 40px; color: var(--text-muted);">
        <div style="font-size: 48px; margin-bottom: 16px;">🔍</div>
        <p>Пользователи не найдены</p>
    </div>
    @endif
</div>
@endsection
