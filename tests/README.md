# 🧪 Тестирование авторизации VK Neuro-Agents

Полное руководство по тестированию системы авторизации VK ID и JWT токенов.

---

## 📋 Содержание

1. [Обзор](#обзор)
2. [E2E тесты (Playwright)](#e2e-тесты-playwright)
3. [Backend тесты (Node.js)](#backend-тесты-nodejs)
4. [PHP JWT тесты](#php-jwt-тесты)
5. [Ручное тестирование](#ручное-тестирование)
6. [Устранение проблем](#устранение-проблем)

---

## Обзор

Система авторизации включает:

| Компонент | Описание |
|-----------|----------|
| **VK ID OAuth** | Авторизация через VK ID с PKCE |
| **JWT токены** | Access token (1ч) + Refresh token (30д) |
| **Backend API** | Node.js + Express |
| **Frontend** | PHP/Laravel + JWT middleware |
| **Nginx** | Проксирование `/api/*` на backend |

---

## E2E тесты (Playwright)

### 📁 Расположение
```
tests/e2e/
├── vkid-login.test.js      # Базовый тест VK ID кнопки
├── auth-flow.test.js       # Полный цикл авторизации
├── playwright.config.js    # Конфигурация
└── package.json
```

### 🚀 Запуск

```bash
cd tests/e2e

# Установка зависимостей
npm install

# Запуск всех тестов
npm test

# Запуск конкретного теста
npx playwright test vkid-login.test.js
npx playwright test auth-flow.test.js

# Запуск в headed режиме (с браузером)
npx playwright test --headed

# Запуск с отладкой
npx playwright test --debug
```

### 📊 Отчёты

После тестирования откройте HTML отчёт:
```bash
npx playwright show-report
```

### 📸 Скриншоты и видео

Сохраняются в:
- `screenshots/` - скриншоты
- `videos/` - видео тестов

---

## Backend тесты (Node.js)

### 📁 Расположение
```
tests/backend/
├── auth.test.js    # Тесты авторизации
├── jwt.test.js     # Тесты JWT токенов
└── package.json
```

### 🚀 Запуск

```bash
cd tests/backend

# Установка зависимостей
npm install

# Запуск тестов авторизации
node auth.test.js

# Запуск JWT тестов
node jwt.test.js
```

### 📋 Тесты auth.test.js

| Тест | Описание |
|------|----------|
| Health Check | Проверка доступности backend |
| VK ID Auth | Авторизация через VK ID |
| Get Profile | Получение профиля с токеном |
| No Token | Запрос без токена (401) |
| Invalid Token | Запрос с невалидным токеном (401) |
| Refresh Token | Обновление токена |
| Logout | Выход из системы |

### 📋 Тесты jwt.test.js

| Тест | Описание |
|------|----------|
| Valid Token | Валидный токен |
| Expired Token | Истёкший токен |
| Expiring Soon | Токен, истекающий через 2с |
| Invalid Signature | Токен с чужой подписью |
| None Algorithm | Атака через alg=none |
| Without UserId | Токен без userId |
| Tampered Token | Изменённый payload |
| Empty Token | Пустой токен |
| Extra Claims | Токен с дополнительными claims |

---

## PHP JWT тесты

### 📁 Расположение
```
tests/php/
└── jwt-middleware.test.php
```

### 🚀 Запуск

```bash
cd frontend/php-app

# Установка зависимостей (если нужно)
composer install

# Запуск тестов из корня проекта
php tests/php/jwt-middleware.test.php
```

### 📋 Тесты

| Тест | Описание |
|------|----------|
| Valid Token | Валидный токен |
| Expired Token | Истёкший токен |
| Invalid Signature | Неправильная подпись |
| Without UserId | Токен без userId |
| Tampered Token | Изменённый payload |
| Extra Claims | Дополнительные claims |
| Base64 Secret | Secret в base64 |
| Fallback to AppKey | Fallback на APP_KEY |

---

## Ручное тестирование

### 1️⃣ Проверка VK ID авторизации

```bash
# 1. Откройте https://yourdomain.com/
# 2. Нажмите "Войти с VK ID"
# 3. Пройдите авторизацию через VK
# 4. Проверьте редирект на /dashboard
# 5. Проверьте отображение профиля
```

### 2️⃣ Проверка JWT токена

```bash
# Получить токен через API
TOKEN=$(curl -s -X POST https://yourdomain.com/api/auth/vkid \
  -H "Content-Type: application/json" \
  -d '{"access_token":"test","user_id":"123"}' \
  | jq -r '.data.access_token')

# Использовать токен для получения профиля
curl -s https://yourdomain.com/api/user/profile \
  -H "Authorization: Bearer $TOKEN"
```

### 3️⃣ Проверка истёкшего токена

```bash
# Создайте истёкший токен (Node.js)
node -e "
const jwt = require('jsonwebtoken');
const token = jwt.sign(
  { userId: 'test', exp: Math.floor(Date.now()/1000) - 3600 },
  '<your_jwt_secret>'
);
console.log(token);
"

# Попробуйте использовать с API
curl -s https://yourdomain.com/api/user/profile \
  -H "Authorization: Bearer <истёкший_токен>"
```

### 4️⃣ Проверка через браузер

```javascript
// Откройте консоль браузера на /dashboard
// Проверьте localStorage
localStorage.getItem('access_token')
localStorage.getItem('user')

// Проверьте запрос к API
fetch('/api/user/profile', {
  headers: {
    'Authorization': 'Bearer ' + localStorage.getItem('access_token')
  }
}).then(r => r.json()).then(console.log)
```

---

## Устранение проблем

### ❌ Тесты не находят кнопку VK ID

**Проблема:** Селектор кнопки не совпадает

**Решение:**
```javascript
// Обновите селектор в тесте
await page.waitForSelector('button:has-text("Войти с VK ID")');

// Или альтернативный
await page.waitForSelector('[class*="vkid"]');
```

### ❌ JWT токен не валидируется

**Проблема:** Разные секреты в backend и PHP

**Решение:**
```bash
# Проверьте JWT_SECRET в .env
cat .env | grep JWT_SECRET

# Проверьте в backend контейнере
docker compose exec backend node -e "console.log(process.env.JWT_SECRET)"

# Проверьте в PHP контейнере
docker compose exec php cat /var/www/html/.env | grep JWT_SECRET
```

### ❌ 401 Unauthorized для валидного токена

**Возможные причины:**
1. Истёк срок действия токена (1 час)
2. Неправильный формат заголовка
3. Проблема с middleware

**Решение:**
```bash
# Проверьте формат заголовка
curl -H "Authorization: Bearer <token>" ...

# Проверьте логи backend
docker compose logs backend | grep -i "unauthorized\|token"

# Проверьте decoded токен
node -e "
const jwt = require('jsonwebtoken');
const decoded = jwt.verify('<token>', '<secret>');
console.log(decoded);
"
```

### ❌ CORS ошибки

**Проблема:** Backend не принимает origin

**Решение:**
```bash
# Проверьте CORS настройки в backend
docker compose logs backend | grep -i cors

# Обновите FRONTEND_URL в .env
FRONTEND_URL=https://yourdomain.com
```

### ❌ Nginx не проксирует /api

**Проблема:** Неправильная конфигурация nginx

**Решение:**
```nginx
# Проверьте nginx config
location /api {
    proxy_pass http://backend:4000;
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
}
```

---

## 📊 Чеклист перед деплоем

- [ ] Все E2E тесты проходят
- [ ] Backend auth тесты проходят
- [ ] JWT тесты проходят
- [ ] PHP middleware тесты проходят
- [ ] Ручная авторизация через VK ID работает
- [ ] Dashboard загружается с токеном
- [ ] Logout работает корректно
- [ ] Refresh токена работает

---

## 🔗 Ссылки

- [VK ID документация](https://id.vk.com/)
- [JWT.io](https://jwt.io/)
- [Playwright документация](https://playwright.dev/)
- [Firebase-JWT-PHP](https://github.com/firebase/php-jwt)

---

*Последнее обновление: 3 апреля 2026*
