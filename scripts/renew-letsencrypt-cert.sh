#!/bin/bash
# Скрипт автоматического обновления SSL сертификата Let's Encrypt

set -e

CERT_DIR="./supabase/ssl"
DOMAIN="${1:-yourdomain.com}"

echo "🔄 Обновление SSL сертификата Let's Encrypt"
echo "============================================"
echo ""

# Проверка certbot
if ! command -v certbot &> /dev/null; then
    echo "❌ certbot не найден. Установите certbot."
    exit 1
fi

# Обновление сертификата
echo "🔐 Обновление сертификата..."
certbot renew --quiet

if [ $? -eq 0 ]; then
    echo "✅ Сертификат обновлен!"
else
    echo "⚠️  Сертификат не требует обновления или ошибка"
fi

echo ""
echo "📋 Копирование сертификатов..."

CERT_PATH="/etc/letsencrypt/live/$DOMAIN"

if [ -f "$CERT_PATH/fullchain.pem" ] && [ -f "$CERT_PATH/privkey.pem" ]; then
    sudo cp "$CERT_PATH/fullchain.pem" "$CERT_DIR/server.crt"
    sudo cp "$CERT_PATH/privkey.pem" "$CERT_DIR/server.key"
    
    sudo chmod 644 "$CERT_DIR/server.crt"
    sudo chmod 600 "$CERT_DIR/server.key"
    sudo chown root:root "$CERT_DIR/server.crt" "$CERT_DIR/server.key"
    
    echo "✅ Сертификаты скопированы"
else
    echo "❌ Сертификаты не найдены"
    exit 1
fi

echo ""
echo "🚀 Перезапуск PostgreSQL..."
cd "$(dirname "$0")/.."
docker compose restart supabase

echo ""
echo "✅ Проверка SSL..."
docker exec supabase psql -U postgres -d vk_bot -c "SHOW ssl;" 2>&1 | grep "on" && \
  echo "✅ SSL включен!" || echo "⚠️  Проверьте логи PostgreSQL"

echo ""
echo "============================================"
echo "✅ SSL сертификат обновлен!"
echo "📅 Следующее обновление: через 90 дней"
echo "============================================"
