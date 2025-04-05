#!/bin/bash

# Установка зависимостей
composer install --optimize-autoloader --no-dev

# Генерация ключа приложения, если его нет
if [ -z "$APP_KEY" ]; then
    php artisan key:generate
fi

# Запуск миграций
php artisan migrate --force

# Создание символических ссылок для хранилища
php artisan storage:link

# Очистка кэша
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Установка прав на директории
chmod -R 775 storage bootstrap/cache

echo "Сборка проекта завершена успешно!" 