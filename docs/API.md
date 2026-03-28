# 📡 VK Neuro-Agents API Documentation

API документация для системы управления нейро-агентами ВКонтакте.

**Base URL**: `https://api.yourdomain.com`  
**Version**: 1.0  
**Auth**: Bearer JWT Token

---

## 🔐 Authentication

### Получить токен через VK

```http
POST /api/auth/vk
Content-Type: application/json

{
  "code": "authorization_code_from_vk"
}
```

**Response**:
```json
{
  "success": true,
  "data": {
    "access_token": "eyJhbGc...",
    "refresh_token": "dGhpcyBp...",
    "expires_in": 3600,
    "user": {
      "id": "uuid",
      "vk_id": 123456,
      "username": "john_doe",
      "email": "john@example.com",
      "role": "user"
    }
  }
}
```

### Refresh Token

```http
POST /api/auth/refresh
Content-Type: application/json

{
  "refresh_token": "dGhpcyBp..."
}
```

### Logout

```http
POST /api/auth/logout
Authorization: Bearer {access_token}
```

---

## 👤 User Profile

### Получить профиль

```http
GET /api/user/profile
Authorization: Bearer {access_token}
```

**Response**:
```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "vk_id": 123456,
    "username": "john_doe",
    "email": "john@example.com",
    "balance": 1500.00,
    "role": "user",
    "created_at": "2026-03-28T10:00:00Z"
  }
}
```

### Обновить профиль

```http
PUT /api/user/profile
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "username": "new_username",
  "email": "new@example.com"
}
```

---

## 🤖 Bots

### Список ботов

```http
GET /api/user/bots
Authorization: Bearer {access_token}
```

**Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "name": "My Bot",
      "vk_group_id": 123456,
      "status": "active",
      "messages_sent": 150,
      "messages_received": 200,
      "created_at": "2026-03-28T10:00:00Z"
    }
  ]
}
```

### Создать бота

```http
POST /api/user/bots
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "name": "My Bot",
  "vk_group_id": 123456,
  "vk_token": "bot_token_from_vk"
}
```

### Получить бота

```http
GET /api/user/bots/:id
Authorization: Bearer {access_token}
```

### Обновить бота

```http
PUT /api/user/bots/:id
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "name": "Updated Bot Name",
  "config": {
    "auto_response": true,
    "greeting_message": "Hello!"
  }
}
```

### Запустить бота

```http
POST /api/user/bots/:id/start
Authorization: Bearer {access_token}
```

### Остановить бота

```http
POST /api/user/bots/:id/stop
Authorization: Bearer {access_token}
```

### Удалить бота

```http
DELETE /api/user/bots/:id
Authorization: Bearer {access_token}
```

---

## 🎯 Target Audiences

### Список ЦА

```http
GET /api/user/target-audiences
Authorization: Bearer {access_token}
```

### Загрузить ЦА

```http
POST /api/user/target-audiences
Authorization: Bearer {access_token}
Content-Type: multipart/form-data

{
  "bot_id": "uuid",
  "name": "Target Audience 1",
  "description": "Description here",
  "file": "csv_or_json_file"
}
```

### Удалить ЦА

```http
DELETE /api/user/target-audiences/:id
Authorization: Bearer {access_token}
```

---

## 💰 Payments

### Доступные методы оплаты

```http
GET /api/payments/methods
Authorization: Bearer {access_token}
```

**Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "name": "yookassa",
      "display_name": "ЮKassa",
      "description": "Банковские карты, СБП, ЮMoney",
      "is_enabled": true,
      "config": {
        "min_amount": 100,
        "max_amount": 100000,
        "commission": 0.028
      },
      "icon": "yookassa"
    },
    {
      "id": "uuid",
      "name": "yoomoney_p2p",
      "display_name": "ЮMoney P2P",
      "description": "Перевод на счёт физлица",
      "is_enabled": false,
      "config": {
        "min_amount": 100,
        "max_amount": 50000,
        "commission": 0
      },
      "icon": "yoomoney"
    }
  ]
}
```

### История платежей

```http
GET /api/user/payments
Authorization: Bearer {access_token}
Query: ?page=1&limit=20&status=succeeded
```

### Создать платёж

```http
POST /api/user/payments/create
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "amount": 1000,
  "method": "yookassa"
}
```

**Response** (ЮKassa):
```json
{
  "success": true,
  "data": {
    "payment_id": "uuid",
    "confirmation_url": "https://yookassa.ru/confirm...",
    "amount": 1000,
    "status": "pending"
  }
}
```

**Response** (ЮMoney P2P):
```json
{
  "success": true,
  "data": {
    "payment_id": "uuid",
    "account_number": "41001XXXXXXXXXXXX",
    "amount": 1000,
    "status": "pending",
    "instruction": "Переведите сумму на счёт ЮMoney: 41001XXXXXXXXXXXX"
  }
}
```

