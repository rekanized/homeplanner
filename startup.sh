#!/bin/bash
set -euo pipefail

cd "$(dirname "$0")"

# Install dependencies
php /opt/php/composer/composer.phar run build

# Set permissions
chmod -R 775 storage bootstrap/cache

php artisan migrate --force --no-interaction

php artisan storage:link || true

php artisan optimize:clear
php artisan config:cache
php artisan route:cache