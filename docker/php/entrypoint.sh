#!/bin/bash
set -e

echo ">>> Waiting for MySQL..."
until php -r "new PDO('mysql:host=${DB_HOST};port=${DB_PORT:-3306};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}');" 2>/dev/null; do
    sleep 2
done
echo ">>> MySQL is ready."

echo ">>> Creating storage:link..."
if [ ! -L /var/www/html/public/storage ]; then
    php artisan storage:link --quiet
fi

echo ">>> Running central migrations..."
php artisan migrate --force --quiet

echo ">>> Caching config, routes, views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo ">>> App ready. Starting PHP-FPM..."
exec "$@"
