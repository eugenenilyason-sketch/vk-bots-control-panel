#!/bin/bash
# Скрипт получения и установки SSL сертификата Let's Encrypt для PostgreSQL

set -e

# Конфигурация
DOMAIN="${1:-}"
EMAIL="${2:-}"
CERT_DIR="./supabase/ssl"
WEBROOT="./nginx/data/www"

echo "🔐 Let's Encrypt SSL Certificate Generator for PostgreSQL"
echo "=========================================================="
echo ""

# Проверка аргументов
if [ -z "$DOMAIN" ]; then
    echo "❌ Укажите домен:"
    echo "   ./scripts/get-letsencrypt-cert.sh yourdomain.com email@example.com"
    exit 1
fi

if [ -z "$EMAIL" ]; then
    echo "❌ Укажите email:"
    echo "   ./scripts/get-letsencrypt-cert.sh yourdomain.com email@example.com"
    exit 1
fi

echo "📋 Параметры:"
echo "   Домен: $DOMAIN"
echo "   Email: $EMAIL"
echo "   Директория сертификатов: $CERT_DIR"
echo ""

# Проверка certbot
if ! command -v certbot &> /dev/null; then
    echo "⚠️  certbot не найден. Установка..."
    
    if command -v apt-get &> /dev/null; then
        sudo apt-get update
        sudo apt-get install -y certbot
    elif command -v yum &> /dev/null; then
        sudo yum install -y certbot
    else
        echo "❌ Не удалось установить certbot. Установите вручную:"
        echo "   https://certbot.eff.org/instructions"
        exit 1
    fi
fi

echo "✅ certbot найден: $(certbot --version)"
echo ""

# Создание директорий
echo "📁 Создание директорий..."
mkdir -p "$CERT_DIR"
mkdir -p "$WEBROOT"
mkdir -p "$WEBROOT/.well-known/acme-challenge"

# Получение сертификата
echo "🔐 Получение сертификата Let's Encrypt..."
echo ""

certbot certonly \
  --webroot \
  -w "$WEBROOT" \
  -d "$DOMAIN" \
  --email "$EMAIL" \
  --agree-tos \
  --non-interactive \
  --rsa-key-size 4096

if [ $? -eq 0 ]; then
    echo "✅ Сертификат успешно получен!"
else
    echo "❌ Ошибка получения сертификата"
    exit 1
fi

echo ""
echo "📋 Копирование сертификатов..."

# Копирование сертификатов
CERT_PATH="/etc/letsencrypt/live/$DOMAIN"

if [ -f "$CERT_PATH/fullchain.pem" ] && [ -f "$CERT_PATH/privkey.pem" ]; then
    # Копирование с правильными правами
    sudo cp "$CERT_PATH/fullchain.pem" "$CERT_DIR/server.crt"
    sudo cp "$CERT_PATH/privkey.pem" "$CERT_DIR/server.key"
    
    # Установка правильных прав
    sudo chmod 644 "$CERT_DIR/server.crt"
    sudo chmod 600 "$CERT_DIR/server.key"
    sudo chown root:root "$CERT_DIR/server.crt" "$CERT_DIR/server.key"
    
    echo "✅ Сертификаты скопированы:"
    echo "   - $CERT_DIR/server.crt"
    echo "   - $CERT_DIR/server.key"
else
    echo "❌ Сертификаты не найдены в $CERT_PATH"
    exit 1
fi

echo ""
echo "🔄 Пересборка образа PostgreSQL..."
cd "$(dirname "$0")/.."
docker compose build supabase

echo ""
echo "🚀 Перезапуск PostgreSQL..."
docker compose up -d supabase

echo ""
echo "⏳ Ожидание запуска PostgreSQL..."
sleep 10

echo ""
echo "✅ Проверка SSL..."
docker exec supabase psql -U postgres -d vk_bot -c "SHOW ssl;" 2>&1 | grep "on" && \
  echo "✅ SSL включен!" || echo "⚠️  Проверьте логи PostgreSQL"

echo ""
echo "=========================================================="
echo "✅ SSL сертификат Let's Encrypt установлен!"
echo ""
echo "📅 Сертификат действителен 90 дней."
echo "🔄 Для автоматического обновления добавьте в crontab:"
echo ""
echo "0 0 1 * * /usr/bin/certbot renew --quiet && \\"
echo "  cp /etc/letsencrypt/live/$DOMAIN/fullchain.pem $CERT_DIR/server.crt && \\"
echo "  cp /etc/letsencrypt/live/$DOMAIN/privkey.pem $CERT_DIR/server.key && \\"
echo "  chmod 644 $CERT_DIR/server.crt && \\"
echo "  chmod 600 $CERT_DIR/server.key && \\"
echo "  docker compose restart supabase"
echo ""
echo "=========================================================="
