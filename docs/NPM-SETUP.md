# 📋 Настройка Nginx Proxy Manager для VK Neuro-Agents

## 🎯 Варианты развёртывания

### Вариант 1: Одиночный NPM (рекомендуется для начала)

**Архитектура**: `Internet → NPM → Сайт`

- ✅ Проще в настройке
- ✅ Меньше точек отказа
- ✅ Достаточно для большинства сценариев

📖 **Инструкция**: [Одиночный NPM](#настройка-одиночного-npm)

---

### Вариант 2: Цепочка NPM (для DMZ)

**Архитектура**: `Internet → NPM (Edge) → NPM (Dev) → Сайт`

- ✅ Дополнительный уровень безопасности
- ✅ Разделение внешнего и внутреннего трафика
- ✅ Для сложных сетевых конфигураций

📖 **Инструкция**: [Цепочка NPM](NPM-CHAIN-SETUP.md)

---

## Настройка одиночного NPM

### 1. Подготовка

**Домен**: `yourdomain.com`

**Сервисы на одном домене**:
- Frontend: `https://yourdomain.com/`
- Backend API: `https://yourdomain.com/api/*`
- Webhooks: `https://yourdomain.com/webhook/*`

---

### 2. Настройка в Nginx Proxy Manager

#### Шаг 1: Добавьте Proxy Host

1. Откройте Nginx Proxy Manager: `http://your-server-ip:81`
2. Войдите (admin@example.com / changeme)
3. Нажмите **Add Proxy Host**

#### Шаг 2: Заполните основные настройки

**Basic Settings**:
- **Domain Names**: `yourdomain.com`
- **Scheme**: `http`
- **Forward Hostname/IP**: `frontend` (имя контейнера frontend)
- **Forward Port**: `3000`
- **Cache Assets**: ✅ Включить
- **Block Common Exploits**: ✅ Включить
- **Websockets Support**: ✅ Включить

#### Шаг 3: Добавьте Advanced конфигурацию

В поле **Advanced** вставьте следующую конфигурацию:

```nginx
# Backend API
location /api {
    proxy_pass http://backend:4000;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection 'upgrade';
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_set_header X-Forwarded-Host $host;
    proxy_set_header X-Forwarded-Port $server_port;
    proxy_cache_bypass $http_upgrade;
    
    # Таймауты
    proxy_connect_timeout 60s;
    proxy_send_timeout 60s;
    proxy_read_timeout 60s;
}

# Webhooks
location /webhook {
    proxy_pass http://backend:4000;
    proxy_http_version 1.1;
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_set_header X-Forwarded-Host $host;
}

# Health check
location /health {
    proxy_pass http://backend:4000;
    proxy_http_version 1.1;
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
}
```

#### Шаг 4: Настройте SSL

**SSL Tab**:
- **SSL Certificate**: Request a new SSL certificate
- **Force SSL**: ✅ Включить
- **HTTP/2 Support**: ✅ Включить
- **HSTS Enabled**: ✅ Включить (опционально)

---

### 3. Обновление переменных окружения

Скопируйте `.env.example` в `.env` и обновите:

```bash
cp .env.example .env
nano .env
```

**Обновите следующие переменные**:

```env
# ============= Application =============
NODE_ENV=production
FRONTEND_URL=https://yourdomain.com
BACKEND_URL=https://yourdomain.com
BASE_URL=https://yourdomain.com

# ============= VK OAuth =============
VK_CLIENT_ID=your_vk_client_id
VK_CLIENT_SECRET=your_vk_client_secret
VK_REDIRECT_URI=https://yourdomain.com

# ============= YooMoney P2P =============
YOOMONEY_ACCOUNT_NUMBER=your_yoomoney_account
YOOMONEY_API_KEY=your_yoomoney_api_key
YOOMONEY_WEBHOOK_URL=https://yourdomain.com/webhook/yoomoney
```

---

### 4. Перезапуск сервисов

```bash
# Остановка старых контейнеров
docker compose down

# Запуск всех сервисов
docker compose up -d

# Проверка статуса
docker compose ps

# Просмотр логов
docker compose logs -f
```

---

### 5. Проверка работы

#### Проверка Frontend

Откройте в браузере: `https://yourdomain.com`

Должна открыться страница входа с кнопкой "Войти через VK".

#### Проверка Backend API

```bash
curl https://yourdomain.com/health
# Ожидаемый ответ: {"status":"ok","timestamp":"..."}
```

#### Проверка API endpoints

```bash
curl https://yourdomain.com/api/auth/me
# Должен вернуть ошибку авторизации (это нормально)
```

---

### 6. Настройка VK OAuth

1. Перейдите в [VK Developers](https://vk.com/dev)
2. Создайте новое приложение
3. Выберите тип "Website"
4. Укажите **Redirect URI**: `https://yourdomain.com`
5. Скопируйте `Client ID` и `Client Secret`
6. Обновите `.env`:

```env
VK_CLIENT_ID=your_actual_client_id
VK_CLIENT_SECRET=your_actual_client_secret
VK_REDIRECT_URI=https://yourdomain.com
```

7. Перезапустите backend:

```bash
docker compose restart backend
```

---

### 7. Настройка webhook для ЮMoney

1. Зарегистрируйтесь в [ЮMoney](https://yoomoney.ru/)
2. Получите API ключ
3. Обновите `.env`:

```env
YOOMONEY_ACCOUNT_NUMBER=your_account_number
YOOMONEY_API_KEY=your_api_key
YOOMONEY_WEBHOOK_URL=https://yourdomain.com/webhook/yoomoney
```

4. В личном кабинете ЮMoney укажите webhook URL:
   `https://yourdomain.com/webhook/yoomoney`

5. Перезапустите backend:

```bash
docker compose restart backend
```

---

### 8. Админ-панель

После первого входа через VK:

1. Откройте NocoDB: `http://your-server-ip:8080`
2. Подключитесь к базе данных:
   - Host: `supabase`
   - Port: `5432`
   - Database: `vk_bot`
   - User: `postgres`
   - Password: из `.env`

3. Найдите таблицу `users`
4. Найдите своего пользователя
5. Измените `role` с `user` на `admin` или `superadmin`
6. Сохраните

Теперь в меню появится пункт **"Админка"**.

---

### 9. Управление методами оплаты (из админки)

1. Войдите как администратор
2. Перейдите в **Админка**
3. В разделе **"Методы оплаты"**:
   - Включите/выключите нужные методы
   - ЮMoney P2P включен по умолчанию
   - Изменения применяются мгновенно

---

### 10. Дополнительные сервисы (опционально)

#### n8n на поддомене

Создайте отдельный Proxy Host:
- **Domain**: `n8n.yourdomain.com`
- **Forward Host**: `n8n`
- **Forward Port**: `5678`

#### NocoDB на поддомене

Создайте отдельный Proxy Host:
- **Domain**: `db.yourdomain.com`
- **Forward Host**: `nocodb`
- **Forward Port**: `8080`

---

## 🔧 Полезные команды

```bash
# Проверка статуса всех сервисов
docker compose ps

# Перезапуск конкретного сервиса
docker compose restart backend

# Просмотр логов
docker compose logs -f backend

# Остановка всех сервисов
docker compose down

# Запуск всех сервисов
docker compose up -d

# Бэкап базы данных
docker exec supabase pg_dump -U postgres vk_bot > backup.sql
```

---

## 🐛 Troubleshooting

### Ошибка 502 Bad Gateway

**Проблема**: Nginx не может подключиться к backend/frontend

**Решение**:
```bash
# Проверьте, что сервисы запущены
docker compose ps

# Проверьте логи
docker compose logs backend
docker compose logs frontend

# Перезапустите сервисы
docker compose restart backend frontend
```

### Ошибка CORS

**Проблема**: Frontend не может подключиться к API

**Решение**:
1. Проверьте, что `FRONTEND_URL` в `.env` совпадает с вашим доменом
2. Перезапустите backend: `docker compose restart backend`

### VK OAuth не работает

**Проблема**: redirect_uri mismatch

**Решение**:
1. Проверьте Redirect URI в настройках VK приложения
2. Должно совпадать с `https://yourdomain.com`
3. Обновите `VK_REDIRECT_URI` в `.env`

---

*Версия: 1.0 | Дата: 28 марта 2026*
