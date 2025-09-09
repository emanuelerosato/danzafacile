# Multi-stage Docker build for Laravel Scuola Danza
# Production-optimized with PHP 8.2, Nginx, and security hardening

# =============================================================================
# Build Stage: PHP Dependencies
# =============================================================================
FROM composer:2.7 as composer-build

WORKDIR /app

# Copy composer files first (better caching)
COPY composer.json composer.lock ./

# Install PHP dependencies (production only)
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --ignore-platform-reqs \
    --prefer-dist \
    --optimize-autoloader

# Copy application code
COPY . .

# Generate optimized autoloader
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev

# =============================================================================
# Build Stage: Node.js Assets
# =============================================================================
FROM node:20-alpine as node-build

WORKDIR /app

# Copy package files
COPY package*.json ./

# Install Node dependencies
RUN npm ci --only=production

# Copy source files needed for build
COPY . .
COPY --from=composer-build /app/vendor ./vendor

# Build production assets
RUN npm run build

# =============================================================================
# Production Stage: PHP-FPM + Nginx
# =============================================================================
FROM php:8.2-fpm-alpine as production

# Build arguments
ARG APP_ENV=production
ARG BUILD_NUMBER
ARG GIT_COMMIT

# Environment variables
ENV APP_ENV=$APP_ENV
ENV BUILD_NUMBER=$BUILD_NUMBER
ENV GIT_COMMIT=$GIT_COMMIT
ENV PHP_MEMORY_LIMIT=512M
ENV PHP_MAX_EXECUTION_TIME=300
ENV PHP_MAX_INPUT_TIME=300
ENV PHP_POST_MAX_SIZE=100M
ENV PHP_UPLOAD_MAX_FILESIZE=100M

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    zip \
    unzip \
    git \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libwebp-dev \
    libzip-dev \
    icu-dev \
    mysql-client \
    redis \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install \
        gd \
        zip \
        intl \
        pdo_mysql \
        bcmath \
        opcache \
        pcntl \
        posix \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/cache/apk/*

# Configure PHP for production
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.enable_cli=1'; \
    echo 'opcache.memory_consumption=256'; \
    echo 'opcache.interned_strings_buffer=16'; \
    echo 'opcache.max_accelerated_files=20000'; \
    echo 'opcache.validate_timestamps=0'; \
    echo 'opcache.save_comments=0'; \
    echo 'opcache.fast_shutdown=1'; \
    echo 'opcache.preload=/var/www/html/opcache-preload.php'; \
    echo 'opcache.preload_user=www-data'; \
} > /usr/local/etc/php/conf.d/opcache.ini

RUN { \
    echo "memory_limit=${PHP_MEMORY_LIMIT}"; \
    echo "max_execution_time=${PHP_MAX_EXECUTION_TIME}"; \
    echo "max_input_time=${PHP_MAX_INPUT_TIME}"; \
    echo "post_max_size=${PHP_POST_MAX_SIZE}"; \
    echo "upload_max_filesize=${PHP_UPLOAD_MAX_FILESIZE}"; \
    echo 'max_file_uploads=20'; \
    echo 'date.timezone=Europe/Rome'; \
    echo 'expose_php=Off'; \
    echo 'display_errors=Off'; \
    echo 'log_errors=On'; \
    echo 'error_log=/var/log/php_errors.log'; \
    echo 'session.cookie_secure=1'; \
    echo 'session.cookie_httponly=1'; \
    echo 'session.use_strict_mode=1'; \
    echo 'session.cookie_samesite=Strict'; \
} > /usr/local/etc/php/conf.d/production.ini

# Configure PHP-FPM for production
RUN { \
    echo '[www]'; \
    echo 'user = www-data'; \
    echo 'group = www-data'; \
    echo 'listen = /var/run/php-fpm.sock'; \
    echo 'listen.owner = www-data'; \
    echo 'listen.group = www-data'; \
    echo 'listen.mode = 0660'; \
    echo 'pm = dynamic'; \
    echo 'pm.max_children = 20'; \
    echo 'pm.start_servers = 3'; \
    echo 'pm.min_spare_servers = 2'; \
    echo 'pm.max_spare_servers = 5'; \
    echo 'pm.max_requests = 1000'; \
    echo 'pm.process_idle_timeout = 30s'; \
    echo 'pm.status_path = /status'; \
    echo 'ping.path = /ping'; \
    echo 'access.log = /var/log/php-fpm-access.log'; \
    echo 'slowlog = /var/log/php-fpm-slow.log'; \
    echo 'request_slowlog_timeout = 30s'; \
    echo 'catch_workers_output = yes'; \
    echo 'decorate_workers_output = no'; \
} > /usr/local/etc/php-fpm.d/production.conf

# Configure Nginx
COPY docker/production/nginx.conf /etc/nginx/nginx.conf
COPY docker/production/default.conf /etc/nginx/http.d/default.conf

# Configure Supervisor
COPY docker/production/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Create necessary directories
RUN mkdir -p \
    /var/www/html \
    /var/log/nginx \
    /var/log/supervisor \
    /var/run/php-fpm \
    && chown -R www-data:www-data \
        /var/www/html \
        /var/log/nginx \
        /var/run/php-fpm

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY --from=composer-build /app ./
COPY --from=node-build /app/public/build ./public/build
COPY --from=node-build /app/public/assets ./public/assets

# Copy production configuration
COPY docker/production/opcache-preload.php ./opcache-preload.php

# Set proper permissions
RUN chown -R www-data:www-data . \
    && find . -type f -exec chmod 644 {} \; \
    && find . -type d -exec chmod 755 {} \; \
    && chmod -R 775 storage bootstrap/cache \
    && chmod 644 .env.example

# Create health check script
RUN { \
    echo '#!/bin/sh'; \
    echo 'curl -f http://localhost/health/simple || exit 1'; \
    echo 'php artisan tinker --execute="echo \"Laravel OK\";" || exit 1'; \
} > /usr/local/bin/healthcheck.sh \
&& chmod +x /usr/local/bin/healthcheck.sh

# Create startup script
RUN { \
    echo '#!/bin/sh'; \
    echo 'set -e'; \
    echo ''; \
    echo 'echo "Starting Scuola Danza Application..."'; \
    echo ''; \
    echo '# Wait for database'; \
    echo 'until php artisan tinker --execute="DB::connection()->getPdo();" 2>/dev/null; do'; \
    echo '  echo "Waiting for database connection..."'; \
    echo '  sleep 5'; \
    echo 'done'; \
    echo ''; \
    echo '# Run migrations'; \
    echo 'php artisan migrate --force'; \
    echo ''; \
    echo '# Cache optimization'; \
    echo 'php artisan config:cache'; \
    echo 'php artisan route:cache'; \
    echo 'php artisan view:cache'; \
    echo 'php artisan event:cache'; \
    echo ''; \
    echo '# Start supervisor (nginx + php-fpm + queue workers)'; \
    echo 'exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf'; \
} > /usr/local/bin/start.sh \
&& chmod +x /usr/local/bin/start.sh

# Expose port
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
    CMD /usr/local/bin/healthcheck.sh

# Switch to www-data user for security
USER www-data

# Start the application
CMD ["/usr/local/bin/start.sh"]

# Labels
LABEL maintainer="scuoladanza@example.com" \
      version="${BUILD_NUMBER}" \
      description="Laravel Scuola Danza Management System" \
      org.opencontainers.image.source="https://github.com/emanuelerosato/scuoladanza" \
      org.opencontainers.image.revision="${GIT_COMMIT}"