#!/bin/bash

echo "Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader

echo "Setting permissions..."
chmod -R 755 storage bootstrap/cache

echo "Running migrations..."
php artisan migrate --force

echo "Clearing and caching config..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Starting server..."
php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
