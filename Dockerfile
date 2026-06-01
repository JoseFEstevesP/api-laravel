# ============================================
# Stage 1: Composer dependencies
# ============================================
FROM composer:latest AS vendor
WORKDIR /var/www

COPY composer.json composer.lock ./
RUN COMPOSER_IGNORE_PLATFORM_REQ=1 composer install \
    --no-dev \
    --optimize-autoloader \
    --no-scripts \
    --no-interaction

# ============================================
# Stage 2: Runtime
# ============================================
FROM php:8.2-apache AS runtime

ENV APP_ENV=production \
    APP_DEBUG=false \
    PHP_OPCACHE_ENABLE=1

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libfreetype-dev \
    libjpeg62-turbo-dev \
    default-libmysqlclient-dev \
    libpq-dev \
    unzip \
    zip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo pdo_mysql pdo_pgsql iconv gettext gd \
    && docker-php-ext-enable gd

RUN groupadd --gid 1000 appgroup && \
    useradd --uid 1000 --gid appgroup --shell /bin/bash --create-home appuser

WORKDIR /var/www

# Copy vendor from composer stage
COPY --from=vendor /var/www/vendor ./vendor

# Copy app source
COPY --chown=appuser:appgroup . .

# Apache config
RUN sed -i 's|/var/www/html|/var/www/public|g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|<Directory "/var/www">|<Directory "/var/www/public">|g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|AllowOverride None|AllowOverride All|g' /etc/apache2/sites-available/000-default.conf \
    && a2enmod rewrite headers ssl \
    && chown -R appuser:appgroup /var/www

RUN mkdir -p /var/www/bootstrap/cache /var/www/storage/logs /var/www/storage/framework/{cache,sessions,views} \
    && chown -R appuser:appgroup /var/www/bootstrap/cache /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache /var/www/storage

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=10s --start-period=40s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

USER appuser

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
