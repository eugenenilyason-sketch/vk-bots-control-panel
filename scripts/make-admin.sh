#!/bin/bash
# Скрипт назначения роли администратора пользователю
# Использование: ./scripts/make-admin.sh <email> [role]
# role: admin (по умолчанию) или superadmin

set -e

# Проверка аргументов
if [ -z "$1" ]; then
    echo "❌ Ошибка: Укажите email пользователя"
    echo ""
    echo "Использование:"
    echo "  ./scripts/make-admin.sh <email> [role]"
    echo ""
    echo "Примеры:"
    echo "  ./scripts/make-admin.sh user@example.com"
    echo "  ./scripts/make-admin.sh user@example.com admin"
    echo "  ./scripts/make-admin.sh user@example.com superadmin"
    exit 1
fi

EMAIL="$1"
ROLE="${2:-admin}"

# Проверка роли
if [ "$ROLE" != "admin" ] && [ "$ROLE" != "superadmin" ]; then
    echo "❌ Ошибка: Роль должна быть 'admin' или 'superadmin'"
    exit 1
fi

echo "============================================"
echo "🛡️ Назначение роли: $ROLE"
echo "============================================"
echo ""

# Проверка подключения к базе
echo "📊 Проверка подключения к базе данных..."
if ! docker exec supabase pg_isready -U postgres > /dev/null 2>&1; then
    echo "❌ Ошибка: База данных недоступна"
    exit 1
fi
echo "✅ База данных доступна"
echo ""

# Поиск пользователя
echo "🔍 Поиск пользователя: $EMAIL"
USER_DATA=$(docker exec supabase psql -U postgres -d vk_bot -t -c \
  "SELECT id, username, email, role FROM users WHERE email = '$EMAIL';" 2>/dev/null)

if [ -z "$USER_DATA" ] || echo "$USER_DATA" | grep -q "^(0 rows)"; then
    echo "❌ Ошибка: Пользователь с email '$EMAIL' не найден"
    echo ""
    echo "📋 Существующие пользователи:"
    docker exec supabase psql -U postgres -d vk_bot -c \
      "SELECT id, username, email, role FROM users;"
    exit 1
fi

echo "✅ Пользователь найден:"
echo "   $USER_DATA"
echo ""

# Назначение роли
echo "🛡️ Назначение роли $ROLE..."
docker exec supabase psql -U postgres -d vk_bot -c \
  "UPDATE users SET role = '$ROLE' WHERE email = '$EMAIL';"

echo ""
echo "✅ Роль назначена!"
echo ""

# Проверка результата
echo "📋 Проверка результата:"
docker exec supabase psql -U postgres -d vk_bot -c \
  "SELECT id, username, email, role FROM users WHERE email = '$EMAIL';"

echo ""
echo "============================================"
echo "✅ Готово!"
echo "============================================"
echo ""
echo "📝 Следующие шаги:"
echo "   1. Выйдите из аккаунта"
echo "   2. Войдите снова"
echo "   3. Откройте https://lianium.ru/admin.html"
echo ""
echo "🔐 Доступные URL:"
echo "   - Админка: https://lianium.ru/admin.html"
echo "   - Настройки: https://lianium.ru/settings.html"
echo "   - Dashboard: https://lianium.ru/dashboard.html"
echo ""
