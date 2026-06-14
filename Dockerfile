FROM php:8.4-fpm

# Системные зависимости
RUN apt-get update && apt-get install -y --no-install-recommends \
    git curl libpng-dev libjpeg-dev libfreetype6-dev \
    libonig-dev libxml2-dev zip unzip nginx libzip-dev \
    default-libmysqlclient-dev supervisor \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

# Устанавливаем зависимости
ENV COMPOSER_MEMORY_LIMIT=-1
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --optimize-autoloader --no-dev --no-interaction

# Права
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 777 /var/www/html/storage && \
    chmod -R 777 /var/www/html/bootstrap/cache

# Nginx конфиг (слушает порт из переменной PORT)
COPY docker/nginx.conf /etc/nginx/sites-available/default

# Скрипт запуска
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# ВАЖНО: не фиксируем порт, используем переменную
ENV PORT=8080
EXPOSE 8080

CMD ["/usr/local/bin/start.sh"]