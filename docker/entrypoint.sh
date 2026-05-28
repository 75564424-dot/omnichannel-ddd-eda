#!/bin/sh
set -e

cd /var/www/html

if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
fi

if [ -z "${APP_KEY:-}" ] || [ "${APP_KEY}" = "base64:" ]; then
    php artisan key:generate --force --no-interaction 2>/dev/null || true
fi

mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || true

if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    touch "${DB_DATABASE:-storage/database.sqlite}" 2>/dev/null || true
fi

php artisan package:discover --ansi 2>/dev/null || true

ROLE="${DOCKER_APP_ROLE:-web}"
if [ "$ROLE" = "worker" ] || [ "$ROLE" = "scheduler" ]; then
    echo "Skipping migrate/cache for role=${ROLE}"
    exec "$@"
fi

echo "Waiting for database..."
for i in $(seq 1 30); do
    if php artisan migrate:status --no-interaction >/dev/null 2>&1; then
        break
    fi
    sleep 2
done

php artisan migrate --force --no-interaction

if [ "${APP_ENV:-local}" = "production" ]; then
    php artisan config:cache --no-interaction 2>/dev/null || true
    php artisan route:cache --no-interaction 2>/dev/null || true
    php artisan view:cache --no-interaction 2>/dev/null || true
fi

exec "$@"
