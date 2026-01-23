# syntax=docker/dockerfile:1

# ---------- Stage 1: PHP Base ----------
FROM php:8.4-fpm-bookworm AS php-base

# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev libzip-dev libicu-dev libxml2-dev libpq-dev unzip \
    && docker-php-ext-configure gd \
    && docker-php-ext-install -j$(nproc) pdo_pgsql pgsql intl zip gd opcache bcmath \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Inline OPcache configuration to avoid "file not found" errors
RUN { \
    echo 'opcache.memory_consumption=128'; \
    echo 'opcache.interned_strings_buffer=8'; \
    echo 'opcache.max_accelerated_files=10000'; \
    echo 'opcache.revalidate_freq=0'; \
    echo 'opcache.validate_timestamps=0'; \
    echo 'opcache.enable_cli=1'; \
    echo 'opcache.jit=tracing'; \
    echo 'opcache.jit_buffer_size=100M'; \
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

# Standard production environment variables
ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr

WORKDIR /var/www/html

# Copy application skeleton
COPY --chown=www-data:www-data . .

# Copy built artifacts from stages
COPY --from=vendor --chown=www-data:www-data /app/vendor ./vendor
COPY --from=frontend --chown=www-data:www-data /app/public/build ./public/build

# Ensure essential Laravel directories are writable
# We do this before switching to USER www-data
RUN chmod -R 775 storage

# Switch to the non-root user
USER www-data

# PRODUCTION PERFORMANCE: Cache Laravel config, routes, and views
# Note: This requires a valid .env or env vars set at build time if your 
# config files use env() without defaults.
# RUN php artisan config:cache && \
#     php artisan route:cache && \
#     php artisan view:cache

# PHP-FPM listens on 9000 by default. 
# 80 is for Nginx (which should be a separate container).
EXPOSE 80/tcp

# Better Healthcheck for PHP-FPM
# This checks if the FPM process is actually alive
HEALTHCHECK --interval=30s --timeout=3s \
  CMD pidof php-fpm || exit 1

CMD ["php-fpm"]