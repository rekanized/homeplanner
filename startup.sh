#!/bin/bash

# Set permissions
chmod -R 775 storage bootstrap/cache

# Ensure SQLite database exists
touch database/database.sqlite

# Auto-generate APP_KEY if not set
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

php artisan migrate --force --no-interaction

php artisan storage:link || true

php artisan optimize:clear
php artisan config:cache
php artisan route:cache

# Start PHP-FPM
exec php-fpm