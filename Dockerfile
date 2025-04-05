FROM composer:2.6 as composer
WORKDIR /app
COPY . .
RUN composer install --no-dev --optimize-autoloader

FROM php:8.2-fpm
WORKDIR /app

# Установка системных зависимостей
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    curl \
    nginx \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql

# Установка Node.js и npm
RUN curl -sL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm

# Копирование файлов из composer stage
COPY --from=composer /app /app

# Копирование конфигурации Nginx
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Установка прав
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Создание скрипта для запуска
RUN echo '#!/bin/bash\n\
# Создание необходимых директорий\n\
mkdir -p storage/framework/{sessions,views,cache}\n\
mkdir -p storage/logs\n\
\n\
# Копирование .env.example в .env\n\
cp .env.example .env\n\
\n\
# Замена переменных окружения\n\
sed -i "s|DB_HOST=.*|DB_HOST=${DB_HOST}|g" .env\n\
sed -i "s|DB_PORT=.*|DB_PORT=${DB_PORT}|g" .env\n\
sed -i "s|DB_DATABASE=.*|DB_DATABASE=${DB_DATABASE}|g" .env\n\
sed -i "s|DB_USERNAME=.*|DB_USERNAME=${DB_USERNAME}|g" .env\n\
sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=${DB_PASSWORD}|g" .env\n\
\n\
# Генерация ключа приложения\n\
php artisan key:generate\n\
\n\
# Запуск миграций\n\
php artisan migrate --force\n\
\n\
# Создание символической ссылки для storage\n\
php artisan storage:link\n\
\n\
# Очистка и кэширование\n\
php artisan config:cache\n\
php artisan route:cache\n\
php artisan view:cache\n\
\n\
# Установка прав на директории\n\
chown -R www-data:www-data storage bootstrap/cache\n\
chmod -R 775 storage bootstrap/cache\n\
\n\
# Настройка Nginx\n\
rm -f /etc/nginx/sites-enabled/default\n\
\n\
# Запуск PHP-FPM и Nginx\n\
service nginx start\n\
php-fpm' > /app/start.sh \
    && chmod +x /app/start.sh

# Открытие портов
EXPOSE 80 443

# Запуск приложения
CMD ["/app/start.sh"] 