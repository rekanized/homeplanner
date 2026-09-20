#!/bin/sh
set -eu

# This image supports SQLite only. Reject other drivers before touching files.
if [ "${DB_CONNECTION:-sqlite}" != sqlite ] || [ -n "${DB_URL:-}" ]; then
    echo 'This image requires DB_CONNECTION=sqlite and an unset DB_URL.' >&2
    exit 1
fi
export DB_CONNECTION=sqlite
export DB_DATABASE="${DB_DATABASE:-/data/database.sqlite}"
case "$DB_DATABASE" in
    /*) ;;
    *) echo 'DB_DATABASE must be an absolute path on a persistent volume.' >&2; exit 1 ;;
esac

mkdir -p "$(dirname "$DB_DATABASE")"
if [ -z "${APP_KEY:-}" ]; then
    APP_KEY=$(php docker/app-key.php)
    export APP_KEY
fi

mkdir -p storage/app/private storage/app/public storage/framework/sessions \
    storage/framework/views storage/framework/cache/data storage/logs bootstrap/cache

# Clear only the local configuration cache; do not flush shared application data.
php artisan config:clear --no-interaction

if [ "${1:-}" = /app/docker/start.sh ] || [ "${1:-}" = php-fpm ]; then
    mkdir -p "$(dirname "$DB_DATABASE")"
    [ -f "$DB_DATABASE" ] || touch "$DB_DATABASE"
    php artisan migrate --force --no-interaction
    php artisan optimize --no-interaction
else
    # One-off Artisan commands also use the current container environment.
    php artisan config:cache --no-interaction
fi

exec "$@"
