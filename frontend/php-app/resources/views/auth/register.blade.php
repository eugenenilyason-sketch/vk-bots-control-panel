<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация - VK Neuro-Agents</title>
    <link rel="stylesheet" href="/styles.css">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh;">
    <div class="container" style="max-width: 440px; padding: 40px;">
        <div style="text-align: center; margin-bottom: 32px;">
            <div style="font-size: 48px; margin-bottom: 16px;">🤖</div>
            <h1 style="font-size: 24px; font-weight: 700; margin-bottom: 8px;">Регистрация</h1>
            <p style="color: var(--text-secondary);">Создание аккаунта</p>
        </div>

        @if(isset($registrationDisabled) && $registrationDisabled)
        <div class="status error" style="margin-bottom: 24px;">
            <strong>⛔ Регистрация закрыта</strong>
            <p style="margin: 8px 0 0 0;">{{ $message }}</p>
        </div>
        
        <div style="text-align: center; margin-top: 24px;">
            <a href="/" class="btn btn-primary" style="display: inline-block; text-decoration: none;">
                ← Вернуться ко входу
            </a>
        </div>
        @else
        <form method="POST" action="/register">
            @csrf

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
                @error('email')
                <div style="color: var(--status-error); font-size: 13px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="username">Имя пользователя (необязательно)</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}">
                @error('username')
                <div style="color: var(--status-error); font-size: 13px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" required minlength="6">
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Минимум 6 символов</div>
                @error('password')
                <div style="color: var(--status-error); font-size: 13px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Подтверждение пароля</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required minlength="6">
                @error('password_confirmation')
                <div style="color: var(--status-error); font-size: 13px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            @if ($errors->any())
            <div class="status error">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <button type="submit" class="btn btn-primary" style="width: 100%;">Зарегистрироваться</button>
        </form>

        <p style="margin-top: 24px; text-align: center; color: var(--text-muted); font-size: 13px;">
            Уже есть аккаунт? <a href="/" style="color: var(--accent-primary);">Войти</a>
        </p>
        @endif
    </div>
</body>
</html>
