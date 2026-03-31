#!/bin/bash

# VK Neuro-Agents Control Panel - Deploy Script
# Деплой проекта

set -e

echo "🚀 Деплой VK Neuro-Agents Control Panel..."

# Проверка .env файла
if [ ! -f .env ]; then
    echo "❌ .env файл не найден. Запустите scripts/init.sh"
    exit 1
fi

# Pull свежих образов
echo "⬇️  Загрузка свежих образов..."
docker compose pull

# Пересборка php и backend
echo "🔨 Пересборка приложения..."
docker compose build vk-php vk-backend

# Остановка старых контейнеров
echo "🛑 Остановка старых контейнеров..."
docker compose down

# Запуск новых контейнеров
echo "🚀 Запуск новых контейнеров..."
docker compose up -d

# Ожидание готовности
echo "⏳ Ожидание готовности сервисов..."
sleep 15

# Очистка кэшей Laravel
echo "🧹 Очистка кэшей Laravel..."
docker compose exec -T vk-php php artisan cache:clear || true
docker compose exec -T vk-php php artisan config:clear || true
docker compose exec -T vk-php php artisan route:clear || true
docker compose exec -T vk-php php artisan view:clear || true

# Проверка статуса
echo "📊 Статус сервисов:"
docker compose ps

# Проверка логов на ошибки
echo "📋 Последние логи:"
docker compose logs --tail=20

echo ""
echo "✅ Деплой завершён!"
echo ""
echo "🔍 Проверьте логи на наличие ошибок:"
echo "   docker compose logs -f"
echo ""
echo "🌐 Доступ:"
echo "   https://yourdomain.com/"
