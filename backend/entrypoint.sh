#!/bin/bash
set -e

echo "Waiting for database..."
while ! nc -z db 3306; do
    sleep 1
done
echo "Database is ready!"

php artisan key:generate --no-interaction --force
php artisan migrate --force
php artisan config:clear
php artisan route:clear

exec "$@"