### Webhook от платёжной системы

```http
POST /webhook/yookassa
Content-Type: application/json

{
  "event": "payment.succeeded",
  "payment_id": "provider_payment_id",
  "amount": 1000
}
```

### Webhook ЮMoney P2P

```http
POST /webhook/yoomoney
Content-Type: application/json

{
  "notification_type": "p2p-incoming",
  "operation_id": "1234567890",
  "amount": 1000,
  "currency": "RUB",
  "datetime": "2026-03-28T10:00:00Z",
  "sender": "41001XXXXXXXXXXXX",
  "account": "41001YYYYYYYYYYYY"
}
```

---

## 📊 Analytics

### Статистика пользователя

```http
GET /api/user/analytics
Authorization: Bearer {access_token}
Query: ?from=2026-01-01&to=2026-03-28
```

**Response**:
```json
{
  "success": true,
  "data": {
    "total_messages": 1500,
    "total_bots": 3,
    "active_bots": 2,
    "total_spent": 5000,
    "period": {
      "from": "2026-01-01",
      "to": "2026-03-28"
    }
  }
}
```

### Экспорт статистики

```http
GET /api/user/analytics/export
Authorization: Bearer {access_token}
Query: ?format=csv&from=2026-01-01&to=2026-03-28
```

---

## 🔑 API Keys

### Список ключей

```http
GET /api/user/api-keys
Authorization: Bearer {access_token}
```

### Создать ключ

```http
POST /api/user/api-keys
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "name": "My Integration",
  "permissions": ["bots:read", "bots:write"],
  "expires_at": "2027-03-28T00:00:00Z"
}
```

**Response**:
```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "name": "My Integration",
    "key": "vk_live_abc123...",
    "key_prefix": "vk_live_abc",
    "permissions": ["bots:read", "bots:write"],
    "expires_at": "2027-03-28T00:00:00Z"
  }
}
```

### Отозвать ключ

```http
DELETE /api/user/api-keys/:id
Authorization: Bearer {access_token}
```

---

## 👨‍💼 Admin Endpoints

### Список пользователей

```http
GET /api/admin/users
Authorization: Bearer {access_token}
Query: ?page=1&limit=20&role=user&search=john

Headers:
  - X-Admin-Key: {admin_api_key}
```

### Профиль пользователя

```http
GET /api/admin/users/:id
Authorization: Bearer {access_token}
Headers:
  - X-Admin-Key: {admin_api_key}
```

### Обновить пользователя

```http
PUT /api/admin/users/:id
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "role": "admin",
  "is_blocked": false,
  "balance": 1000
}

Headers:
  - X-Admin-Key: {admin_api_key}
```

### Заблокировать пользователя

```http
POST /api/admin/users/:id/block
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "reason": "Violation of terms"
}

Headers:
  - X-Admin-Key: {admin_api_key}
```

### Список платежей

```http
GET /api/admin/payments
Authorization: Bearer {access_token}
Query: ?page=1&limit=20&status=succeeded

Headers:
  - X-Admin-Key: {admin_api_key}
```

### Возврат платежа

```http
POST /api/admin/payments/refund
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "payment_id": "uuid",
  "amount": 1000,
  "reason": "User request"
}

Headers:
  - X-Admin-Key: {admin_api_key}
```

---

## 💳 Payment Methods (Admin Only)

### Список методов оплаты

```http
GET /api/admin/payment-methods
Authorization: Bearer {access_token}

Headers:
  - X-Admin-Key: {admin_api_key}
```

**Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "name": "yookassa",
      "display_name": "ЮKassa",
      "is_enabled": true,
      "is_admin_only": false,
      "config": {...},
      "updated_at": "2026-03-28T10:00:00Z"
    }
  ]
}
```

### Обновить метод оплаты

```http
PUT /api/admin/payment-methods/:id
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "is_enabled": false,
  "config": {
    "min_amount": 200,
    "max_amount": 50000
  }
}

Headers:
  - X-Admin-Key: {admin_api_key}
```

### ЮMoney P2P настройки

```http
GET /api/admin/yoomoney-p2p
Authorization: Bearer {access_token}

Headers:
  - X-Admin-Key: {admin_api_key}
```

### Добавить проверенного пользователя ЮMoney

```http
POST /api/admin/yoomoney-p2p
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "account_number": "41001XXXXXXXXXXXX",
  "verified_user_vk_id": 12345678,
  "verified_user_name": "Иван Иванов",
  "is_verified": true,
  "is_active": true,
  "api_key": "yoomoney_api_key"
}

Headers:
  - X-Admin-Key: {admin_api_key}
```

### Обновить ЮMoney P2P

```http
PUT /api/admin/yoomoney-p2p/:id
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "is_active": false,
  "is_verified": true
}

