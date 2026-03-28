# 💳 ЮMoney P2P Integration Guide

Руководство по интеграции платежей через ЮMoney P2P (переводы на счёт физлица).

---

## 📋 Оглавление

1. [Обзор](#обзор)
2. [Настройка ЮMoney](#настройка-юmoney)
3. [Подключение проверенного пользователя](#подключение-проверенного-пользователя)
4. [Управление методами оплаты](#управление-методами-оплаты)
5. [API Endpoints](#api-endpoints)
6. [Безопасность](#безопасность)
7. [Troubleshooting](#troubleshooting)

---

## 📖 Обзор

### Что такое ЮMoney P2P?

**ЮMoney P2P** — это приём платежей через переводы на личный счёт физлица в ЮMoney.

**Преимущества**:
- ✅ Меньшая комиссия (0% для P2P переводов)
- ✅ Быстрое зачисление (мгновенно)
- ✅ Простая интеграция
- ✅ Не требуется ИП/ООО (для небольших сумм)

**Недостатки**:
- ⚠️ Лимиты на переводы (до 50,000₽ в месяц для верифицированных)
- ⚠️ Требуется доверенное лицо (проверенный пользователь)
- ⚠️ Ручная проверка платежей (в некоторых случаях)

### Схема работы

```
┌─────────────┐         ┌──────────────┐         ┌─────────────┐
│ Пользователь│         │  Проверенный │         │   Система   │
│   (клиент)  │────────►│  пользователь│────────►│   (админка) │
│             │  Перевод │   (физлицо)  │  Webhook│             │
└─────────────┘         └──────────────┘         └─────────────┘
                              │
                              ▼
                        ┌─────────────┐
                        │   ЮMoney    │
                        │    API      │
                        └─────────────┘
```

---

## 🔧 Настройка ЮMoney

### Шаг 1: Регистрация счёта ЮMoney

1. Перейдите на [ЮMoney](https://yoomoney.ru/)
2. Зарегистрируйтесь или войдите
3. Пройдите идентификацию (паспортные данные)
4. Получите номер счёта (формат: `41001XXXXXXXXXXXX`)

### Шаг 2: Получение API ключа

1. Откройте [ЮMoney API](https://yoomoney.ru/myservices/transfer-api)
2. Нажмите **"Создать новый ключ"**
3. Выберите права:
   - ✅ `account-info` — информация о счёте
   - ✅ `operation-history` — история операций
   - ✅ `incoming-transfers` — входящие переводы
4. Скопируйте API ключ (начинается с `41001...`)

### Шаг 3: Настройка webhook

1. В личном кабинете ЮMoney перейдите в **Настройки → Уведомления**
2. Укажите webhook URL: `https://api.yourdomain.com/webhook/yoomoney`
3. Скопируйте секрет webhook (для проверки подписи)

---

## 👤 Подключение проверенного пользователя

### Кто такой проверенный пользователь?

**Проверенный пользователь** — это физическое лицо, на счёт которого будут поступать платежи.

**Требования**:
- ✅ Гражданин РФ
- ✅ Прошёл полную идентификацию в ЮMoney
- ✅ Имеет подтверждённый номер телефона
- ✅ Доверенное лицо администратора системы

### Регистрация в системе

#### Через админ-панель

1. Войдите как суперадмин
2. Перейдите в **Настройки → Платёжные методы → ЮMoney P2P**
3. Нажмите **"Добавить проверенного пользователя"**
4. Заполните форму:

```json
{
  "account_number": "41001XXXXXXXXXXXX",
  "verified_user_vk_id": 12345678,
  "verified_user_name": "Иванов Иван Иванович",
  "is_verified": true,
  "is_active": true,
  "api_key": "yoomoney_api_key_here"
}
```

5. Сохраните

#### Через API

```http
POST /api/admin/yoomoney-p2p
Authorization: Bearer {admin_access_token}
X-Admin-Key: {admin_api_key}
Content-Type: application/json

{
  "account_number": "41001XXXXXXXXXXXX",
  "verified_user_vk_id": 12345678,
  "verified_user_name": "Иванов Иван Иванович",
  "is_verified": true,
  "is_active": true,
  "api_key": "yoomoney_api_key_here"
}
```

### Проверка подключения

```http
GET /api/admin/yoomoney-p2p
Authorization: Bearer {admin_access_token}
X-Admin-Key: {admin_api_key}
```

**Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "account_number": "41001XXXXXXXXXXXX",
      "verified_user_vk_id": 12345678,
      "verified_user_name": "Иванов Иван Иванович",
      "is_verified": true,
      "is_active": true,
      "last_payment_check": "2026-03-28T10:00:00Z"
    }
  ]
}
```

---

## 🎛 Управление методами оплаты

### Включение/выключение методов

Админ может включать или выключать доступные методы оплаты для пользователей.

#### Через админ-панель

1. Войдите как админ
2. Перейдите в **Настройки → Платёжные методы**
3. Переключите тумблер рядом с методом

#### Через API

```http
PUT /api/admin/payment-methods/yoomoney_p2p
Authorization: Bearer {admin_access_token}
X-Admin-Key: {admin_api_key}
Content-Type: application/json

{
  "is_enabled": true
}
```

### Доступные методы

| Метод | ID | Описание | Комиссия | Лимиты |
|-------|-----|----------|----------|--------|
| **ЮKassa** | `yookassa` | Карты, СБП, ЮMoney (юрлица) | 2.8% | 100-100,000₽ |
| **ЮMoney P2P** | `yoomoney_p2p` | Перевод на счёт физлица | 0% | 100-50,000₽ |
| **Карты** | `cards` | Visa, Mastercard, МИР | 2.5% | 100-100,000₽ |

---

## 📡 API Endpoints

### User Endpoints

#### Получить доступные методы оплаты

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
      "id": "uuid-1",
      "name": "yookassa",
      "display_name": "ЮKassa",
      "description": "Банковские карты, СБП, ЮMoney",
      "is_enabled": true,
      "icon": "yookassa",
      "config": {
        "min_amount": 100,
        "max_amount": 100000,
        "commission": 0.028
      }
    },
    {
      "id": "uuid-2",
      "name": "yoomoney_p2p",
      "display_name": "ЮMoney P2P",
      "description": "Перевод на счёт физлица (проверенный пользователь)",
      "is_enabled": true,
      "icon": "yoomoney",
      "config": {
        "min_amount": 100,
        "max_amount": 50000,
        "commission": 0
      }
    }
  ]
}
```

#### Создать платёж (ЮMoney P2P)

```http
POST /api/user/payments/create
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "amount": 1000,
  "method": "yoomoney_p2p"
}
```

**Response**:
```json
{
  "success": true,
  "data": {
    "payment_id": "uuid",
    "account_number": "41001XXXXXXXXXXXX",
    "amount": 1000,
    "status": "pending",
    "instruction": "Переведите 1000₽ на счёт ЮMoney: 41001XXXXXXXXXXXX",
    "expires_at": "2026-03-28T11:00:00Z"
  }
}
```

### Admin Endpoints

#### Список методов оплаты

```http
GET /api/admin/payment-methods
Authorization: Bearer {admin_access_token}
X-Admin-Key: {admin_api_key}
```

#### Обновить метод оплаты

```http
PUT /api/admin/payment-methods/:id
Authorization: Bearer {admin_access_token}
X-Admin-Key: {admin_api_key}
Content-Type: application/json

{
  "is_enabled": false,
  "config": {
    "min_amount": 200,
    "max_amount": 30000
  }
}
```

#### Добавить проверенного пользователя

```http
POST /api/admin/yoomoney-p2p
Authorization: Bearer {admin_access_token}
X-Admin-Key: {admin_api_key}
Content-Type: application/json

{
  "account_number": "41001XXXXXXXXXXXX",
  "verified_user_vk_id": 12345678,
  "verified_user_name": "Иванов Иван Иванович",
  "is_verified": true,
  "is_active": true,
  "api_key": "yoomoney_api_key"
}
```

#### Обновить проверенного пользователя

```http
PUT /api/admin/yoomoney-p2p/:id
Authorization: Bearer {admin_access_token}
X-Admin-Key: {admin_api_key}
Content-Type: application/json

{
  "is_active": false,
  "is_verified": true
}
```

#### Получить статистику ЮMoney

```http
GET /api/admin/yoomoney-p2p/stats
Authorization: Bearer {admin_access_token}
X-Admin-Key: {admin_api_key}
Query: ?from=2026-01-01&to=2026-03-28
```

---

## 🔒 Безопасность

### Хранение API ключей

- ✅ API ключи хранятся в хэшированном виде
- ✅ Доступ только у суперадмина
- ✅ Логгирование всех операций с ключами
- ✅ Регулярная ротация ключей (рекомендуется раз в 90 дней)

### Проверка webhook

```javascript
// Пример проверки подписи webhook
const crypto = require('crypto');

function verifyYooMoneyWebhook(body, signature, secret) {
  const hash = crypto
    .createHmac('sha256', secret)
    .update(JSON.stringify(body))
    .digest('hex');
  
  return hash === signature;
}
```

### Верификация пользователя

- ✅ Проверка VK ID через OAuth
- ✅ Сверка имени с паспортом
- ✅ Проверка номера счёта ЮMoney
- ✅ Тестовый перевод (1₽)

### Лимиты и мониторинг

| Параметр | Значение |
|----------|----------|
| Макс. сумма в день | 15,000₽ |
| Макс. сумма в месяц | 50,000₽ |
| Мин. сумма перевода | 100₽ |
| Автоматическая проверка | Каждые 5 минут |
| Уведомление о крупных суммах | >10,000₽ |

---

## 🐛 Troubleshooting

### Платёж не зачисляется

**Проблема**: Пользователь перевёл деньги, но баланс не пополнился.

**Решение**:
1. Проверьте статус в ЮMoney API
2. Проверьте webhook логи
3. Сверьте номер счёта
4. При необходимости — ручное зачисление через админку

### Ошибка "Неверный API ключ"

**Проблема**: ЮMoney возвращает ошибку авторизации.

**Решение**:
1. Проверьте API ключ в настройках
2. Пересоздайте ключ в кабинете ЮMoney
3. Обновите в системе: `PUT /api/admin/yoomoney-p2p/:id`

### Webhook не приходит

**Проблема**: ЮMoney не отправляет уведомления.

**Решение**:
1. Проверьте URL webhook в настройках ЮMoney
2. Убедитесь, что сервер доступен из интернета
3. Проверьте firewall и SSL сертификат
4. Включите логгирование webhook

### Превышен лимит

**Проблема**: Пользователь не может перевести больше лимита.

**Решение**:
1. Проверьте лимиты в настройках метода
2. Предложите разбить платёж на несколько
3. Предложите альтернативный метод (ЮKassa)

---

## 📊 Мониторинг

### Метрики для отслеживания

```sql
-- Общая сумма поступлений за день
SELECT SUM(amount) 
FROM payments 
WHERE provider = 'yoomoney_p2p' 
  AND status = 'succeeded' 
  AND DATE(created_at) = CURRENT_DATE;

-- Количество успешных платежей
SELECT COUNT(*) 
FROM payments 
WHERE provider = 'yoomoney_p2p' 
  AND status = 'succeeded';

-- Средний чек
SELECT AVG(amount) 
FROM payments 
WHERE provider = 'yoomoney_p2p' 
  AND status = 'succeeded';
```

### Dashboard метрики

- 📈 Сумма поступлений (день/неделя/месяц)
- 🔢 Количество платежей
- 💰 Средний чек
- ⏱ Время зачисления
- ❌ Процент неудачных платежей

---

## 📞 Поддержка

При возникновении проблем:

1. Проверьте логи: `docker compose logs backend`
2. Проверьте статус ЮMoney: [status.yoomoney.ru](https://status.yoomoney.ru/)
3. Обратитесь в поддержку ЮMoney
4. Создайте issue на GitHub

---

*Версия: 1.0 | Последнее обновление: 28 марта 2026*
