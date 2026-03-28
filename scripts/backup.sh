#!/bin/bash

# VK Neuro-Agents Control Panel - Backup Script
# Бэкап базы данных и данных

set -e

BACKUP_DIR="./backups"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")

echo "🔄 Создание бэкапа..."

# Создание директории для бэкапов
mkdir -p $BACKUP_DIR

# Бэкап PostgreSQL
echo "💾 Бэкап PostgreSQL..."
docker exec supabase pg_dump -U postgres -d vk_bot > "$BACKUP_DIR/db_backup_$TIMESTAMP.sql"

# Бэкап n8n данных
echo "💾 Бэкап n8n workflows..."
tar -czf "$BACKUP_DIR/n8n_backup_$TIMESTAMP.tar.gz" ./n8n/data

# Бэкап NocoDB данных
echo "💾 Бэкап NocoDB данных..."
tar -czf "$BACKUP_DIR/nocodb_backup_$TIMESTAMP.tar.gz" ./nocodb/data

# Сжатие SQL бэкапа
echo "📦 Сжатие бэкапов..."
gzip "$BACKUP_DIR/db_backup_$TIMESTAMP.sql"

# Удаление старых бэкапов (хранить последние 7)
echo "🧹 Очистка старых бэкапов..."
ls -t $BACKUP_DIR/*.sql.gz | tail -n +8 | xargs -r rm
ls -t $BACKUP_DIR/*.tar.gz | tail -n +8 | xargs -r rm

echo "✅ Бэкап завершён: $BACKUP_DIR"
echo "📋 Файлы:"
ls -lh $BACKUP_DIR/*$TIMESTAMP*
