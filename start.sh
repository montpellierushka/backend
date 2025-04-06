#!/bin/bash

# Генерация ключа приложения, если он не установлен
if [ -z "$APP_KEY" ]; then
    php artisan key:generate
fi

# Запуск миграций
php artisan migrate --force

# Очистка кэша
php artisan config:clear
php artisan route:clear
php artisan cache:clear

# Кэширование конфигурации и маршрутов
php artisan config:cache
php artisan route:cache

# Запуск Nginx и PHP-FPM
service nginx start
php-fpm 