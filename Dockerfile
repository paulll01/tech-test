FROM php:8.4-fpm

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip pkg-config libonig-dev libzip-dev \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        pcntl \
        bcmath \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-scripts --no-progress || true

COPY . .

RUN [ -f .env ] || cp .env.example .env \
  && php artisan key:generate --force

CMD ["php-fpm"]
