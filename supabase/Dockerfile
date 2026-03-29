FROM supabase/postgres:15.1.0.117

# Copy SSL files
COPY ssl/server.key /var/lib/postgresql/ssl/server.key
COPY ssl/server.crt /var/lib/postgresql/ssl/server.crt

# Set correct permissions
RUN chown postgres:postgres /var/lib/postgresql/ssl/server.key /var/lib/postgresql/ssl/server.crt && \
    chmod 600 /var/lib/postgresql/ssl/server.key && \
    chmod 644 /var/lib/postgresql/ssl/server.crt

# Copy SSL initialization script
COPY ssl-init.sh /docker-entrypoint-initdb.d/00-ssl-init.sh
RUN chmod +x /docker-entrypoint-initdb.d/00-ssl-init.sh

# Default command with SSL
CMD ["postgres", "-c", "ssl=on", "-c", "ssl_cert_file=/var/lib/postgresql/ssl/server.crt", "-c", "ssl_key_file=/var/lib/postgresql/ssl/server.key", "-c", "ssl_min_protocol_version=TLSv1.2", "-c", "ssl_ciphers=HIGH:MEDIUM:+3DES:!aNULL"]
