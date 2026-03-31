@extends('layouts.app')

@section('title', 'Платёжные методы')

@section('content')
<header class="header">
    <h1>💳 Платёжные методы</h1>
    <div style="display: flex; gap: 12px;">
        <a href="{{ route('admin') }}" class="btn btn-secondary">← Админка</a>
        <a href="{{ route('admin.users') }}" class="btn btn-secondary">Пользователи</a>
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

<!-- Header with Add Button -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Настройки платёжных методов</h2>
        <a href="{{ route('admin.payment-methods.create') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 8px;">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Добавить метод
        </a>
    </div>
</div>
    
    @if(isset($methods) && count($methods) > 0)
    <div style="display: flex; flex-direction: column; gap: 16px;">
        @foreach($methods as $method)
        <div style="background: var(--bg-tertiary); padding: 20px; border-radius: 8px; border: 2px solid {{ $method->is_enabled ? 'var(--accent-primary)' : 'var(--border-color)' }};">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="font-size: 32px;">{{ $method->icon ?? '💳' }}</div>
                    <div>
                        <h3 style="font-size: 18px; font-weight: 600; margin-bottom: 4px;">{{ $method->display_name }}</h3>
                        <div style="font-size: 12px; color: var(--text-muted);">
                            Тип: <span class="badge badge-info">{{ $method->type }}</span>
                            @if($method->api_key_encrypted ?? null)
                            <span class="badge badge-success" style="margin-left: 8px;">🔑 API настроен</span>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div>
                    @if($method->is_enabled)
                    <span class="badge badge-success">✅ Активен</span>
                    @else
                    <span class="badge badge-warning">⏸️ Отключен</span>
                    @endif
                </div>
                
                <form action="{{ route('admin.payment-methods.delete', $method->name) }}" method="POST" style="display: inline;" onsubmit="return confirm('Вы уверены что хотите удалить этот метод?');">
                    @csrf
                    <button type="submit" class="btn btn-danger" style="padding: 8px 16px;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                    </button>
                </form>
            </div>
            
            <!-- Edit Form -->
            <form action="{{ route('admin.payment-methods.update', $method->name) }}" method="POST" style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border-color);">
                @csrf
                
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 12px; align-items: end;">
                    <div class="form-group" style="margin: 0;">
                        <label for="display_name_{{ $method->name }}">Отображение</label>
                        <input type="text" id="display_name_{{ $method->name }}" name="display_name" value="{{ old('display_name', $method->display_name) }}" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-secondary); color: var(--text-primary);">
                    </div>
                    
                    <div class="form-group" style="margin: 0;">
                        <label for="type_{{ $method->name }}">Тип</label>
                        <select id="type_{{ $method->name }}" name="type" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-secondary); color: var(--text-primary);">
                            <option value="p2p" {{ old('type', $method->type) === 'p2p' ? 'selected' : '' }}>P2P</option>
                            <option value="card" {{ old('type', $method->type) === 'card' ? 'selected' : '' }}>Карта</option>
                            <option value="qr" {{ old('type', $method->type) === 'qr' ? 'selected' : '' }}>QR (СБП)</option>
                            <option value="crypto" {{ old('type', $method->type) === 'crypto' ? 'selected' : '' }}>Крипта</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin: 0;">
                        <label for="icon_{{ $method->name }}">Иконка</label>
                        <input type="text" id="icon_{{ $method->name }}" name="icon" value="{{ old('icon', $method->icon ?? '💳') }}" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-secondary); color: var(--text-primary);">
                    </div>
                    
                    <div style="display: flex; gap: 8px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; white-space: nowrap;">
                            <input type="checkbox" name="is_enabled" value="1" {{ old('is_enabled', $method->is_enabled) ? 'checked' : '' }} style="width: 18px; height: 18px;">
                            <span>Включен</span>
                        </label>
                        
                        <button type="submit" class="btn btn-primary">💾 Сохранить</button>
                    </div>
                </div>
                
                <!-- API Keys Section -->
                <div style="margin-top: 16px; padding: 16px; background: var(--bg-secondary); border-radius: 6px;">
                    <h4 style="margin-bottom: 12px; font-size: 14px; color: var(--text-secondary);">🔐 API настройки (зашифровано)</h4>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;">
                        <div class="form-group" style="margin: 0;">
                            <label for="api_key_{{ $method->name }}">API Key</label>
                            <input type="text" id="api_key_{{ $method->name }}" name="api_key" placeholder="Введите API ключ" value="{{ old('api_key') }}" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-tertiary); color: var(--text-primary);">
                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Оставьте пустым чтобы не менять</div>
                        </div>
                        
                        <div class="form-group" style="margin: 0;">
                            <label for="api_secret_{{ $method->name }}">API Secret</label>
                            <input type="password" id="api_secret_{{ $method->name }}" name="api_secret" placeholder="Введите API секрет" value="{{ old('api_secret') }}" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-tertiary); color: var(--text-primary);">
                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Оставьте пустым чтобы не менять</div>
                        </div>
                        
                        <div class="form-group" style="margin: 0;">
                            <label for="merchant_id_{{ $method->name }}">Merchant ID</label>
                            <input type="text" id="merchant_id_{{ $method->name }}" name="merchant_id" placeholder="Введите Merchant ID" value="{{ old('merchant_id', $method->merchant_id ?? '') }}" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-tertiary); color: var(--text-primary);">
                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Оставьте пустым чтобы не менять</div>
                        </div>
                    </div>
                </div>
                
                <!-- Advanced Settings Section -->
                <div style="margin-top: 16px; padding: 16px; background: var(--bg-secondary); border-radius: 6px;">
                    <h4 style="margin-bottom: 12px; font-size: 14px; color: var(--text-secondary);">⚙️ Расширенные настройки</h4>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div class="form-group" style="margin: 0;">
                            <label for="api_url_{{ $method->name }}">API URL</label>
                            <input type="url" id="api_url_{{ $method->name }}" name="settings[api_url]" value="{{ old('settings.api_url', $method->settings['api_url'] ?? '') }}" placeholder="https://api.example.com" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-tertiary); color: var(--text-primary);">
                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Адрес API сервиса</div>
                        </div>
                        
                        <div class="form-group" style="margin: 0;">
                            <label for="webhook_url_{{ $method->name }}">Webhook URL</label>
                            <input type="url" id="webhook_url_{{ $method->name }}" name="settings[webhook_url]" value="{{ old('settings.webhook_url', $method->settings['webhook_url'] ?? '') }}" placeholder="https://yourdomain.com/webhook/{{ $method->name }}" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-tertiary); color: var(--text-primary);">
                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">URL для уведомлений</div>
                        </div>
                        
                        <div class="form-group" style="margin: 0;">
                            <label for="timeout_{{ $method->name }}">Таймаут (сек)</label>
                            <input type="number" id="timeout_{{ $method->name }}" name="settings[timeout]" value="{{ old('settings.timeout', $method->settings['timeout'] ?? 30) }}" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-tertiary); color: var(--text-primary);">
                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Время ожидания ответа</div>
                        </div>
                        
                        <div class="form-group" style="margin: 0;">
                            <label for="currency_{{ $method->name }}">Валюта</label>
                            <select id="currency_{{ $method->name }}" name="settings[currency]" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-tertiary); color: var(--text-primary);">
                                <option value="RUB" {{ old('settings.currency', $method->settings['currency'] ?? 'RUB') === 'RUB' ? 'selected' : '' }}>RUB</option>
                                <option value="USD" {{ old('settings.currency', $method->settings['currency'] ?? 'USD') === 'USD' ? 'selected' : '' }}>USD</option>
                                <option value="EUR" {{ old('settings.currency', $method->settings['currency'] ?? 'EUR') === 'EUR' ? 'selected' : '' }}>EUR</option>
                                <option value="KZT" {{ old('settings.currency', $method->settings['currency'] ?? 'KZT') === 'KZT' ? 'selected' : '' }}>KZT</option>
                            </select>
                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Валюта платежей</div>
                        </div>
                        
                        <div class="form-group" style="margin: 0;">
                            <label for="test_mode_{{ $method->name }}">Тестовый режим</label>
                            <select id="test_mode_{{ $method->name }}" name="settings[test_mode]" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-tertiary); color: var(--text-primary);">
                                <option value="0" {{ old('settings.test_mode', $method->settings['test_mode'] ?? 0) == 0 ? 'selected' : '' }}>Боевой режим</option>
                                <option value="1" {{ old('settings.test_mode', $method->settings['test_mode'] ?? 0) == 1 ? 'selected' : '' }}>Тестовый режим</option>
                            </select>
                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Режим разработки</div>
                        </div>
                        
                        <div class="form-group" style="margin: 0;">
                            <label for="description_template_{{ $method->name }}">Шаблон описания</label>
                            <input type="text" id="description_template_{{ $method->name }}" name="settings[description_template]" value="{{ old('settings.description_template', $method->settings['description_template'] ?? 'Пополнение баланса') }}" placeholder="Пополнение баланса" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-tertiary); color: var(--text-primary);">
                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">{user_id} - ID пользователя</div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        @endforeach
    </div>
    @else
    <div style="text-align: center; padding: 40px; color: var(--text-muted);">
        <div style="font-size: 48px; margin-bottom: 16px;">💳</div>
        <p>Платёжные методы не настроены</p>
    </div>
    @endif
</div>

<!-- Info -->
<div class="card" style="border: 1px solid var(--accent-blue);">
    <div class="card-header">
        <h2 class="card-title" style="color: var(--accent-blue);">ℹ️ Информация</h2>
    </div>
    
    <div style="padding: 16px; color: var(--text-secondary); line-height: 1.8;">
        <p><strong>Типы платёжных методов:</strong></p>
        <ul style="margin-left: 20px; margin-bottom: 16px;">
            <li><strong>P2P</strong> — Прямой перевод между пользователями (YooMoney P2P)</li>
            <li><strong>Карта</strong> — Оплата банковской картой через эквайринг</li>
            <li><strong>QR (СБП)</strong> — Оплата через Систему Быстрых Платежей</li>
            <li><strong>Крипта</strong> — Оплата криптовалютой (USDT, BTC, ETH)</li>
        </ul>
        
        <p><strong>Безопасность:</strong></p>
        <ul style="margin-left: 20px;">
            <li>🔐 API ключи шифруются перед сохранением в БД</li>
            <li>🔐 Только суперадмин может изменять настройки</li>
            <li>🔐 При просмотре ключи не отображаются</li>
        </ul>
    </div>
</div>
@endsection
