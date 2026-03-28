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
mkdir -p n8n/data
mkdir -p n8n/workflows
mkdir -p nocodb/data
mkdir -p supabase/migrations
mkdir -p backend/logs

echo "✅ Директории созданы"

# Проверка наличия Docker образов
echo "🐳 Проверка Docker образов..."

# Pull образов если нужно
if ! docker images jc21/nginx-proxy-manager:latest -q | grep -q .; then
    echo "⬇️  Загрузка Nginx Proxy Manager..."
    docker pull jc21/nginx-proxy-manager:latest
fi

if ! docker images n8n/n8n:latest -q | grep -q .; then
    echo "⬇️  Загрузка n8n..."
    docker pull n8n/n8n:latest
fi

if ! docker images nocodb/nocodb:latest -q | grep -q .; then
    echo "⬇️  Загрузка NocoDB..."
    docker pull nocodb/nocodb:latest
fi

if ! docker images supabase/postgres:15.1.0.117 -q | grep -q .; then
    echo "⬇️  Загрузка Supabase PostgreSQL..."
    docker pull supabase/postgres:15.1.0.117
fi

if ! docker images redis:7-alpine -q | grep -q .; then
    echo "⬇️  Загрузка Redis..."
    docker pull redis:7-alpine
fi

echo "✅ Docker образы готовы"

# Запуск сервисов
echo "🚀 Запуск сервисов..."

docker compose up -d

# Ожидание готовности сервисов
echo "⏳ Ожидание готовности сервисов..."
sleep 15

# Проверка статуса
echo "📊 Статус сервисов:"
docker compose ps

echo ""
echo "✅ Инициализация завершена!"
echo ""
echo "📋 Доступ к сервисам:"
echo "   - Nginx Proxy Manager: http://localhost:81"
echo "   - n8n: http://localhost:5678"
echo "   - NocoDB: http://localhost:8080"
echo ""
echo "🔐 Сохраните файл .env в безопасном месте!"
echo ""
echo "📚 Следующие шаги:"
echo "   1. Настройте домены в Nginx Proxy Manager"
echo "   2. Получите SSL сертификаты"
echo "   3. Настройте OAuth приложения VK и OK"
echo "   4. Настройте платёжную систему"
echo "   5. Создайте workflow в n8n"
echo "   6. Настройте таблицы в NocoDB"
echo ""
