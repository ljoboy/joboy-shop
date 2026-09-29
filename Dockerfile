# syntax=docker/dockerfile:1.10

###############################################################################
# 0. Base PHP : FrankenPHP + PHP 8.5 + extensions
#    On réutilise l'image "builder" pour le runtime afin que les extensions
#    PHP installées ici soient réellement présentes dans l'image finale.
###############################################################################
FROM dunglas/frankenphp:builder-php8.5-bookworm AS php-base

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

###############################################################################
# 1. Binaire Composer (les images FrankenPHP ne le contiennent pas)
###############################################################################
FROM composer:2 AS composer-bin

###############################################################################
# 2. Frontend assets (Bun + Vite)
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
# 3. Dépendances PHP (vendor/)
###############################################################################
FROM php-base AS vendor

WORKDIR /app

COPY --from=composer-bin /usr/bin/composer /usr/local/bin/composer

COPY composer.json composer.lock ./

RUN composer install \
    --no-autoloader \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --prefer-dist

###############################################################################
# 4. Runtime
###############################################################################
FROM php-base AS runtime

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
COPY --from=composer-bin /usr/bin/composer /usr/local/bin/composer

RUN chmod +x /usr/local/bin/app-entrypoint

COPY --from=vendor /app/vendor ./vendor
COPY . .
COPY --from=assets /app/public/build ./public/build

# Génération d'un .env pendant le build, à partir de .env.example.
# Aucun secret n'est injecté ici : les valeurs réelles proviennent des
# variables d'environnement du conteneur (docker compose / Dokploy) qui ont
# priorité sur le .env, et l'entrypoint réinjecte le tout au démarrage.
ARG APP_NAME="Joboy Shop"
ARG APP_ENV=production
ARG APP_DEBUG=false
ARG APP_URL=http://localhost
ARG DB_CONNECTION=mariadb
ARG DB_HOST=mariadb
ARG DB_PORT=3306
ARG DB_DATABASE=joboy_shop
ARG DB_USERNAME=joboy

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
       /usr/local/bin/app-entrypoint env:build

RUN composer dump-autoload --classmap-authoritative --no-dev --no-interaction \
    && php artisan package:discover --ansi \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80

USER www-data

ENTRYPOINT ["/usr/local/bin/app-entrypoint"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
