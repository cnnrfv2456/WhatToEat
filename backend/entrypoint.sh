#!/bin/bash
set -e

echo "Waiting for database..."
while ! nc -z db 3306; do
    sleep 1
done
echo "Database is ready!"

# 若 .env 不存在，從範本建立（prod 環境沒有 bind mount .env）
if [ ! -f /var/www/.env ]; then
    cp /var/www/.env.example /var/www/.env
fi

php artisan key:generate --no-interaction --force
php artisan migrate --force
php artisan config:clear
php artisan route:clear

exec "$@"
