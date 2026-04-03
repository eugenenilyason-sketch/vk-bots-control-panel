#!/bin/bash
# Скрипт запуска VK Neuro-Agents Control Panel (локальная разработка)

set -e

echo "🚀 Запуск VK Neuro-Agents Control Panel"
echo "======================================="

# Переход в директорию проекта
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR/.."

# Создание директории для логов
mkdir -p logs

# Проверка .env
if [ ! -f ".env" ]; then
    echo "⚠️  .env не найден!"
    echo "📋 Скопируйте .env.example в .env и настройте переменные:"
    echo "   cp .env.example .env"
    echo "   nano .env"
    exit 1
fi

# Загрузка переменных из .env
set -a
source .env
set +a

# Проверка обязательных переменных
REQUIRED_VARS=("POSTGRES_PASSWORD" "JWT_SECRET" "VK_CLIENT_ID" "VK_CLIENT_SECRET" "REDIS_PASSWORD")
for var in "${REQUIRED_VARS[@]}"; do
    if [ -z "${!var}" ]; then
        echo "❌ Переменная $var не установлена в .env"
        exit 1
    fi
done

echo "✅ .env загружен"

# Запуск через Docker Compose
echo ""
echo "📦 Запуск контейнеров..."
docker compose up -d

# Ожидание запуска
echo "⏳ Ожидание запуска сервисов..."
sleep 10

# Проверка здоровья сервисов
echo ""
echo "🔍 Проверка сервисов..."

# Backend health check
MAX_ATTEMPTS=10
ATTEMPT=1
BACKEND_OK=false

while [ $ATTEMPT -le $MAX_ATTEMPTS ]; do
    if docker compose exec -T backend node -e "
        const http = require('http');
        http.get('http://localhost:4000/health', res => {
            let data = '';
            res.on('data', chunk => data += chunk);
            res.on('end', () => process.exit(data.includes('ok') ? 0 : 1));
        }).on('error', () => process.exit(1));
    " 2>/dev/null; then
        BACKEND_OK=true
        break
    fi
    echo "   Backend: попытка $ATTEMPT/$MAX_ATTEMPTS..."
    sleep 3
    ATTEMPT=$((ATTEMPT + 1))
done

if [ "$BACKEND_OK" = true ]; then
    echo "✅ Backend работает"
else
    echo "❌ Backend не отвечает"
    docker compose logs backend --tail=20
    exit 1
fi

# Frontend check
if curl -sk https://localhost/ | grep -q "VK Neuro-Agents"; then
    echo "✅ Frontend работает"
else
    echo "⚠️  Frontend может не отвечать (проверьте SSL сертификаты)"
fi

# Итоги
echo ""
echo "======================================="
echo "✅ VK Neuro-Agents запущен!"
echo "======================================="
echo ""
echo "📊 Сервисы:"
docker compose ps
echo ""
echo "📝 Логи: docker compose logs -f"
echo "🛑 Остановка: docker compose down"
echo ""
