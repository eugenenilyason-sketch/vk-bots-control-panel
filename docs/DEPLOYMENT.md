# 🚀 Руководство по развёртыванию VK Neuro-Agents

## 📋 Содержание

1. [Требования](#требования)
2. [Быстрый старт](#быстрый-старт)
3. [Настройка окружения](#настройка-окружения)
4. [Первый запуск](#первый-запуск)
5. [Настройка VK OAuth](#настройка-vk-oauth)
6. [SSL сертификаты](#ssl-сертификаты)
7. [Деплой обновлений](#деплой-обновлений)
8. [Бэкапы](#бэкапы)
9. [Troubleshooting](#troubleshooting)

---

## Требования

### Минимальные
- **CPU**: 2 cores
- **RAM**: 4 GB
- **Disk**: 20 GB
- **Docker**: 20+
- **Docker Compose**: 2+

### Рекомендуемые
- **CPU**: 4 cores
- **RAM**: 8 GB
- **Disk**: 50 GB SSD
- **Domain**: lianium.ru

---

## Быстрый старт

### 1. Клонирование репозитория

```bash
git clone https://github.com/eugenenilyason-sketch/vk-bots-control-panel.git
cd web-vk-bot
```

### 2. Инициализация

```bash
# Автоматическая инициализация
./scripts/init.sh
```

Или вручную:

```bash
# Копирование .env
cp .env.example .env

# Генерация секретов
openssl rand -hex 32  # POSTGRES_PASSWORD
openssl rand -hex 32  # JWT_SECRET
# ... и т.д.
```

### 3. Запуск

```bash
docker compose up -d
```

### 4. Создание админа

```bash
./scripts/make-admin.sh admin@lianium.ru superadmin
```

---

## Настройка окружения

### .env файл

```env
# Database
POSTGRES_PASSWORD=ваш_пароль
DATABASE_URL=postgresql://postgres:${POSTGRES_PASSWORD}@supabase:5432/vk_bot

# VK OAuth
VK_CLIENT_ID=54514184
VK_CLIENT_SECRET=ваш_secret
VK_REDIRECT_URI=https://lianium.ru

# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=https://lianium.ru

# Session
SESSION_DRIVER=file

# Redis
REDIS_PASSWORD=ваш_пароль
```

---

## Первый запуск

### 1. Проверка статуса

```bash
docker compose ps
```

**Ожидаемый результат:**
```
NAME           STATUS
vk-php         Up (healthy)
vk-backend     Up (healthy)
supabase       Up (healthy)
redis          Up
vk-nginx-php   Up
```

### 2. Проверка логов

```bash
docker compose logs -f
```

### 3. Открытие сайта

```
https://lianium.ru/
```

### 4. Вход как админ

```
Email: admin@lianium.ru
Пароль: (из make-admin.sh)
```

---

## Настройка VK OAuth

### 1. Создание приложения

1. Откройте https://vk.com/dev
2. Приложения → Создать
3. Тип: **Сайт**
4. Название: VK Neuro-Agents

### 2. Настройки приложения

```
Адрес сайта: https://lianium.ru
Redirect URI: https://lianium.ru
```

### 3. Получение ключей

```
Client ID: 54514184
Client Secret: (скопируйте)
```

### 4. Обновление .env

```bash
nano .env
# Обновите VK_CLIENT_SECRET
docker compose restart vk-backend
```

---

## SSL сертификаты

### Let's Encrypt

```bash
# Получение сертификата
./scripts/get-letsencrypt-cert.sh lianium.ru email@example.com

# Автоматическое обновление
crontab scripts/letsencrypt-crontab
```

### Проверка SSL

```bash
# Проверка сертификата
openssl s_client -connect lianium.ru:443

# Проверка онлайн
https://www.ssllabs.com/ssltest/
```

---

## Деплой обновлений

### Автоматический деплой

```bash
./scripts/deploy.sh
```

### Вручную

```bash
# Pull изменений
git pull

# Пересборка
docker compose build

# Перезапуск
docker compose down
docker compose up -d

# Очистка кэшей
docker compose exec vk-php php artisan cache:clear
docker compose exec vk-php php artisan config:clear
```

### Watchtower (автообновление)

```bash
# Watchtower уже включён в docker-compose.yml
# Проверяет обновления каждые 24 часа
```

---

## Бэкапы

### Бэкап БД

```bash
# Создать бэкап
./scripts/backup.sh

# Бэкап в /backups/
ls -la backups/
```

### Восстановление из бэкапа

```bash
# Остановка
docker compose down

# Восстановление
docker exec -i supabase psql -U postgres -d vk_bot < backups/backup-YYYYMMDD.sql

# Запуск
docker compose up -d
```

---

## Troubleshooting

### Контейнер не запускается

```bash
# Проверка логов
docker compose logs <service-name>

# Перезапуск
docker compose restart <service-name>
```

### Ошибка 500

```bash
# Очистка кэшей Laravel
docker compose exec vk-php php artisan cache:clear
docker compose exec vk-php php artisan config:clear
docker compose exec vk-php php artisan view:clear

# Проверка логов
docker compose logs vk-php | grep ERROR
```

### БД недоступна

```bash
# Проверка статуса
docker exec supabase pg_isready

# Перезапуск БД
docker compose restart supabase
```

### SSL не работает

```bash
# Проверка сертификатов
ls -la nginx/letsencrypt/

# Проверка Nginx конфига
docker exec vk-nginx-php nginx -t

# Перезапуск Nginx
docker compose restart vk-nginx-php
```

---

## Контакты

**Поддержка**: support@lianium.ru  
**Документация**: https://lianium.ru/docs

---

*Версия: 2.0.0 | Дата: 30 марта 2026*
