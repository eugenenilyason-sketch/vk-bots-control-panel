#!/bin/bash

# VK Neuro-Agents Control Panel - Init Script
# Инициализация проекта

set -e

echo "🚀 Инициализация VK Neuro-Agents Control Panel..."

# Проверка Docker
if ! command -v docker &> /dev/null; then
    echo "❌ Docker не установлен. Установите Docker."
    exit 1
fi

if ! command -v docker-compose &> /dev/null && ! docker compose version &> /dev/null; then
    echo "❌ Docker Compose не установлен."
    exit 1
fi

echo "✅ Docker проверен"

# Создание .env файла
if [ ! -f .env ]; then
    echo "📝 Создание .env файла..."
    cp .env.example .env

    # Генерация секретов
    echo "🔐 Генерация секретов..."

    POSTGRES_PASS=$(openssl rand -hex 32)
    JWT_SECRET=$(openssl rand -hex 32)
    NC_JWT_SECRET=$(openssl rand -hex 32)
    N8N_JWT_SECRET=$(openssl rand -hex 32)
    REDIS_PASS=$(openssl rand -hex 32)

    # Замена значений в .env
    sed -i "s/POSTGRES_PASSWORD=.*/POSTGRES_PASSWORD=$POSTGRES_PASS/" .env
    sed -i "s/JWT_SECRET=.*/JWT_SECRET=$JWT_SECRET/" .env
    sed -i "s/NC_JWT_SECRET=.*/NC_JWT_SECRET=$NC_JWT_SECRET/" .env
    sed -i "s/N8N_JWT_SECRET=.*/N8N_JWT_SECRET=$N8N_JWT_SECRET/" .env
    sed -i "s/REDIS_PASSWORD=.*/REDIS_PASSWORD=$REDIS_PASS/" .env

    echo "✅ .env файл создан с безопасными секретами"
    echo "⚠️  Сохраните файл .env в безопасном месте!"
else
    echo "✅ .env файл уже существует"
fi

# Создание директорий
echo "📁 Создание директорий..."

mkdir -p nginx/data
mkdir -p nginx/letsencrypt
mkdir -p supabase/migrations
mkdir -p backend/logs
mkdir -p frontend/php-app/storage
mkdir -p frontend/php-app/bootstrap/cache

echo "✅ Директории созданы"

# Проверка наличия Docker образов
echo "🐳 Проверка Docker образов..."

# Pull образов если нужно
if ! docker images nginx:alpine -q | grep -q .; then
    echo "⬇️  Загрузка Nginx..."
    docker pull nginx:alpine
fi

if ! docker images redis:7-alpine -q | grep -q .; then
    echo "⬇️  Загрузка Redis..."
    docker pull redis:7-alpine
fi

if ! docker images supabase/postgres:15.1.0.117 -q | grep -q .; then
    echo "⬇️  Загрузка Supabase PostgreSQL..."
    docker pull supabase/postgres:15.1.0.117
fi

echo "✅ Docker образы готовы"

# Запуск сервисов
echo "🚀 Запуск сервисов..."

docker compose up -d

# Ожидание готовности сервисов
echo "⏳ Ожидание готовности сервисов..."
sleep 20

# Очистка кэшей Laravel
echo "🧹 Очистка кэшей Laravel..."
docker compose exec -T vk-php php artisan cache:clear || true
docker compose exec -T vk-php php artisan config:clear || true
docker compose exec -T vk-php php artisan route:clear || true
docker compose exec -T vk-php php artisan view:clear || true

# Проверка статуса
echo "📊 Статус сервисов:"
docker compose ps

echo ""
echo "✅ Инициализация завершена!"
echo ""
echo "📋 Доступ:"
echo "   - Frontend: https://yourdomain.com/"
echo ""
echo "🔐 Сохраните файл .env в безопасном месте!"
echo ""
echo "📚 Следующие шаги:"
echo "   1. Создайте админа: ./scripts/make-admin.sh"
echo "   2. Настройте OAuth VK приложение"
echo "   3. Получите SSL сертификаты"
echo "   4. Настройте платёжные методы"
echo ""
