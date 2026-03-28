# 🚀 Deployment Guide

Полное руководство по развёртыванию VK Neuro-Agents Control Panel.

---

## 📋 Предварительные требования

### Системные требования

- **CPU**: 4+ cores (рекомендуется 8+)
- **RAM**: 8+ GB (рекомендуется 16+)
- **Disk**: 50+ GB SSD
- **OS**: Linux (Ubuntu 20.04+, Debian 11+)
- **Docker**: 20+
- **Docker Compose**: 2+

### Домены и SSL

Вам понадобятся:
- Основной домен: `yourdomain.com`
- Поддомены:
  - `app.yourdomain.com` — Frontend
  - `api.yourdomain.com` — Backend API
  - `n8n.yourdomain.com` — n8n
  - `nocodb.yourdomain.com` — NocoDB

---

## 🔧 Шаг 1: Установка Docker

```bash
# Обновление системы
sudo apt update && sudo apt upgrade -y

# Установка Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Добавление пользователя в группу docker
sudo usermod -aG docker $USER

# Установка Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Проверка установки
docker --version
docker-compose --version
```

---

## 🔧 Шаг 2: Клонирование проекта

```bash
cd /home/vidserv
git clone <repository-url> web-vk-bot
cd web-vk-bot
```

---

## 🔧 Шаг 3: Инициализация проекта

```bash
# Запуск скрипта инициализации
./scripts/init.sh
```

Скрипт автоматически:
- Создаст `.env` файл
- Сгенерирует безопасные секреты
- Создаст необходимые директории
- Загрузит Docker образы
- Запустит сервисы

---

## 🔧 Шаг 4: Настройка OAuth приложений

### ВКонтакте (VK)

