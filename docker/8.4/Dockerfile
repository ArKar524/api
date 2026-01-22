FROM php:8.4-cli-bookworm

ARG NODE_VERSION=22
ARG POSTGRES_VERSION=18

WORKDIR /var/www/html
ENV DEBIAN_FRONTEND=noninteractive

# System deps
RUN apt-get update && apt-get install -y --no-install-recommends \
    git curl ca-certificates unzip zip supervisor \
    libpng-dev libzip-dev libicu-dev libxml2-dev libonig-dev \
    libpq-dev \
    dnsutils nano \
  && rm -rf /var/lib/apt/lists/*

# PHP extensions (core)
RUN docker-php-ext-configure intl \
  && docker-php-ext-install -j$(nproc) \
    pdo_pgsql pgsql \
    mbstring xml intl bcmath zip gd opcache

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Node (optional - only if you REALLY need it inside this container)
RUN curl -fsSL https://deb.nodesource.com/setup_${NODE_VERSION}.x | bash - \
  && apt-get update && apt-get install -y --no-install-recommends nodejs \
  && npm i -g npm pnpm \
  && rm -rf /var/lib/apt/lists/*

# PostgreSQL client (optional)
RUN apt-get update && apt-get install -y --no-install-recommends postgresql-client-${POSTGRES_VERSION} \
  && rm -rf /var/lib/apt/lists/*

# Supervisor config
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 80
CMD ["/usr/bin/supervisord","-n"]
