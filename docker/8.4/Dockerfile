# syntax=docker/dockerfile:1

# ---------- Stage 1: PHP Base (Shared Extensions) ----------
FROM php:8.4-fpm-bookworm AS php-base

# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
  libpng-dev libzip-dev libicu-dev libxml2-dev libpq-dev unzip \
  && docker-php-ext-configure gd \
  && docker-php-ext-install -j$(nproc) pdo_pgsql pgsql intl zip gd opcache bcmath \
  && apt-get clean && rm -rf /var/lib/apt/lists/*

# ---------- Stage 2: Composer Dependencies ----------
FROM composer:2 AS vendor
WORKDIR /app

# Copy only dependency files first for better caching
COPY composer.json composer.lock ./
RUN composer install \
  --no-dev \
  --no-interaction \
  --no-plugins \
  --no-scripts \
  --prefer-dist

# ---------- Stage 3: Frontend Build ----------
FROM node:22-bookworm-slim AS frontend
WORKDIR /app

# Leverage Corepack for pnpm/yarn without manual installs
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


# Set production environment
ENV APP_ENV=production \
  APP_DEBUG=false \
  PHP_OPCACHE_VALIDATE_TIMESTAMPS=0

WORKDIR /var/www/html

# Optimize OPcache for production
COPY ./docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Copy Application Code
# 1. Copy skeleton/code
COPY --chown=www-data:www-data . .

# 2. Copy dependencies from previous stages
COPY --from=vendor --chown=www-data:www-data /app/vendor ./vendor
COPY --from=frontend --chown=www-data:www-data /app/public/build ./public/build

# Ensure storage and cache are writable
RUN chmod -R 775 storage bootstrap/cache

# Use a non-root user for security (www-data is standard for PHP-FPM)
USER www-data

EXPOSE 80/tcp

# Optional: Healthcheck to ensure the container is responding
HEALTHCHECK --interval=30s --timeout=3s \
  CMD script -e -c "php-fpm -t" || exit 1

CMD ["php-fpm"]