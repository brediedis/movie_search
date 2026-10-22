FROM php:8.4-cli

# pdo_sqlite, curl, mbstring and xml are part of the official image;
# git/unzip/zip are needed by Composer, intl for Laravel's Number helpers.
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libzip-dev libicu-dev \
    && docker-php-ext-install zip intl \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# The container runs as the host user (see compose.yaml), so Composer
# needs a writable home directory.
ENV COMPOSER_HOME=/tmp/composer

WORKDIR /app

EXPOSE 8000

CMD ["sh", "docker/start.sh"]
