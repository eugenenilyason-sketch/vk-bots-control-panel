# PHP-FPM для Laravel
FROM php:8.4-fpm-bookworm

# Системные пакеты
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpq-dev zip unzip git \
    && rm -rf /var/lib/apt/lists/*

# PHP расширения
RUN docker-php-ext-install pdo pdo_pgsql pgsql

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Рабочая директория
WORKDIR /var/www/html

# Копируем ВСЕ файлы Laravel
COPY php-app/ ./

# Устанавливаем ВСЕ зависимости (включая dev для discovery)
RUN composer install --optimize-autoloader --ignore-platform-reqs --no-scripts \
    && mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data /var/www/html

# Пользователь
USER www-data

EXPOSE 9000
CMD ["php-fpm"]
