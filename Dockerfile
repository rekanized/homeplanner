# Homeplanner - PHP-FPM Application Image
FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    sqlite-dev \
    libzip-dev \
    zip \
    unzip \
    curl \
    && docker-php-ext-install pdo pdo_sqlite zip bcmath

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /home/site/wwwroot

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set permissions
RUN chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Create SQLite database if not exists
RUN mkdir -p database \
    && touch database/database.sqlite \
    && chown www-data:www-data database/database.sqlite

# Copy startup script and make executable
COPY startup.sh /usr/local/bin/startup.sh
RUN chmod +x /usr/local/bin/startup.sh

EXPOSE 9000

CMD ["/usr/local/bin/startup.sh"]
