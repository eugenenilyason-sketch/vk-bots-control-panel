#!/bin/bash
# Скрипт запуска VK Neuro-Agents Control Panel

set -e

echo "🚀 Запуск VK Neuro-Agents Control Panel"
echo "======================================="

cd /home/vidserv/web-vk-bot

# Создание директории для логов
mkdir -p logs

# Проверка Docker сервисов
echo ""
echo "📦 Проверка Docker сервисов..."
docker compose ps nginx-proxy-manager supabase 2>&1 | grep -q "healthy\|Up" || {
    echo "⚠️  Docker сервисы не запущены. Запускаю..."
    docker compose up -d nginx-proxy-manager supabase redis 2>&1 | tail -5
    sleep 10
}

# Проверка и установка зависимостей
echo ""
echo "📦 Проверка зависимостей..."

# Backend зависимости
if [ ! -d "backend/node_modules" ] || [ ! -f "backend/node_modules/.package-lock.json" ]; then
    echo "⚠️  Backend зависимости не найдены. Устанавливаю..."
    cd backend && npm install --silent 2>&1 | tail -3
    cd ..
fi

# Frontend зависимости
if [ ! -d "frontend/node_modules" ] || [ ! -f "frontend/node_modules/.package-lock.json" ]; then
    echo "⚠️  Frontend зависимости не найдены. Устанавливаю..."
    cd frontend && npm install --silent 2>&1 | tail -3
    cd ..
fi

echo "✅ Зависимости установлены"

# Запуск backend
echo ""
echo "🔧 Запуск Backend..."
cd backend

export DATABASE_URL="postgresql://postgres:ed77f303bb44e9b51ce591eb354c33cadadf9bfa0ca030302b03b898d298f75e@localhost:5432/vk_bot"

# Остановка предыдущего процесса
pkill -f "tsx src/index.ts" 2>/dev/null || true
sleep 1

# Запуск нового в фоне с перенаправлением логов
nohup npx tsx src/index.ts > /tmp/backend.log 2>&1 &
BACKEND_PID=$!
echo "✅ Backend запущен (PID: $BACKEND_PID)"

# Ждём пока backend запустится (даём больше времени)
echo "⏳ Ожидание запуска backend..."
sleep 10

# Проверка backend (несколько попыток)
MAX_ATTEMPTS=5
ATTEMPT=1
BACKEND_OK=false

while [ $ATTEMPT -le $MAX_ATTEMPTS ]; do
    if curl -s --connect-timeout 2 http://localhost:4000/health 2>/dev/null | grep -q "ok"; then
        BACKEND_OK=true
        break
    fi
    echo "   Попытка $ATTEMPT/$MAX_ATTEMPTS..."
    sleep 2
    ATTEMPT=$((ATTEMPT + 1))
done

if [ "$BACKEND_OK" = true ]; then
    echo "✅ Backend работает (http://localhost:4000)"
else
    echo "❌ Backend не отвечает"
    echo "📝 Логи backend:"
    tail -20 /tmp/backend.log
    exit 1
fi

# Запуск frontend
echo ""
echo "🎨 Запуск Frontend..."
cd ../frontend

# Остановка предыдущего процесса
pkill -f "node server.js" 2>/dev/null || true
sleep 1

# Запуск нового в фоне с перенаправлением логов
nohup node server.js > /tmp/frontend.log 2>&1 &
FRONTEND_PID=$!
echo "✅ Frontend запущен (PID: $FRONTEND_PID)"

# Ждём пока frontend запустится
sleep 5

# Проверка frontend (несколько попыток)
MAX_ATTEMPTS=3
ATTEMPT=1
FRONTEND_OK=false

while [ $ATTEMPT -le $MAX_ATTEMPTS ]; do
    if curl -s --connect-timeout 2 http://localhost:3000/ 2>/dev/null | grep -q "VK Neuro-Agents"; then
        FRONTEND_OK=true
        break
    fi
    echo "   Попытка $ATTEMPT/$MAX_ATTEMPTS..."
    sleep 2
    ATTEMPT=$((ATTEMPT + 1))
done

if [ "$FRONTEND_OK" = true ]; then
    echo "✅ Frontend работает (http://localhost:3000)"
else
    echo "⚠️  Frontend может не отвечать"
    echo "📝 Логи frontend:"
    tail -10 /tmp/frontend.log
fi

# Итоги
echo ""
echo "======================================="
echo "✅ VK Neuro-Agents запущен!"
echo "======================================="
echo ""
echo "📊 Сервисы:"
echo "   - Frontend: http://localhost:3000"
echo "   - Backend API: http://localhost:4000"
echo "   - Nginx Proxy Manager: http://localhost:81"
echo "   - PostgreSQL: localhost:5432"
echo ""
echo "📝 PID процессов:"
echo "   - Backend: $BACKEND_PID"
echo "   - Frontend: $FRONTEND_PID"
echo ""
echo "🛑 Для остановки: pkill -f 'tsx src/index.ts' && pkill -f 'node server.js'"
echo ""

# Сохранение PID
echo $BACKEND_PID > /tmp/backend.pid
echo $FRONTEND_PID > /tmp/frontend.pid