1. Перейдите в [VK Developers](https://vk.com/dev)
2. Создайте новое приложение
3. Выберите тип "Website"
4. Укажите Redirect URI: `https://yourdomain.com/auth/vk/callback`
5. Скопируйте `Client ID` и `Client Secret`
6. Включите необходимые права: `messages`, `groups`, `offline`

### Обновление .env

```bash
nano .env
```

Заполните:
```env
VK_CLIENT_ID=your_vk_client_id
VK_CLIENT_SECRET=your_vk_client_secret
VK_REDIRECT_URI=https://yourdomain.com/auth/vk/callback
```

---

## 🔧 Шаг 5: Настройка Nginx Proxy Manager

1. Откройте Nginx Proxy Manager: `http://your-server-ip:81`
2. Логин: `admin@example.com`
3. Пароль: `changeme`

### Создание Proxy Host для Frontend

- **Domain**: `app.yourdomain.com`
- **Forward Host**: `frontend`
- **Forward Port**: `3000`
- **SSL**: Request a new SSL certificate
- **Force SSL**: Yes
- **HTTP/2 Support**: Yes

### Создание Proxy Host для Backend API

- **Domain**: `api.yourdomain.com`
- **Forward Host**: `backend`
- **Forward Port**: `4000`
- **SSL**: Request a new SSL certificate
- **Force SSL**: Yes

### Создание Proxy Host для n8n

- **Domain**: `n8n.yourdomain.com`
- **Forward Host**: `n8n`
- **Forward Port**: `5678`
- **SSL**: Request a new SSL certificate
- **Force SSL**: Yes
- **Websockets Support**: Yes

### Создание Proxy Host для NocoDB

- **Domain**: `nocodb.yourdomain.com`
- **Forward Host**: `nocodb`
- **Forward Port**: `8080`
- **SSL**: Request a new SSL certificate
- **Force SSL**: Yes

---

## 🔧 Шаг 6: Настройка платёжной системы (ЮKassa)

1. Зарегистрируйтесь в [ЮKassa](https://yookassa.ru/)
2. Создайте магазин
3. Получите `shopId` и `secretKey`
4. Настройте webhook URL: `https://api.yourdomain.com/webhook/yookassa`

### Обновление .env

```env
YOOKASSA_SHOP_ID=your_shop_id
YOOKASSA_SECRET_KEY=your_secret_key
YOOKASSA_WEBHOOK_URL=https://api.yourdomain.com/webhook/yookassa
```

---

## 🔧 Шаг 7: Настройка n8n workflows

1. Откройте n8n: `https://n8n.yourdomain.com`
2. Войдите с учётными данными из `.env`

### Создание workflow для VK Bot

1. Создайте новый workflow
2. Добавьте Webhook ноду:
   - **Method**: POST
   - **Path**: `vk-webhook`
3. Добавьте VK ноду для отправки сообщений
4. Добавьте логику обработки сообщений
5. Сохраните и активируйте workflow

### Экспорт workflow

```bash
# Экспортируйте workflow в файл
# n8n → Settings → Export workflow → Save as JSON
# Поместите файл в ./n8n/workflows/
```

### Получение API ключа n8n

1. n8n → Settings → API
2. Сгенерируйте API ключ
3. Обновите `.env`:

```env
N8N_API_KEY=your_n8n_api_key
```

---

## 🔧 Шаг 8: Настройка NocoDB

1. Откройте NocoDB: `https://nocodb.yourdomain.com`
2. Создайте супер админа
3. Подключитесь к базе данных:
   - **Host**: `supabase`
   - **Port**: `5432`
   - **Database**: `vk_bot`
   - **Username**: `postgres`
   - **Password**: из `.env`

### Создание таблиц

NocoDB автоматически обнаружит таблицы из миграций.

### Получение API ключа NocoDB

1. NocoDB → Settings → API Keys
2. Сгенерируйте API ключ
3. Обновите `.env`:

```env
NOCODB_API_KEY=your_nocodb_api_key
```

---

## 🔧 Шаг 9: Деплой приложения

```bash
# Перезапуск всех сервисов
docker compose down
docker compose up -d

# Проверка статуса
docker compose ps

# Просмотр логов
docker compose logs -f
```

---

## 🔧 Шаг 10: Регистрация суперадмина

1. Откройте: `https://app.yourdomain.com`
2. Войдите через VK
3. Первый пользователь автоматически получает роль `superadmin`
4. Перейдите в Admin Panel
5. Сгенерируйте Admin API Key

---

## 📊 Мониторинг и обслуживание

### Проверка статуса сервисов

```bash
docker compose ps
```

### Просмотр логов

```bash
# Все логи
docker compose logs -f

# Логи конкретного сервиса
docker compose logs -f backend
```

### Бэкап базы данных

```bash
./scripts/backup.sh
```

### Восстановление из бэкапа

```bash
# Распаковка бэкапа
gunzip backups/db_backup_YYYYMMDD_HHMMSS.sql.gz

# Восстановление
docker exec -i supabase psql -U postgres -d vk_bot < backups/db_backup_YYYYMMDD_HHMMSS.sql
```

### Обновление проекта

```bash
# Pull свежих изменений
git pull

# Деплой
./scripts/deploy.sh
```

---

## 🔒 Безопасность

### Firewall настройка

```bash
# Разрешить только необходимые порты
sudo ufw allow 22/tcp    # SSH
sudo ufw allow 80/tcp    # HTTP
sudo ufw allow 443/tcp   # HTTPS
sudo ufw enable
```

### Docker security best practices

- Не публикуйте внутренние порты наружу
- Используйте Docker secrets для чувствительных данных
- Регулярно обновляйте образы
- Используйте non-root пользователей в контейнерах

### SSL/TLS

- Все сервисы должны работать только по HTTPS
- Используйте Let's Encrypt для SSL сертификатов
- Включите HSTS в Nginx

---

## 🐛 Troubleshooting

### Сервис не запускается

```bash
# Проверка логов
docker compose logs <service-name>

# Проверка конфигурации
docker compose config

# Пересоздание контейнера
docker compose up -d --force-recreate <service-name>
```

### Проблемы с базой данных

```bash
# Проверка подключения к БД
docker exec -it supabase psql -U postgres -d vk_bot

# Проверка миграций
docker exec supabase ls -la /docker-entrypoint-initdb.d/
```

### Проблемы с SSL

```bash
# Пересоздание SSL сертификатов
# В Nginx Proxy Manager:
# 1. Удалите старый сертификат
# 2. Запросите новый
```

### Очистка ресурсов

```bash
# Остановка всех сервисов
docker compose down

# Удаление volumes (осторожно!)
docker compose down -v

# Очистка unused ресурсов
docker system prune -a
```

---

## 📞 Поддержка

При возникновении проблем:

1. Проверьте логи: `docker compose logs -f`
2. Проверьте документацию
3. Создайте issue на GitHub
4. Обратитесь в поддержку

---

## 📋 Checklist после деплоя

- [ ] Все сервисы запущены (`docker compose ps`)
- [ ] SSL сертификаты активны
- [ ] OAuth приложения настроены
- [ ] Платёжная система подключена
- [ ] n8n workflows созданы
- [ ] NocoDB таблицы видны
- [ ] Регистрация суперадмина выполнена
- [ ] Бэкап настроен
- [ ] Мониторинг включён

---

*Версия: 1.0 | Последнее обновление: 28 марта 2026*
