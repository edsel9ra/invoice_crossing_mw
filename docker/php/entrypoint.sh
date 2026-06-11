#!/bin/sh
set -e

if [ -z "$APP_KEY" ]; then
    APP_KEY=$(php artisan key:generate --show --force)
    export APP_KEY
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force

php-fpm