Headers:
  - X-Admin-Key: {admin_api_key}
```

### Список ботов

```http
GET /api/admin/bots
Authorization: Bearer {access_token}
Query: ?page=1&limit=20&status=active

Headers:
  - X-Admin-Key: {admin_api_key}
```

### Перезапустить бота

```http
POST /api/admin/bots/:id/restart
Authorization: Bearer {access_token}

Headers:
  - X-Admin-Key: {admin_api_key}
```

### Общая статистика

```http
GET /api/admin/analytics
Authorization: Bearer {access_token}
Query: ?from=2026-01-01&to=2026-03-28

Headers:
  - X-Admin-Key: {admin_api_key}
```

**Response**:
```json
{
  "success": true,
  "data": {
    "total_users": 1500,
    "active_users": 800,
    "total_bots": 300,
    "active_bots": 200,
    "total_revenue": 500000,
    "total_messages": 50000
  }
}
```

### Генерация API ключа

```http
POST /api/admin/api-keys
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "name": "n8n Integration",
  "service": "n8n",
  "permissions": ["*"]
}

Headers:
  - X-Admin-Key: {admin_api_key}
```

### Деплой проекта

```http
POST /api/admin/deploy
Authorization: Bearer {access_token}

Headers:
  - X-Admin-Key: {admin_api_key}
```

### Создание бэкапа

```http
POST /api/admin/backup
Authorization: Bearer {access_token}

Headers:
  - X-Admin-Key: {admin_api_key}
```

---

## 🔌 Webhooks

### VK Bot Webhook

```http
POST /webhook/vk
Content-Type: application/json

{
  "type": "message_new",
  "object": {
    "message": {
      "from_id": 123456,
      "text": "Hello bot!",
      "peer_id": 123456
    }
  },
  "group_id": 789012
}
```

### n8n Workflow Webhook

```http
POST /webhook/n8n/:workflow_id
Content-Type: application/json

{
  "event": "bot.message.received",
  "data": {
    "bot_id": "uuid",
    "message": "Hello"
  }
}
```

---

## ❌ Error Responses

### 400 Bad Request

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Invalid input data",
    "details": [
      {
        "field": "email",
        "message": "Invalid email format"
      }
    ]
  }
}
```

### 401 Unauthorized

```json
{
  "success": false,
  "error": {
    "code": "UNAUTHORIZED",
    "message": "Invalid or expired token"
  }
}
```

### 403 Forbidden

```json
{
  "success": false,
  "error": {
    "code": "FORBIDDEN",
    "message": "Insufficient permissions"
  }
}
```

### 404 Not Found

```json
{
  "success": false,
  "error": {
    "code": "NOT_FOUND",
    "message": "Resource not found"
  }
}
```

### 429 Too Many Requests

```json
{
  "success": false,
  "error": {
    "code": "RATE_LIMIT_EXCEEDED",
    "message": "Too many requests. Try again in 60 seconds."
  }
}
```

### 500 Internal Server Error

```json
{
  "success": false,
  "error": {
    "code": "INTERNAL_ERROR",
    "message": "Something went wrong",
    "request_id": "req_abc123"
  }
}
```

---

## 📋 Rate Limits

| Endpoint | Limit |
|----------|-------|
| Auth endpoints | 10 requests/minute |
| User endpoints | 100 requests/minute |
| Bot endpoints | 60 requests/minute |
| Admin endpoints | 30 requests/minute |
| Webhooks | 1000 requests/minute |

---

## 🔗 SDK Examples

### JavaScript/Node.js

```javascript
const api = {
  baseURL: 'https://api.yourdomain.com',
  token: 'your_access_token'
};

// Получить список ботов
async function getBots() {
  const response = await fetch(`${api.baseURL}/api/user/bots`, {
    headers: {
      'Authorization': `Bearer ${api.token}`
    }
  });
  return await response.json();
}

// Создать бота
async function createBot(name, vkGroupId, vkToken) {
  const response = await fetch(`${api.baseURL}/api/user/bots`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${api.token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ name, vk_group_id: vkGroupId, vk_token: vkToken })
  });
  return await response.json();
}
```

### Python

```python
import requests

API_BASE = 'https://api.yourdomain.com'
TOKEN = 'your_access_token'

def get_bots():
    response = requests.get(
        f'{API_BASE}/api/user/bots',
        headers={'Authorization': f'Bearer {TOKEN}'}
    )
    return response.json()

def create_bot(name, vk_group_id, vk_token):
    response = requests.post(
        f'{API_BASE}/api/user/bots',
        headers={
            'Authorization': f'Bearer {TOKEN}',
            'Content-Type': 'application/json'
        },
        json={'name': name, 'vk_group_id': vk_group_id, 'vk_token': vk_token}
    )
    return response.json()
```

---

*Версия API: 1.0 | Последнее обновление: 28 марта 2026*
