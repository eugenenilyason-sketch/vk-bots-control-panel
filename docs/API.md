# 📡 VK Neuro-Agents API Documentation

API документация для системы управления нейро-агентами ВКонтакте (Laravel 11).

**Base URL**: `https://yourdomain.com`  
**Version**: 2.0.0  
**Auth**: Laravel Session (Cookie)

---

## 🔐 Аутентификация

### Вход (Email/Пароль)

```http
POST /login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response (Success)**:
```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "email": "user@example.com",
    "username": "username",
    "role": "user"
  }
}
```

**Response (Error)**:
```json
{
  "success": false,
  "errors": {
    "email": ["Пользователь не найден"]
  }
}
```

---

### Регистрация

```http
POST /register
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "username": "username"
}
```

**Rules**:
- `email`: required, unique
- `password`: required, min: 6
- `username`: optional

---

### Выход

```http
POST /logout
```

---

## 👤 Dashboard

### Главная страница

```http
GET /dashboard
```

**Response Data**:
- Баланс пользователя
- Количество ботов
- Активные боты
- Последние платежи

---

## 🤖 Боты (CRUD)

### Список ботов

```http
GET /bots
```

**Response**: Список ботов пользователя

---

### Создать бота

```http
GET /bots/create
POST /bots

{
  "name": "My Bot"
}
```

**Rules**:
- `name`: required, min: 3, max: 255

---

### Редактировать бота

```http
GET /bots/{id}/edit
PUT /bots/{id}

{
  "name": "Updated Bot",
  "status": "active"
}
```

---

### Удалить бота

```http
DELETE /bots/{id}
```

---

### Запустить/Остановить бота

```http
POST /bots/{id}/start
POST /bots/{id}/stop
```

---

## 💳 Платежи

### История платежей

```http
GET /payments
GET /payments?status=succeeded
```

**Query Parameters**:
- `status`: succeeded, pending, failed
- `type`: deposit, subscription

---

### Пополнить баланс

```http
GET /payments/create
POST /payments

{
  "amount": 1000,
  "method": "yoomoney"
}
```

**Rules**:
- `amount`: required, min: 100, max: 100000
- `method`: required, exists in payment_methods

---

## ⚙️ Настройки

### Профиль пользователя

```http
GET /settings
POST /settings

{
  "email": "new@example.com",
  "username": "new_username"
}
```

---

### Смена пароля

```http
POST /settings/password

{
  "current_password": "old_password",
  "password": "new_password",
  "password_confirmation": "new_password"
}
```

**Rules**:
- `current_password`: required
- `password`: required, min: 6, confirmed

---

## 🛡️ Админ-панель (Superadmin Only)

### Главная админки

```http
GET /admin
```

**Response Data**:
- Всего пользователей
- Активных пользователей
- Всего ботов
- Доход (сумма платежей)

---

### Пользователи

```http
GET /admin/users
GET /admin/users?search=email@example.com
GET /admin/users?role=admin
GET /admin/users?status=active
```

---

### Редактировать пользователя

```http
GET /admin/users/{id}/edit
POST /admin/users/{id}

{
  "role": "admin",
  "balance": 5000,
  "is_active": true,
  "is_blocked": false
}
```

---

### Блокировка/Разблокировка

```http
POST /admin/users/{id}/block
POST /admin/users/{id}/unblock
```

---

### Платёжные методы

```http
GET /admin/payment-methods
GET /admin/payment-methods/create
POST /admin/payment-methods

{
  "name": "paypal",
  "display_name": "PayPal",
  "type": "p2p",
  "icon": "🅿️",
  "is_enabled": true
}
```

---

### Обновить платёжный метод

```http
POST /admin/payment-methods/{id}

{
  "display_name": "PayPal Pro",
  "type": "card",
  "is_enabled": true,
  "api_key": "pk_test_...",
  "api_secret": "sk_test_...",
  "settings": {
    "api_url": "https://api.paypal.com",
    "timeout": 30,
    "currency": "USD"
  }
}
```

---

### Удалить платёжный метод

```http
POST /admin/payment-methods/{id}/delete
```

---

### Настройки системы

```http
GET /admin/settings
POST /admin/settings

{
  "registration_enabled": true
}
```

**Settings**:
- `registration_enabled`: boolean (разрешить регистрацию)

---

## 📊 Models

### User

```json
{
  "id": "uuid",
  "vk_id": 123456,
  "email": "user@example.com",
  "username": "username",
  "role": "user|admin|superadmin",
  "balance": 1500.00,
  "is_active": true,
  "is_blocked": false,
  "created_at": "2026-03-30T10:00:00Z",
  "updated_at": "2026-03-30T10:00:00Z"
}
```

### Bot

```json
{
  "id": "uuid",
  "user_id": "uuid",
  "name": "My Bot",
  "status": "active|inactive|blocked",
  "messages_sent": 100,
  "messages_received": 50,
  "created_at": "2026-03-30T10:00:00Z"
}
```

### Payment

```json
{
  "id": "uuid",
  "user_id": "uuid",
  "amount": 1000.00,
  "currency": "RUB",
  "status": "pending|succeeded|failed",
  "provider": "YooMoney P2P",
  "type": "deposit",
  "created_at": "2026-03-30T10:00:00Z"
}
```

### PaymentMethod

```json
{
  "name": "yoomoney",
  "display_name": "YooMoney P2P",
  "type": "p2p|card|qr|crypto",
  "icon": "💰",
  "is_enabled": true,
  "min_amount": 100,
  "max_amount": 100000,
  "settings": {
    "api_url": "https://api.yoomoney.ru",
    "timeout": 30,
    "currency": "RUB"
  }
}
```

---

## 🔒 Безопасность

### CSRF Protection

Все POST, PUT, DELETE запросы требуют CSRF токен:

```html
<meta name="csrf-token" content="{{ csrf_token() }}">
```

```javascript
headers: {
  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
}
```

### Session Auth

Laravel использует cookie сессии:
- `laravel_session`
- `laravel_session` подписан и зашифрован

### Middleware

| Middleware | Описание |
|------------|----------|
| `auth` | Требует аутентификации |
| `admin` | Требует прав администратора |

---

## 📝 Лицензия

MIT

---

*Версия: 2.0.0 | Дата: 31 марта 2026*  
*Фреймворк: Laravel 11 + PHP 8.4*
