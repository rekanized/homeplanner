FROM php:8.3-fpm-bookworm

# Install system dependencies and PHP extensions
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libsqlite3-dev \
        libonig-dev \
        libxml2-dev \
        libzip-dev \
        libicu-dev \
        zip \
        curl \
    && docker-php-ext-configure intl \
    && docker-php-ext-install pdo_sqlite mbstring dom xml zip bcmath pcntl intl \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# 1. Optimize dependencies installation (Layered approach)
COPY composer.json composer.lock ./

# Pre-create Laravel's required directories to avoid permission issues during discovery
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# 2. Run composer install
# We use --no-autoloader first to install dependencies while ignoring scripts that need the full app context
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction

# 3. Copy application files
COPY . .

# 4. Finalize composer (Autoloading and Scripts)
# Now that files are present, we can generate the final autoloader and run discovery safely
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Ensure final permissions
RUN chmod +x docker/entrypoint.sh \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000

ENTRYPOINT ["sh", "/app/docker/entrypoint.sh"]
CMD ["php-fpm"]
