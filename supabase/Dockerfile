FROM supabase/postgres:15.1.0.117

# Копируем скрипт инициализации
COPY init-remote-access.sh /docker-entrypoint-initdb.d/01-remote-access.sh
RUN chmod +x /docker-entrypoint-initdb.d/01-remote-access.sh
