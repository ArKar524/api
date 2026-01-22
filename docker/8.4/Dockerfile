# ---------- Build frontend ----------
FROM node:22-bookworm-slim AS frontend
WORKDIR /app

# Copy only manifests first (better cache)
COPY package.json package-lock.json* pnpm-lock.yaml* yarn.lock* ./

# Install deps (make it PASS even with peer conflicts)
RUN \
  if [ -f pnpm-lock.yaml ]; then corepack enable && pnpm i --frozen-lockfile; \
  elif [ -f yarn.lock ]; then yarn install --frozen-lockfile; \
  elif [ -f package-lock.json ]; then npm ci --legacy-peer-deps; \
  else npm install --legacy-peer-deps; fi

# Build assets
COPY . .
RUN \
  if [ -f pnpm-lock.yaml ]; then pnpm build; \
  elif [ -f yarn.lock ]; then yarn build; \
  else npm run build; fi


# ---------- Build PHP deps ----------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader


# ---------- Runtime ----------
FROM php:8.4-fpm-bookworm AS app

WORKDIR /var/www/html
ENV APP_ENV=production
ENV APP_DEBUG=false

RUN apt-get update && apt-get install -y --no-install-recommends \
    ca-certificates curl git unzip \
    libpng-dev libzip-dev libicu-dev libxml2-dev libonig-dev \
    libpq-dev \
  && docker-php-ext-configure intl \
  && docker-php-ext-install -j$(nproc) \
      pdo_pgsql pgsql mbstring xml intl bcmath zip gd opcache \
  && rm -rf /var/lib/apt/lists/*

RUN { \
  echo "opcache.enable=1"; \
  echo "opcache.enable_cli=0"; \
  echo "opcache.validate_timestamps=0"; \
  echo "opcache.jit=0"; \
} > /usr/local/etc/php/conf.d/99-opcache.ini

COPY . .

COPY --from=vendor /app/vendor /var/www/html/vendor
COPY --from=frontend /app/public/build /var/www/html/public/build

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
