#!/bin/sh
# Container entrypoint: prepares the app on first start, then serves it.
set -e

if [ ! -f .env ]; then
    echo "Creating .env from .env.example"
    cp .env.example .env
fi

composer install --no-interaction --prefer-dist --no-progress

if ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --force
fi

if [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
fi

php artisan migrate --force

if ! grep -q '^OMDB_API_KEY=.\+' .env; then
    echo ""
    echo "WARNING: OMDB_API_KEY is empty in .env - searches will fail until you add one."
    echo "Get a free key at https://www.omdbapi.com/apikey.aspx"
    echo ""
fi

echo "App running at http://localhost:${APP_PORT:-8000}"
exec php artisan serve --host=0.0.0.0 --port=8000
