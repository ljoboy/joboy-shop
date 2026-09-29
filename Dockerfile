# syntax=docker/dockerfile:1.10

###############################################################################
# 1. Frontend assets (Bun + Vite)
###############################################################################
FROM oven/bun:1-alpine AS assets

WORKDIR /app

COPY package.json bun.lock ./
RUN bun install --frozen-lockfile --ignore-scripts

COPY vite.config.js ./
COPY resources ./resources
COPY public ./public

RUN bun run build

###############################################################################
# 2. PHP dependencies (Composer)
###############################################################################
FROM dunglas/frankenphp:builder-php8.5-bookworm AS vendor

USER root

RUN install-php-extensions \
    bcmath \
    exif \
    gd \
    intl \
    opcache \
    pcntl \
    pdo_mysql \
    pdo_sqlite \
    zip

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-autoloader \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --prefer-dist

###############################################################################
# 3. Runtime (FrankenPHP)
###############################################################################
FROM dunglas/frankenphp:php8.5-bookworm AS runtime

USER root

WORKDIR /app

ENV APP_ENV=production \
    APP_DEBUG=false \
    APP_URL=http://localhost \
    COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1 \
    PATH="/app/vendor/bin:$PATH"

COPY docker/php/Caddyfile /etc/caddy/Caddyfile
COPY docker/php/entrypoint.sh /usr/local/bin/app-entrypoint
RUN chmod +x /usr/local/bin/app-entrypoint

COPY --from=vendor /app/vendor ./vendor
COPY . .
COPY --from=assets /app/public/build ./public/build

# Génération d'un .env pendant le build à partir de .env.example.
# Les mêmes variables sont réinjectées/overridées par l'entrypoint au démarrage,
# ce qui permet à docker compose de fournir les valeurs réelles via `environment:`.
ARG APP_NAME="Joboy Shop"
ARG APP_ENV=production
ARG APP_DEBUG=false
ARG APP_URL=http://localhost
ARG DB_CONNECTION=mariadb
ARG DB_HOST=mariadb
ARG DB_PORT=3306
ARG DB_DATABASE=joboy_shop
ARG DB_USERNAME=joboy
ARG DB_PASSWORD=joboy

RUN cp .env.example .env \
    && APP_NAME="${APP_NAME}" \
       APP_ENV="${APP_ENV}" \
       APP_DEBUG="${APP_DEBUG}" \
       APP_URL="${APP_URL}" \
       DB_CONNECTION="${DB_CONNECTION}" \
       DB_HOST="${DB_HOST}" \
       DB_PORT="${DB_PORT}" \
       DB_DATABASE="${DB_DATABASE}" \
       DB_USERNAME="${DB_USERNAME}" \
       DB_PASSWORD="${DB_PASSWORD}" \
       /usr/local/bin/app-entrypoint env:build

RUN composer dump-autoload --classmap-authoritative --no-dev --no-interaction \
    && php artisan package:discover --ansi \
    && mkdir -p storage/framework/{cache/data,sessions,views} storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80

USER www-data

ENTRYPOINT ["/usr/local/bin/app-entrypoint"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
