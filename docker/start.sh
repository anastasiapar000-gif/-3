#!/bin/bash

# Заменяем ${PORT} в конфиге nginx на реальный порт
sed -i "s/\${PORT:-8080}/${PORT:-8080}/g" /etc/nginx/sites-available/default

# Права
chmod -R 777 /var/www/html/storage
chmod -R 777 /var/www/html/bootstrap/cache

# Очистка кэша
php artisan config:clear
php artisan cache:clear

# Миграции
php artisan migrate --force

# Запуск PHP-FPM в фоне
php-fpm -D

# Запуск Nginx на порту из переменной
nginx -g "daemon off;"