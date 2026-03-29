#!/bin/bash
# Скрипт для подключения NocoDB к PostgreSQL

set -e

echo "🔧 Подключение NocoDB к PostgreSQL..."

# Получаем IP адрес supabase
SUPABASE_IP=$(docker inspect supabase --format='{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}')
echo "📍 Supabase IP: $SUPABASE_IP"

# Получаем пароль
POSTGRES_PASS=$(grep POSTGRES_PASSWORD .env | cut -d'=' -f2)
echo "🔑 Password: ${POSTGRES_PASS:0:10}..."

# Создаём файл конфигурации для NocoDB
cat > nocodb/data/nc_db_config.json <<EOF
{
  "title": "vk_bot",
  "type": "pg",
  "config": {
    "host": "$SUPABASE_IP",
    "port": 5432,
    "user": "postgres",
    "password": "$POSTGRES_PASS",
    "database": "vk_bot"
  },
  "ssl": false
}
EOF

echo "✅ Конфигурация создана: nocodb/data/nc_db_config.json"
echo ""
echo "📋 Теперь:"
echo "1. Откройте http://localhost:8080"
echo "2. Создайте суперадмина (любые email/password)"
echo "3. Нажмите 'Connect Database'"
echo "4. Импортируйте файл: nocodb/data/nc_db_config.json"
echo ""
echo "Или используйте JSON вручную:"
cat nocodb/data/nc_db_config.json
