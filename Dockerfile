FROM composer:2.6 as composer
WORKDIR /app
COPY composer.json composer.lock ./
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
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql

# Установка Node.js и npm
RUN curl -sL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm

# Копирование файлов из composer stage
COPY --from=composer /app/vendor ./vendor
COPY . .

# Установка прав
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Копирование .env.example в .env
RUN mv .env.example .env

# Генерация ключа приложения
RUN php artisan key:generate

# Запуск миграций
RUN php artisan migrate --force

# Создание символической ссылки для storage
RUN php artisan storage:link

# Очистка и кэширование
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Установка зависимостей для фронтенда и сборка
RUN npm install && npm run build

# Запуск приложения
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"] 