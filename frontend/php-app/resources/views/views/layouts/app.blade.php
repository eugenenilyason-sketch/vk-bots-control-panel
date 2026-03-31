<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'VK Neuro-Agents')</title>
    <link rel="stylesheet" href="/styles.css">
</head>
<body>
    <nav class="sidebar">
        <div class="sidebar-content">
            <div class="menu-logo">
                <span class="menu-logo-icon">🤖</span>
                <div class="menu-logo-text">VK Neuro-Agents</div>
            </div>
            <nav class="nav-menu">
                <a href="/dashboard" class="nav-item {{ request()->is('dashboard') ? 'active' : '' }}">
                    <span class="nav-icon">📊</span><span>Dashboard</span>
                </a>
                <a href="/bots" class="nav-item {{ request()->is('bots') ? 'active' : '' }}">
                    <span class="nav-icon">🤖</span><span>Боты</span>
                </a>
                <a href="/payments" class="nav-item {{ request()->is('payments') ? 'active' : '' }}">
                    <span class="nav-icon">💳</span><span>Оплата</span>
                </a>
                <a href="/settings" class="nav-item {{ request()->is('settings') ? 'active' : '' }}">
                    <span class="nav-icon">⚙️</span><span>Настройки</span>
                </a>
                @if(auth()->check() && auth()->user()->isAdmin())
                <a href="/admin" class="nav-item {{ request()->is('admin') ? 'active' : '' }}">
                    <span class="nav-icon">🛡️</span><span>Админка</span>
                </a>
                @endif
            </nav>
            <div class="menu-footer">
                <form action="/logout" method="POST">
                    @csrf
                    <button type="submit" class="btn-logout">
                        <span>🚪</span><span>Выйти</span>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <main class="main-content">
        @yield('content')
    </main>
</body>
</html>
