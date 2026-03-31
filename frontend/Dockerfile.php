# Используем готовый образ с расширениями
FROM php:8.4-fpm-bookworm

# Копируем системные пакеты из кэша (если есть)
RUN set -eux; \
    apt-get update || true; \
    apt-get install -y --no-install-recommends \
        libpq-dev \
        zip \
        unzip \
        git \
    || echo "Using pre-installed packages"; \
    rm -rf /var/lib/apt/lists/*

# Установка расширений PHP
RUN docker-php-ext-install pdo pdo_pgsql pgsql

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Настройка рабочего каталога
WORKDIR /var/www/html

# Копируем composer.json и composer.lock для кэширования
COPY php-app/composer.json php-app/composer.lock ./

# Устанавливаем зависимости (до монтирования volume)
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs && \
    chown -R www-data:www-data /var/www/html

# Переключение на пользователя www-data
USER www-data

EXPOSE 9000

CMD ["php-fpm"]
