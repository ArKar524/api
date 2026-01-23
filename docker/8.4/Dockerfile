# syntax=docker/dockerfile:1

# ---------- Stage 1: PHP Base ----------
FROM php:8.4-fpm-bookworm AS php-base
RUN apt-get update && apt-get install -y --no-install-recommends \
  libpng-dev libzip-dev libicu-dev libxml2-dev libpq-dev unzip \
  && docker-php-ext-configure gd \
  && docker-php-ext-install -j$(nproc) pdo_pgsql pgsql intl zip gd opcache bcmath \
  && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN { \
  echo 'opcache.memory_consumption=128'; \
  echo 'opcache.interned_strings_buffer=8'; \
  echo 'opcache.max_accelerated_files=10000'; \
  echo 'opcache.validate_timestamps=0'; \
  echo 'opcache.enable_cli=1'; \
  } > /usr/local/etc/php/conf.d/opcache-optimized.ini

# ---------- Stage 2: Composer Dependencies ----------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-plugins --no-scripts --prefer-dist

# ---------- Stage 3: Frontend Build ----------
FROM node:22-bookworm-slim AS frontend
WORKDIR /app
RUN corepack enable
COPY package.json package-lock.json* pnpm-lock.yaml* yarn.lock* ./
RUN \
  if [ -f pnpm-lock.yaml ]; then pnpm i --frozen-lockfile; \
  elif [ -f yarn.lock ]; then yarn install --frozen-lockfile; \
  else npm ci; fi
COPY . .
RUN \
  if [ -f pnpm-lock.yaml ]; then pnpm build; \
  elif [ -f yarn.lock ]; then yarn build; \
  else npm run build; fi

# ---------- Stage 4: Final Runtime ----------
FROM php-base AS app
ENV APP_ENV=production APP_DEBUG=false

WORKDIR /var/www/html

# 1. Copy application skeleton
COPY --chown=www-data:www-data . .

# 2. Copy dependencies (doing this after skeleton ensures they are not overwritten)
COPY --from=vendor --chown=www-data:www-data /app/vendor ./vendor
COPY --from=frontend --chown=www-data:www-data /app/public/build ./public/build

# 3. Permissions for Laravel
RUN chmod -R 775 storage bootstrap/cache && \
  chown -R www-data:www-data storage bootstrap/cache

USER www-data

# PHP-FPM default port is 9000
EXPOSE 9000

HEALTHCHECK --interval=30s --timeout=3s \
  CMD pidof php-fpm || exit 1

CMD ["php-fpm"]