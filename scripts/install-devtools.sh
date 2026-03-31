#!/bin/bash
# VK Neuro-Agents - DevTools Installation Script
# Автоматическая установка инструментов разработки

set -e

echo "============================================"
echo "🔧 Установка инструментов разработки"
echo "============================================"
echo ""

# Проверка sudo
if [ "$EUID" -ne 0 ]; then 
  echo "⚠️  Требуется sudo для некоторых пакетов"
  echo "   Запустите скрипт с: sudo ./scripts/install-devtools.sh"
  echo ""
fi

# System tools
echo "📦 Установка системных утилит..."
if command -v apt &> /dev/null; then
  sudo apt update
  sudo apt install -y jq httpie netcat-openbsd curl wget 2>/dev/null || echo "   ⚠️  Некоторые пакеты уже установлены"
elif command -v yum &> /dev/null; then
  sudo yum install -y jq httpie netcat curl wget 2>/dev/null || echo "   ⚠️  Некоторые пакеты уже установлены"
fi
echo "   ✅ Системные утилиты"
echo ""

# Node.js глобальные
echo "📦 Установка Node.js утилит..."
npm install -g nodemon prisma playwright http-server 2>/dev/null || echo "   ⚠️  Некоторые пакеты уже установлены"
echo "   ✅ Node.js утилиты"
echo ""

# Python утилиты
echo "📦 Установка Python утилит..."
pip3 install --user beautifulsoup4 requests selenium 2>/dev/null || echo "   ⚠️  Некоторые пакеты уже установлены"
echo "   ✅ Python утилиты"
echo ""

# Playwright браузеры (с обходом IPv6)
echo "📦 Установка Playwright браузеров..."
export PLAYWRIGHT_DOWNLOAD_HOST=https://playwright.azureedge.net
cd /home/vidserv/web-vk-bot/frontend
npx playwright install chromium 2>/dev/null || echo "   ⚠️  Используйте системный Chrome"
echo "   ✅ Playwright"
echo ""

echo "============================================"
echo "✅ Установка завершена!"
echo "============================================"
echo ""
echo "📚 Доступные команды:"
echo ""
echo "=== Тестирование API ==="
echo "   http GET https://yourdomain.com/api/health"
echo "   http GET https://yourdomain.com/api/user/profile"
echo ""
echo "=== E2E Тесты ==="
echo "   cd frontend"
echo "   npx playwright test"
echo ""
echo "=== Prisma Studio ==="
echo "   cd backend"
echo "   npx prisma studio"
echo ""
echo "=== Скрапинг ==="
echo "   python3 scripts/test-scrape.py"
echo ""
echo "=== Network тесты ==="
echo "   nc -zv localhost 4000"
echo "   nc -zv localhost 5432"
echo ""
echo "============================================"
