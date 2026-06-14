#!/bin/bash

# Права на папки
chmod -R 777 /var/www/html/storage
chmod -R 777 /var/www/html/bootstrap/cache

# Очистка кэша
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Миграции
php artisan migrate --force

# Генерация оптимизированного автозагрузчика
composer dump-autoload --optimize

# Запуск PHP-FPM
php-fpm

# Запуск Nginx
nginx -g "daemon off;"