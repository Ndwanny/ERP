FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY . .
RUN npm run build

FROM composer:2 AS composer-bin

FROM php:8.2-cli-alpine AS vendor

COPY --from=composer-bin /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
        nginx \
        supervisor \
        postgresql-libs \
        libzip \
        oniguruma \
        $PHPIZE_DEPS \
        postgresql-dev \
        libzip-dev \
        oniguruma-dev \
    && docker-php-ext-install -j"$(nproc)" bcmath mbstring opcache pdo_pgsql zip \
    && apk del $PHPIZE_DEPS postgresql-dev libzip-dev oniguruma-dev

COPY docker/php.ini /usr/local/etc/php/conf.d/99-mps.ini
COPY --from=vendor /app/vendor /var/www/html/vendor
COPY . /var/www/html
COPY --from=assets /app/public/build /var/www/html/public/build
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint

RUN chmod +x /usr/local/bin/entrypoint \
    && mkdir -p /var/www/html/storage/framework/cache/data \
               /var/www/html/storage/framework/sessions \
               /var/www/html/storage/framework/views \
               /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html

WORKDIR /var/www/html

EXPOSE 10000

ENTRYPOINT ["/usr/local/bin/entrypoint"]
