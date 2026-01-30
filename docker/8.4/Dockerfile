# syntax=docker/dockerfile:1

# ---------- Stage 1: PHP Base ----------
FROM php:8.4-fpm-bookworm AS php-base

# Install dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev \
    libzip-dev \
    libicu-dev \
    libxml2-dev \
    libpq-dev \
    unzip \
    && docker-php-ext-configure gd \
    && docker-php-ext-install -j$(nproc) pdo_pgsql pgsql intl zip gd opcache bcmath \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Production PHP Configuration
RUN { \
    echo 'opcache.memory_consumption=256'; \
    echo 'opcache.interned_strings_buffer=16'; \
    echo 'opcache.max_accelerated_files=20000'; \
    echo 'opcache.validate_timestamps=0'; \
    echo 'opcache.enable_cli=1'; \
    } > /usr/local/etc/php/conf.d/opcache-optimized.ini

# ---------- Stage 2: Composer Dependencies ----------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
# Install no-dev dependencies
RUN composer install --no-dev --no-interaction --no-plugins --no-scripts --prefer-dist

# ---------- Stage 3: Frontend Build ----------
FROM node:22-bookworm-slim AS frontend
WORKDIR /app
COPY package.json package-lock.json* pnpm-lock.yaml* yarn.lock* ./
RUN corepack enable && \
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
ENV APP_ENV=production \
    APP_DEBUG=false \
    TZ=UTC

WORKDIR /var/www/html

# 1. Copy application code
COPY --chown=www-data:www-data . .

# 2. Copy built dependencies
COPY --from=vendor --chown=www-data:www-data /app/vendor ./vendor
COPY --from=frontend --chown=www-data:www-data /app/public/build ./public/build

# 3. Create and set permissions for storage/cache
RUN mkdir -p storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache && \
    chown -R www-data:www-data storage bootstrap/cache

# Switch to non-root user
USER www-data

EXPOSE 9000

HEALTHCHECK --interval=30s --timeout=5s --start-period=5s --retries=3 \
    CMD fcgi-test || pidof php-fpm || exit 1

CMD ["php-fpm"]