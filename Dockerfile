# syntax=docker/dockerfile:1
FROM php:8.3-fpm-bookworm AS extensions

# SQLite, mbstring, DOM and XML are already provided by the official PHP image.
RUN apt-get update \
    && apt-get install -y --no-install-recommends libzip-dev libicu-dev \
    && docker-php-ext-install -j"$(nproc)" zip bcmath pcntl intl opcache

FROM php:8.3-fpm-bookworm AS runtime

RUN apt-get update \
    && apt-get install -y --no-install-recommends libzip4 libicu72 \
    && rm -rf /var/lib/apt/lists/*
COPY --from=extensions /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=extensions /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-homeplanner.ini
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/zz-homeplanner.conf

WORKDIR /app
ENV APP_NAME=Homeplanner \
    APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    LOG_LEVEL=info \
    DB_CONNECTION=sqlite \
    DB_DATABASE=/data/database.sqlite \
    SESSION_DRIVER=database \
    CACHE_STORE=database \
    QUEUE_CONNECTION=database

FROM runtime AS dependencies
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --no-progress

COPY app ./app
COPY bootstrap ./bootstrap
COPY config ./config
COPY database ./database
COPY lang ./lang
COPY public ./public
COPY resources ./resources
COPY routes ./routes
COPY artisan ./artisan
RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && DB_DATABASE=:memory: composer dump-autoload --no-dev --optimize --no-interaction \
    && composer check-platform-reqs --no-dev

FROM runtime AS app
COPY --from=dependencies /app /app
COPY docker/entrypoint.sh docker/start.sh docker/app-key.php /app/docker/
RUN chmod +x docker/start.sh \
    && mkdir -p /data storage/app/private storage/app/public \
    && ln -s /app/storage/app/public public/storage \
    && chown -R www-data:www-data /data storage bootstrap/cache

USER www-data
EXPOSE 9000
ENTRYPOINT ["sh", "/app/docker/entrypoint.sh"]
HEALTHCHECK --interval=10s --timeout=3s --start-period=60s --retries=6 \
    CMD php -r "exit(@fsockopen('127.0.0.1', 9000) ? 0 : 1);"
CMD ["/app/docker/start.sh"]
