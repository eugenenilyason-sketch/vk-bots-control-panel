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
            <h1 style="font-size: 24px; font-weight: 700; margin-bottom: 8px;">VK Neuro-Agents</h1>
            <p style="color: var(--text-secondary);">Регистрация</p>
        </div>
        
        <form method="POST" action="/register">
            @csrf
            
            <div class="form-group">
                <label for="name">Имя пользователя</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required>
            </div>
            
            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" required minlength="6">
            </div>
            
            <div class="form-group">
                <label for="password_confirmation">Подтверждение пароля</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required minlength="6">
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
            Уже есть аккаунт? <a href="/login" style="color: var(--accent-primary);">Войти</a>
        </p>
    </div>
</body>
</html>
