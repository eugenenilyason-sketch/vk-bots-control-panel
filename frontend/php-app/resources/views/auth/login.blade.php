<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Вход - VK Neuro-Agents</title>
    <link rel="stylesheet" href="/styles.css">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh;">
    <div class="container" style="max-width: 440px; padding: 40px;">
        <div style="text-align: center; margin-bottom: 32px;">
            <div style="font-size: 48px; margin-bottom: 16px;">🤖</div>
            <h1 style="font-size: 24px; font-weight: 700; margin-bottom: 8px;">VK Neuro-Agents</h1>
            <p style="color: var(--text-secondary);">Вход в систему</p>
        </div>
        
        <form method="POST" action="/login">
            @csrf
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" required>
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
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Войти</button>
            
            <!-- VK ID Button -->
            @include('components.vkid-button')

            <p style="margin-top: 24px; text-align: center; color: var(--text-muted); font-size: 13px;">
                Нет аккаунта? <a href="/register" style="color: var(--accent-primary);">Зарегистрироваться</a>
            </p>
        </form>
