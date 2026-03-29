#!/bin/bash
# Скрипт генерации SSL сертификатов для PostgreSQL

set -e

CERT_DIR="./supabase/ssl"

echo "🔐 Генерация SSL сертификатов для PostgreSQL..."

# Создание директории
mkdir -p "$CERT_DIR"

# Генерация приватного ключа
echo "📝 Генерация приватного ключа..."
openssl genrsa -out "$CERT_DIR/server.key" 2048

# Генерация самоподписанного сертификата
echo "📝 Генерация сертификата..."
openssl req -new -x509 -key "$CERT_DIR/server.key" \
  -out "$CERT_DIR/server.crt" \
  -days 365 \
  -subj "/C=RU/ST=Moscow/L=Moscow/O=VK Neuro-Agents/CN=supabase"

# Установка правильных прав
chmod 600 "$CERT_DIR/server.key"
chmod 644 "$CERT_DIR/server.crt"

echo "✅ SSL сертификаты созданы:"
echo "   - $CERT_DIR/server.key (приватный ключ)"
echo "   - $CERT_DIR/server.crt (сертификат)"
echo ""
echo "⚠️  Сертификат самоподписанный. Для production используйте доверенный CA."
