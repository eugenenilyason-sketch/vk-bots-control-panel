# 📱 Настройка авторизации через VK ID (универсальная)

Полное руководство по настройке входа через VK ID для всех устройств.

---

## 📋 Как это работает

### Поддерживаемые методы входа

| Метод | Flow | Токен |
|-------|------|-------|
| **Email/Password** | Laravel Auth → JWT генерация | ✅ Генерируется |
| **VK ID** | SDK → Backend API | ✅ Генерируется |

### OAuth Flow (VK ID)

```
┌─────────────┐         ┌──────────────┐         ┌─────────────┐
│  Браузер     │         │   VK ID      │         │   Backend   │
│  (любое      │         │   SDK        │         │   API       │
│   устройство)│         │              │         │             │
└──────┬──────┘         └──────┬───────┘         └──────┬──────┘
       │                       │                        │
       │  1. VK ID OneTap      │                        │
       │  widget rendered      │                        │
       ├──────────────────────►│                        │
       │                       │                        │
       │  2. Authorization     │                        │
       │  (user login)         │                        │
       │◄──────────────────────┤                        │
       │                       │                        │
       │  3. LOGIN_SUCCESS     │                        │
       │  event с code         │                        │
       ├──────────────────────►│                        │
       │                       │                        │
       │  4. Exchange code     │                        │
       │  на токены (SDK)      │                        │
       ├──────────────────────►│                        │
       │                       │                        │
       │  5. Send tokens to    │                        │
       │  backend API          │                        │
       ├───────────────────────┼───────────────────────►│
       │                       │                        │
       │                       │  6. Find/create user   │
       │                       │  + generate JWT        │
       │                       ├───────────────────────►│
       │                       │                        │
       │  7. JWT Token         │                        │
       │  + redirect           │                        │
       │◄──────────────────────┼────────────────────────┤
       │                       │                        │
```

### Особенности

| Параметр | Значение |
|----------|----------|
| **SDK** | VK ID SDK (`@vkid/sdk`) локальный |
| **Widget** | OneTap с `showAlternativeLogin: true` |
| **Flow** | Frontend (JS) обмен кода → Backend API |
| **Устройства** | Все (desktop, mobile, tablet) |

---

## 🔧 Настройка

### 1. Создание VK приложения

