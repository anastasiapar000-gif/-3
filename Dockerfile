# 1. Базовый образ с PHP 8.2 и FPM (ОБЯЗАТЕЛЬНО ДОЛЖЕН БЫТЬ ПЕРВЫМ!)
FROM php:8.2-fpm

# 2. Устанавливаем системные зависимости и расширения PHP для MySQL
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

# 3. Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. Создаем рабочую директорию
WORKDIR /var/www/html

# 5. Копируем файлы проекта
COPY . .

# 6. Устанавливаем зависимости Laravel
RUN composer install --optimize-autoloader --no-dev

# 7. Настраиваем права доступа для папок storage и кэша
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# 8. Копируем конфигурацию Nginx (убедитесь, что файл docker/nginx.conf существует!)
COPY docker/nginx.conf /etc/nginx/sites-available/default

# 9. Открываем порт 80
EXPOSE 80

# 10. Копируем и настраиваем скрипт запуска
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# 11. Команда запуска контейнера
CMD ["/usr/local/bin/start.sh"]