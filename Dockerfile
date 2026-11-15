FROM php:8.2-cli
RUN apt-get update && apt-get install -y \
    git unzip libsqlite3-dev libzip-dev \
    && docker-php-ext-install pdo_sqlite zip \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist
COPY . .

RUN composer dump-autoload --optimize \
    && cp .env.example .env \
    && touch database/database.sqlite \
    && php artisan key:generate \
    && chmod -R 775 storage bootstrap/cache database

EXPOSE 8000

CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8000