1. Перейдите на [https://dev.vk.com/](https://dev.vk.com/)
2. Создайте новое приложение
3. В настройках приложения укажите:
   - **Тип приложения**: Website
   - **Redirect URI**: `https://yourdomain.com/`
   - **Базовый домен**: `yourdomain.com`
   - **Доверенные redirect URI**: добавьте все варианты (с/без www, http/https)

### 2. Получение credentials

После создания приложения вам понадобятся:
- **Client ID** (Application ID)
- **Client Secret** (Secure key)
- **Redirect URI** (должен совпадать с указанным в настройках)

### 3. Установка SDK

SDK уже установлен в проекте:

```bash
cd frontend/php-app
npm install @vkid/sdk@2.6.5
```

Файл SDK скопирован в `public/js/vkid-sdk.js`.

### 4. Настройка проекта

#### 4.1. Обновите `.env`

```env
# JWT Secret (генерируется автоматически)
JWT_SECRET=your_jwt_secret_key_here

# VK ID OAuth
VK_CLIENT_ID=1234567
VK_CLIENT_SECRET=AbCdEfGhIjKlMnOp
VK_REDIRECT_URI=https://yourdomain.com

# Backend URL (для PHP → Node.js коммуникации)
BACKEND_URL=http://backend:4000
```

> **Важно**: 
> - `JWT_SECRET` используется для подписи JWT токенов. Если не указан, используется `APP_KEY`.
> - `VK_REDIRECT_URI` должен быть **без** пути — путь добавляется автоматически в коде.

#### 4.2. Проверьте конфигурацию

Убедитесь, что в `frontend/php-app/config/services.php` есть:

```php
'vk' => [
    'client_id' => env('VK_CLIENT_ID'),
    'client_secret' => env('VK_CLIENT_SECRET'),
    'redirect_uri' => env('VK_REDIRECT_URI'),
],
```

---

## 🚀 Деплой

### 1. Перезапустите frontend

После изменения `.env` перезапустите PHP контейнер:

```bash
docker compose restart frontend
```

### 2. Проверьте логи

```bash
docker compose logs -f frontend
```

---

## 🧪 Тестирование

### 1. Проверка загрузки SDK

Откройте сайт с любого устройства или используйте Chrome DevTools:

1. Откройте `https://yourdomain.com`
2. Откройте консоль (F12)
3. Убедитесь, что в консоли есть:
   ```
   🔍 VK ID SDK loaded, initializing...
   ✅ VK ID Config initialized
   🎨 OneTap rendered
   ```

### 2. Проверка виджета

1. Убедитесь, что виджет VK ID OneTap отображается на странице входа
2. Виджет должен показывать кнопку "Войти с VK ID"

### 3. Тест полного flow

1. Кликните "Войти с VK ID" в виджете
2. Пройдите авторизацию в VK (если не авторизованы)
3. Разрешите приложению доступ к email
4. После успешной авторизации вас редиректнёт на `/dashboard?token=...`
5. Убедитесь, что dashboard загрузился

---

## 🐛 Troubleshooting

### Ошибка: "VK ID SDK not loaded"

**Причина**: Файл SDK не загруется

**Решение**:
1. Проверьте, что файл существует:
   ```bash
   ls -la frontend/php-app/public/js/vkid-sdk.js
   ```

2. Проверьте права доступа:
   ```bash
   chmod 644 frontend/php-app/public/js/vkid-sdk.js
   ```

3. Убедитесь, что Nginx раздаёт статические файлы из `public/`

---

### Ошибка: "Ошибка VK ID"

**Причина**: Проблемы с конфигурацией или авторизацией

**Решение**:
1. Проверьте `VK_CLIENT_ID` в `.env`
2. Убедитесь, что Redirect URI совпадает с указанным в VK
3. Откройте консоль браузера для детализации ошибки

---

### Ошибка: "Ошибка входа: ..."

**Причина**: Backend API вернул ошибку

**Решение**:
1. Проверьте, что backend контейнер запущен:

```bash
docker compose ps backend
```

2. Проверьте логи backend:

```bash
docker compose logs -f backend | grep -i "vk id"
```

3. Убедитесь, что `BACKEND_URL` в `.env` правильный

---

## 🔐 Безопасность

### Code Exchange

Обмен кода на токены происходит на frontend через VK ID SDK, затем токены отправляются на backend API для создания сессии.

### Redirect URI Validation

VK автоматически проверяет redirect URI. Убедитесь, что:
- URI точно совпадает с указанным в настройках приложения
- Используется HTTPS (для production)
- Нет опечаток в домене

---

## 📱 Адаптивность

VK ID OneTap widget автоматически адаптируется под разные устройства:
- ✅ Desktop browsers
- ✅ Mobile browsers (Android, iOS)
- ✅ Tablets

---

## 📈 Мониторинг

### Метрики для отслеживания

- Количество успешных входов через VK ID
- Количество ошибок при авторизации
- Конверсия от клика до успешного входа

### Пример SQL запроса

```sql
-- Количество входов через VK ID за последний день
SELECT COUNT(*) 
FROM users 
WHERE vk_id IS NOT NULL 
  AND created_at > NOW() - INTERVAL '1 day';
```

---

## 📚 Дополнительные ресурсы

- [VK ID SDK Documentation](https://id.vk.com/about/business/go/docs/ru/vkid/latest/vk-id/connection/start-integration/web/install)
- [VK Developers Portal](https://dev.vk.com/)
- [VK ID SDK GitHub](https://github.com/VKCOM/vkid-web-sdk)
- [Laravel HTTP Client](https://laravel.com/docs/11.x/http-client)

---

*Версия: 3.0.0 | Дата: 3 апреля 2026*
*Статус: ✅ Универсальная версия для всех устройств*
