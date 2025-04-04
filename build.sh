#!/bin/bash

# Установка зависимостей без dev-пакетов
composer install --no-dev --optimize-autoloader

# Очистка кэша
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Оптимизация
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Установка прав на директории
chmod -R 775 storage bootstrap/cache

# Создание символической ссылки для storage
php artisan storage:link

echo "Сборка проекта завершена успешно!" 