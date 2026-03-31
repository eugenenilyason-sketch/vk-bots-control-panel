#!/bin/bash
# SSL initialization script for PostgreSQL

set -e

SSL_DIR="/var/lib/postgresql/ssl"
SSL_KEY="$SSL_DIR/server.key"
SSL_CERT="$SSL_DIR/server.crt"

# Check if SSL files exist
if [ -f "$SSL_KEY" ] && [ -f "$SSL_CERT" ]; then
    echo "🔐 Setting up SSL certificates..."
    
    # Set correct permissions
    chmod 600 "$SSL_KEY"
    chmod 644 "$SSL_CERT"
    chown postgres:postgres "$SSL_KEY" "$SSL_CERT"
    
    echo "✅ SSL certificates configured"
else
    echo "⚠️  SSL certificates not found, running without SSL"
fi

# Execute main command
exec "$@"
