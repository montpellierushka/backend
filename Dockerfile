FROM php:8.2-fpm

# Установка системных зависимостей
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx \
    supervisor

# Очистка кэша apt
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Установка расширений PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Установка composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Настройка рабочей директории
WORKDIR /var/www

# Копирование файлов проекта
COPY . .

# Установка зависимостей composer
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Настройка прав доступа
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage

# Копирование конфигурации Nginx
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Копирование конфигурации supervisor
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Копирование скрипта запуска
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Замена переменных окружения\n\
sed -i "s|TELEGRAM_BOT_TOKEN=.*|TELEGRAM_BOT_TOKEN=${TELEGRAM_BOT_TOKEN}|g" .env\n\
sed -i "s|TELEGRAM_WEBHOOK_URL=.*|TELEGRAM_WEBHOOK_URL=${TELEGRAM_WEBHOOK_URL}|g" .env\n\
sed -i "s|TELEGRAM_BOT_USERNAME=.*|TELEGRAM_BOT_USERNAME=${TELEGRAM_BOT_USERNAME}|g" .env\n\
sed -i "s|DB_HOST=.*|DB_HOST=${DB_HOST}|g" .env\n\
sed -i "s|DB_PORT=.*|DB_PORT=${DB_PORT}|g" .env\n\
sed -i "s|DB_DATABASE=.*|DB_DATABASE=${DB_DATABASE}|g" .env\n\
sed -i "s|DB_USERNAME=.*|DB_USERNAME=${DB_USERNAME}|g" .env\n\
sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=${DB_PASSWORD}|g" .env\n\
\n\
# Открываем порт 80
EXPOSE 80

# Запускаем supervisor
CMD ["/usr/local/bin/start.sh"] 