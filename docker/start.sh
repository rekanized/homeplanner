#!/bin/bash
set -eu

# Keep all application processes inside the app container. If any process exits,
# stop the others and let Docker's restart policy recover the whole application.
fpm_pid=
worker_pids=()

shutdown() {
    trap '' TERM INT
    if [ -n "$fpm_pid" ]; then
        kill -QUIT "$fpm_pid" 2>/dev/null || true
    fi
    if [ "${#worker_pids[@]}" -gt 0 ]; then
        kill -TERM "${worker_pids[@]}" 2>/dev/null || true
    fi
    wait || true
}

trap 'shutdown; exit 0' TERM INT

php-fpm &
fpm_pid=$!
php artisan schedule:work --no-interaction &
worker_pids+=("$!")
php artisan queue:work --sleep=3 --tries=3 --timeout=60 --no-interaction &
worker_pids+=("$!")

status=0
wait -n "$fpm_pid" "${worker_pids[@]}" || status=$?
shutdown
# Even a clean, unexpected exit requires restarting these long-running services.
if [ "$status" -eq 0 ]; then
    status=1
fi
exit "$status"
