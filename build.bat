@echo off

REM Установка зависимостей без dev-пакетов
composer install --no-dev --optimize-autoloader

REM Очистка кэша
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

REM Оптимизация
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

REM Создание символической ссылки для storage
php artisan storage:link

echo Сборка проекта завершена успешно! 