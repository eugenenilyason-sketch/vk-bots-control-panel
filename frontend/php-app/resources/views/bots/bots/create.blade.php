@extends('layouts.app')

@section('title', 'Создать бота')

@section('content')
<header class="header">
    <h1>Создать бота</h1>
    <a href="{{ route('bots.index') }}" class="btn btn-secondary">← Назад</a>
</header>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Новый бот</h2>
    </div>
    
    <form action="{{ route('bots.store') }}" method="POST">
        @csrf
        
        <div class="form-group">
            <label for="name">Название бота</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus placeholder="My Bot">
            @error('name')
            <div style="color: var(--status-error); font-size: 13px; margin-top: 4px;">{{ $message }}</div>
            @enderror
        </div>
        
        <div style="display: flex; gap: 12px; margin-top: 24px;">
            <button type="submit" class="btn btn-primary" style="flex: 1;">Создать бота</button>
            <a href="{{ route('bots.index') }}" class="btn btn-secondary" style="flex: 1;">Отмена</a>
        </div>
    </form>
</div>
@endsection
