# syntax=docker/dockerfile:1
FROM composer:2.9.5 AS composer
FROM php:8.4-fpm-bookworm

ARG BUILD_JOBS=2
# proxy_ca is optional for ordinary Docker Desktop; managed cloud builds mount a combined CA bundle.
RUN --mount=type=secret,id=proxy_ca \
    set -eux; \
    if [ -s /run/secrets/proxy_ca ]; then \
      set -- -o Acquire::https::CaInfo=/run/secrets/proxy_ca; \
    else set --; fi; \
    apt-get "$@" update; \
    apt-get "$@" install -y --no-install-recommends git unzip libicu-dev libonig-dev libpq-dev libzip-dev; \
    docker-php-ext-install -j"$BUILD_JOBS" intl mbstring pdo_pgsql zip pcntl; \
    rm -rf /var/lib/apt/lists/*

COPY --from=composer /usr/bin/composer /usr/local/bin/composer
COPY --chmod=0644 docker/php/local.ini /usr/local/etc/php/conf.d/99-kamali.ini
COPY --chmod=0644 docker/php/pool.conf /usr/local/etc/php-fpm.d/zz-kamali.conf
WORKDIR /var/www/html
CMD ["php-fpm"]
