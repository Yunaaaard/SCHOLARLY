#!/bin/bash

cd laravel

echo "Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader

echo "Setting permissions..."
chmod -R 755 storage bootstrap/cache

echo "Testing database connection..."
php artisan db:show || echo "Database connection failed - check your DB variables"

echo "Running migrations..."
php artisan migrate --force || echo "Migration failed - check database credentials"

echo "Clearing and caching config..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear

echo "Starting server..."
php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
