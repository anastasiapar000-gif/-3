#!/bin/bash
# Применяем миграции (force нужен для продакшена)
php artisan migrate --force

# Запускаем PHP-FPM и Nginx в фоновом режиме
php-fpm -D &
nginx -g "daemon off;"