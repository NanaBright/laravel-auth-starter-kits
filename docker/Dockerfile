# Production Dockerfile for Laravel Auth Starter Kits
FROM php:8.2-fpm-alpine AS base

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    zip \
    unzip \
    oniguruma-dev \
    libxml2-dev \
    icu-dev \
    linux-headers \
    $PHPIZE_DEPS

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Copy PHP configuration
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# ---- Dependencies Stage ----
FROM base AS dependencies

# Copy composer files
COPY composer.json composer.lock ./

# Install dependencies (no dev)
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# ---- Build Stage ----
FROM node:20-alpine AS build

WORKDIR /var/www/html

# Copy package files
COPY package.json package-lock.json* ./

# Install npm dependencies
RUN npm ci

# Copy source files for building
COPY resources ./resources
COPY vite.config.js tailwind.config.js postcss.config.js ./

# Build assets
RUN npm run build

# ---- Production Stage ----
FROM base AS production

# Create non-root user
RUN addgroup -g 1000 www && adduser -u 1000 -G www -s /bin/sh -D www

# Set working directory
WORKDIR /var/www/html

# Copy application code
COPY --chown=www:www . .

# Copy dependencies from dependencies stage
COPY --from=dependencies --chown=www:www /var/www/html/vendor ./vendor

# Copy built assets from build stage
COPY --from=build --chown=www:www /var/www/html/public/build ./public/build

# Generate optimized autoloader
RUN composer dump-autoload --optimize

# Create storage directories and set permissions
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && chown -R www:www storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Switch to non-root user
USER www

# Expose port 9000 and start php-fpm server
EXPOSE 9000

CMD ["php-fpm"]
