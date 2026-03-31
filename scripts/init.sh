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
mkdir -p nginx/ssl
mkdir -p supabase/migrations
mkdir -p backend/logs
mkdir -p frontend/php-app/storage
mkdir -p frontend/php-app/bootstrap/cache

echo "✅ Директории созданы"

# Генерация самоподписанных SSL сертификатов (если нет)
echo ""
echo "🔐 Проверка SSL сертификатов..."
if [ ! -f "nginx/ssl/server.crt" ] || [ ! -f "nginx/ssl/server.key" ]; then
    echo "⚠️  SSL сертификаты не найдены - создаём самоподписанные..."
    
    # Создаём самоподписанный сертификат на 365 дней
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
        -keyout nginx/ssl/server.key \
        -out nginx/ssl/server.crt \
        -subj "/CN=lianium.ru/O=VK Neuro-Agents/C=RU" \
        2>/dev/null
    
    if [ $? -eq 0 ]; then
        echo "✅ SSL сертификаты созданы:"
        echo "   - nginx/ssl/server.crt"
        echo "   - nginx/ssl/server.key"
        echo ""
        echo "⚠️  Внимание: Это самоподписанные сертификаты!"
        echo "   Для production используйте Let's Encrypt:"
        echo "   ./scripts/get-letsencrypt-cert.sh"
    else
        echo "❌ Ошибка создания SSL сертификатов!"
        echo "   Попробуйте вручную:"
        echo "   openssl req -x509 -nodes -days 365 -newkey rsa:2048 \\"
        echo "     -keyout nginx/ssl/server.key \\"
        echo "     -out nginx/ssl/server.crt \\"
        echo "     -subj '/CN=lianium.ru/O=VK Neuro-Agents/C=RU'"
    fi
else
    echo "✅ SSL сертификаты найдены"
fi

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
echo ""
echo "⏳ Ожидание готовности сервисов..."

# Ждём пока БД станет healthy
echo "   • Redis: 5 сек"
sleep 5

echo "   • Supabase (БД): ждём healthy статус..."
for i in {1..30}; do
    if docker compose ps supabase 2>&1 | grep -q "(healthy)"; then
        echo "     ✅ БД готова!"
        break
    fi
    echo -n "."
    sleep 2
done
echo ""

echo "   • Backend: 10 сек"
sleep 10

echo "   • PHP: 10 сек"
sleep 10

# Проверка что PHP контейнер работает
echo ""
echo "🔍 Проверка PHP контейнера..."
if ! docker compose ps php 2>&1 | grep -q "Up"; then
    echo "⚠️  PHP контейнер не запущен - перезапускаем..."
    docker compose restart php
    sleep 5
fi

# Установка Laravel зависимостей
echo ""
echo "📦 Установка Laravel зависимостей..."
if [ -f "frontend/php-app/composer.json" ] && [ ! -d "frontend/php-app/vendor" ]; then
    echo "   Запуск composer install..."
    
    # Пробуем несколько раз (контейнер может быть ещё не готов)
    for attempt in 1 2 3; do
        echo "   Попытка $attempt..."
        if docker compose exec -T php composer install --no-interaction --optimize-autoloader 2>&1 | tail -5; then
            echo "   ✅ Laravel зависимости установлены"
            break
        else
            echo "   ⚠️  Попытка $attempt не удалась, ждём 10 сек..."
            sleep 10
        fi
    done
    
    # Проверка результата
    if [ -d "frontend/php-app/vendor" ]; then
        echo "✅ Laravel зависимости установлены"
    else
        echo "⚠️  Ошибка установки зависимостей - выполните вручную:"
        echo "   docker compose exec php composer install"
    fi
else
    echo "✅ Laravel зависимости уже установлены"
fi

# Исправление прав доступа
echo ""
echo "🔧 Исправление прав доступа..."
chown -R $(id -u):$(id -g) frontend/php-app/storage 2>/dev/null || true
chown -R $(id -u):$(id -g) frontend/php-app/bootstrap/cache 2>/dev/null || true
echo "✅ Права исправлены"

# Проверка статуса
echo ""
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
