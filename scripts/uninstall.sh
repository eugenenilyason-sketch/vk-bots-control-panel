#!/bin/bash
# VK Neuro-Agents Control Panel - Uninstall Script
# Полное удаление проекта

set -e

echo "============================================"
echo "🗑️  УДАЛЕНИЕ VK NEURO-AGENTS CONTROL PANEL"
echo "============================================"
echo ""

# Подтверждение
echo "⚠️  ВНИМАНИЕ: Это действие удалит:"
echo "   • Все Docker контейнеры"
echo "   • Все тома (базы данных, кэши)"
echo "   • Все сети"
echo "   • Логи приложения"
echo ""
read -p "Вы уверены? (y/N): " confirm

if [[ ! $confirm =~ ^[Yy]$ ]]; then
    echo "❌ Удаление отменено"
    exit 0
fi

echo ""
echo "📋 НАЧИНАЕМ УДАЛЕНИЕ..."
echo ""

# 1. Остановка контейнеров
echo "🛑 Остановка контейнеров..."
docker compose down 2>&1 || true
echo "✅ Контейнеры остановлены"
echo ""

# 2. Удаление томов
echo "🗑️  Удаление томов (БД, кэши)..."
docker compose down -v 2>&1 || true
docker volume rm vk-bots-control-panel_supabase_data 2>/dev/null || true
docker volume rm vk-bots-control-panel_redis_data 2>/dev/null || true
echo "✅ Томы удалены"
echo ""

# 3. Удаление сетей
echo "🗑️  Удаление сетей..."
docker network rm vk-bots-control-panel_vk-bot-network 2>/dev/null || true
echo "✅ Сети удалены"
echo ""

# 4. Удаление образов (опционально)
echo "🗑️  Удаление образов проекта..."
docker rmi vk-bots-control-panel-php 2>/dev/null || true
docker rmi vk-bots-control-panel-backend 2>/dev/null || true
docker rmi vk-bots-control-panel-supabase 2>/dev/null || true
echo "✅ Образы удалены"
echo ""

# 5. Очистка логов
echo "🗑️  Очистка логов..."
rm -rf backend/logs/* 2>/dev/null || true
rm -rf frontend/php-app/storage/logs/* 2>/dev/null || true
rm -rf logs/* 2>/dev/null || true
echo "✅ Логи удалены"
echo ""

# 6. Очистка SSL сертификатов
echo "🗑️  Очистка SSL сертификатов..."
rm -rf nginx/ssl/* 2>/dev/null || true
rm -rf frontend/ssl/* 2>/dev/null || true
echo "✅ SSL сертификаты удалены"
echo ""

# 7. Удаление .env
echo "🗑️  Удаление .env файла..."
rm -f .env 2>/dev/null || true
rm -f frontend/php-app/.env 2>/dev/null || true
echo "✅ .env файлы удалены"
echo ""

# 8. Очистка кэшей
echo "🗑️  Очистка кэшей..."
rm -rf frontend/php-app/bootstrap/cache/* 2>/dev/null || true
rm -rf frontend/php-app/storage/framework/cache/* 2>/dev/null || true
rm -rf frontend/php-app/storage/framework/sessions/* 2>/dev/null || true
rm -rf frontend/php-app/storage/framework/views/* 2>/dev/null || true
echo "✅ Кэши удалены"
echo ""

# 9. Проверка
echo "📊 Проверка..."
echo ""
echo "Оставшиеся контейнеры:"
docker compose ps 2>&1 || echo "  (нет)"
echo ""
echo "Оставшиеся тома:"
docker volume ls | grep vk-bots-control-panel || echo "  (нет)"
echo ""

echo "============================================"
echo "✅ УДАЛЕНИЕ ЗАВЕРШЕНО!"
echo "============================================"
echo ""
echo "📋 ЧТО УДАЛЕНО:"
echo "   ✅ Контейнеры"
echo "   ✅ Томы (БД, Redis)"
echo "   ✅ Сети"
echo "   ✅ Образы проекта"
echo "   ✅ Логи"
echo "   ✅ SSL сертификаты"
echo "   ✅ .env файлы"
echo "   ✅ Кэши"
echo ""
echo "📁 ЧТО ОСТАЛОСЬ:"
echo "   ✅ Исходный код"
echo "   ✅ Скрипты"
echo "   ✅ Документация"
echo "   ✅ .git (история git)"
echo ""
echo "🔄 ДЛЯ ПОВТОРНОЙ УСТАНОВКИ:"
echo "   1. ./scripts/init.sh"
echo "   2. docker compose up -d"
echo ""
echo "============================================"
