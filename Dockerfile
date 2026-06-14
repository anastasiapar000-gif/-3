# Базовый образ PHP 8.3 с FPM
FROM php:8.3-fpm

# Устанавливаем системные зависимости и расширения PHP
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx \
    libzip-dev \
    default-libmysqlclient-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Создаем рабочую директорию
WORKDIR /var/www/html

# Копируем файлы проекта
COPY . .

# Устанавливаем зависимости Laravel
ENV COMPOSER_MEMORY_LIMIT=-1
RUN composer install --optimize-autoloader --no-dev --no-interaction

# Настраиваем права доступа
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Копируем конфигурацию Nginx
COPY docker/nginx.conf /etc/nginx/sites-available/default

# Открываем порт
EXPOSE 80

# Копируем скрипт запуска
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Запускаем скрипт
CMD ["/usr/local/bin/start.sh"]