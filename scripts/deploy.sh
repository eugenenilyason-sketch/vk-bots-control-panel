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

# Пересборка frontend и backend
echo "🔨 Пересборка приложения..."
docker compose build frontend backend

# Остановка старых контейнеров
echo "🛑 Остановка старых контейнеров..."
docker compose down

# Запуск новых контейнеров
echo "🚀 Запуск новых контейнеров..."
docker compose up -d

# Ожидание готовности
echo "⏳ Ожидание готовности сервисов..."
sleep 20

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
