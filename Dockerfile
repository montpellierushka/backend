FROM php:8.3-fpm

WORKDIR /var/www

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

# Установка Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Копирование файлов проекта
COPY . .

# Установка зависимостей
RUN composer install --no-dev --optimize-autoloader

# Удаление дефолтной конфигурации Nginx
RUN rm -f /etc/nginx/sites-enabled/default \
    && rm -f /etc/nginx/conf.d/default.conf

# Копирование конфигурации Nginx и PHP-FPM
COPY nginx.conf /etc/nginx/conf.d/default.conf
COPY www.conf /usr/local/etc/php-fpm.d/www.conf

# Установка прав
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
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
sed -i "s|TELEGRAM_BOT_TOKEN=.*|TELEGRAM_BOT_TOKEN=${TELEGRAM_BOT_TOKEN}|g" .env\n\
sed -i "s|TELEGRAM_WEBHOOK_URL=.*|TELEGRAM_WEBHOOK_URL=${TELEGRAM_WEBHOOK_URL}|g" .env\n\
sed -i "s|TELEGRAM_BOT_USERNAME=.*|TELEGRAM_BOT_USERNAME=${TELEGRAM_BOT_USERNAME}|g" .env\n\
sed -i "s|TELEGRAM_WEB_APP_URL=.*|TELEGRAM_WEB_APP_URL=${TELEGRAM_WEB_APP_URL}|g" .env\n\
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
# Запуск PHP-FPM и Nginx\n\
php-fpm -D\n\
nginx -g "daemon off;"' > /var/www/start.sh \
    && chmod +x /var/www/start.sh

# Открытие портов
EXPOSE 80

# Запуск приложения
CMD ["/var/www/start.sh"] 