#!/bin/sh
set -e

# 1. Setup Environment
[ -f .env ] || cp .env.example .env

# Determine SQLite path mapping to the isolated volume
DB_PATH="${DB_DATABASE:-/data/database.sqlite}"
DB_DIR=$(dirname "$DB_PATH")

# 2. Automated File Provisioning
mkdir -p "$DB_DIR" storage/framework/sessions storage/framework/views storage/framework/cache storage/logs bootstrap/cache
[ -f "$DB_PATH" ] || touch "$DB_PATH"

# 3. Laravel Setup
php artisan key:generate --no-interaction
php artisan migrate --force --no-interaction
php artisan storage:link || true
php artisan optimize

# 4. Fix Permissions
chown -R www-data:www-data storage bootstrap/cache "$DB_DIR"
chmod -R 775 storage bootstrap/cache "$DB_DIR"

echo "🚀 Homeplanner is ready. Starting server..."
php artisan schedule:work > /dev/null 2>&1 &
exec "$@"
