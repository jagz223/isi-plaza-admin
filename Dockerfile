# --- Dependencias PHP ---
FROM composer:2 AS vendor
WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

COPY . .
RUN composer dump-autoload --optimize --classmap-authoritative \
    && php artisan package:discover --ansi

# --- Build front (Vite / Inertia) ---
FROM node:22-alpine AS assets
WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY . .
COPY --from=vendor /app/vendor ./vendor
RUN npm run build

# --- Imagen final ---
FROM php:8.2-cli-alpine AS runtime

RUN apk add --no-cache \
    libpq-dev \
    icu-dev \
    oniguruma-dev \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_pgsql \
    intl \
    mbstring \
    opcache

COPY docker/php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

WORKDIR /app

COPY --from=vendor /app /app
COPY --from=assets /app/public/build /app/public/build

RUN mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENV PORT=10000
EXPOSE 10000

ENTRYPOINT ["/entrypoint.sh"]
