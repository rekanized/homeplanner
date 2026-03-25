#!/bin/sh
set -e

# Set permissions for storage and cache
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Ensure SQLite database exists
mkdir -p database
touch database/database.sqlite
chown www-data:www-data database/database.sqlite

# Auto-generate APP_KEY if not set
if [ -z "$APP_KEY" ]; then
    if [ ! -f .env ]; then
        echo "Creating .env from .env.example..."
        cp .env.example .env
    fi
    echo "Generating new APP_KEY..."
    php artisan key:generate --force
fi

echo "Running migrations..."
php artisan migrate --force --no-interaction

echo "Linking storage..."
php artisan storage:link || true

echo "Optimizing application..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache

# Start PHP-FPM
echo "Starting PHP-FPM..."
exec php-fpm
