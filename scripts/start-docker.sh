#!/bin/bash
# Скрипт запуска VK Neuro-Agents Control Panel в Docker

set -e

echo "🚀 Запуск VK Neuro-Agents Control Panel (Docker)"
echo "================================================="

# Переход в директорию проекта
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR/.."

# Проверка Docker
if ! command -v docker &> /dev/null || ! command -v docker compose &> /dev/null; then
    echo "❌ Docker или Docker Compose не найдены!"
    exit 1
fi

# Проверка .env файла
if [ ! -f ".env" ]; then
    echo "⚠️  .env файл не найден. Создаю..."
    cp .env.example .env
    
    # Генерация секретов если не сгенерированы
    if grep -q "your_" .env; then
        echo "🔐 Генерация секретов..."
        
        # Генерация паролей и секретов
        POSTGRES_PASS=$(openssl rand -hex 32) || { echo "❌ openssl не найден. Установите openssl."; exit 1; }
        JWT_SECRET=$(openssl rand -hex 32) || { echo "❌ openssl не найден. Установите openssl."; exit 1; }
        NC_JWT_SECRET=$(openssl rand -hex 32) || { echo "❌ openssl не найден. Установите openssl."; exit 1; }
        N8N_JWT_SECRET=$(openssl rand -hex 32) || { echo "❌ openssl не найден. Установите openssl."; exit 1; }
        REDIS_PASS=$(openssl rand -hex 32) || { echo "❌ openssl не найден. Установите openssl."; exit 1; }
        
        # Замена значений в .env
        sed -i "s/POSTGRES_PASSWORD=.*/POSTGRES_PASSWORD=$POSTGRES_PASS/" .env
        sed -i "s/JWT_SECRET=.*/JWT_SECRET=$JWT_SECRET/" .env
        sed -i "s/NC_JWT_SECRET=.*/NC_JWT_SECRET=$NC_JWT_SECRET/" .env
        sed -i "s/N8N_JWT_SECRET=.*/N8N_JWT_SECRET=$N8N_JWT_SECRET/" .env
        sed -i "s/REDIS_PASSWORD=.*/REDIS_PASSWORD=$REDIS_PASS/" .env
        
        echo "✅ Секреты сгенерированы"
    fi
    
    echo "✅ .env файл создан"
    echo "⚠️  Сохраните файл .env в безопасном месте!"
fi

# Создание необходимых директорий
echo ""
echo "📁 Создание директорий..."
mkdir -p nginx/data nginx/letsencrypt
mkdir -p n8n/data n8n/workflows
mkdir -p nocodb/data
mkdir -p backend/logs

# Остановка старых контейнеров
echo ""
echo "🛑 Остановка старых контейнеров..."
docker compose down 2>/dev/null || true

# Запуск сервисов
echo ""
echo "🚀 Запуск сервисов..."
docker compose up -d

# Ожидание готовности
echo ""
echo "⏳ Ожидание готовности сервисов (30 секунд)..."
sleep 30

# Проверка статуса
echo ""
echo "📊 Статус сервисов:"
docker compose ps

# Проверка health
echo ""
echo "🏥 Проверка health check:"

# Backend health
BACKEND_HEALTH=$(docker inspect --format='{{.State.Health.Status}}' vk-backend 2>/dev/null || echo "unknown")
echo "   Backend: $BACKEND_HEALTH"

# Frontend health
FRONTEND_HEALTH=$(docker inspect --format='{{.State.Health.Status}}' vk-frontend 2>/dev/null || echo "unknown")
echo "   Frontend: $FRONTEND_HEALTH"

# Supabase health
SUPABASE_HEALTH=$(docker inspect --format='{{.State.Health.Status}}' supabase 2>/dev/null || echo "unknown")
echo "   Supabase: $SUPABASE_HEALTH"

# Итоги
echo ""
echo "================================================="
echo "✅ VK Neuro-Agents запущен в Docker!"
echo "================================================="
echo ""
echo "📊 Сервисы:"
echo "   - Frontend: http://localhost:3000"
echo "   - Backend API: http://localhost:4000"
echo "   - Nginx Proxy Manager: http://localhost:81"
echo "   - n8n: http://localhost:5678"
echo "   - NocoDB: http://localhost:8080"
echo "   - PostgreSQL: localhost:5432"
echo "   - Redis: localhost:6379"
echo ""
echo "📝 Логи:"
echo "   - docker compose logs -f"
echo "   - docker compose logs backend"
echo "   - docker compose logs frontend"
echo ""
echo "🛑 Для остановки:"
echo "   docker compose down"
echo ""
echo "🔄 Для перезапуска:"
echo "   docker compose restart"
echo ""
