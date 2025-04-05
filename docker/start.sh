#!/bin/bash

# Ожидание доступности MySQL
if [ ! -z "$DB_HOST" ]; then
    until nc -z -v -w30 $DB_HOST 3306; do
        echo "Waiting for database connection..."
        sleep 5
    done
fi

# Очистка кэша конфигурации
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Миграции базы данных
php artisan migrate --force

# Запуск supervisor
/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf 