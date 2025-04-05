FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
      apt-utils \
      libpq-dev \
      libpng-dev \
      libzip-dev \
      zip unzip \
      git && \
      docker-php-ext-install pdo_mysql && \
      docker-php-ext-install bcmath && \
      docker-php-ext-install gd && \
      docker-php-ext-install zip && \
      apt-get clean && \
      rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY ./_docker/app/php.ini /usr/local/etc/php/conf.d/php.ini

# Install composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sS https://getcomposer.org/installer | php -- \
    --filename=composer \
    --install-dir=/usr/local/bin

WORKDIR /var/www

# Копирование .env.example в .env и настройка переменных окружения
COPY .env.example .env
RUN sed -i "s|TELEGRAM_BOT_TOKEN=.*|TELEGRAM_BOT_TOKEN=\${TELEGRAM_BOT_TOKEN}|g" .env && \
    sed -i "s|TELEGRAM_WEBHOOK_URL=.*|TELEGRAM_WEBHOOK_URL=\${TELEGRAM_WEBHOOK_URL}|g" .env && \
    sed -i "s|TELEGRAM_BOT_USERNAME=.*|TELEGRAM_BOT_USERNAME=\${TELEGRAM_BOT_USERNAME}|g" .env && \
    sed -i "s|DB_HOST=.*|DB_HOST=\${DB_HOST}|g" .env && \
    sed -i "s|DB_PORT=.*|DB_PORT=\${DB_PORT}|g" .env && \
    sed -i "s|DB_DATABASE=.*|DB_DATABASE=\${DB_DATABASE}|g" .env && \
    sed -i "s|DB_USERNAME=.*|DB_USERNAME=\${DB_USERNAME}|g" .env && \
    sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=\${DB_PASSWORD}|g" .env 