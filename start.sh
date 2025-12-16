#!/bin/bash

cd laravel

echo "Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader

echo "Setting permissions..."
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage
chmod -R 775 bootstrap/cache

echo "Creating storage directories..."
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/cache
mkdir -p storage/logs

echo "=== DATABASE CONFIGURATION DEBUG ==="
echo "DB_CONNECTION: $DB_CONNECTION"
echo "DB_HOST: $DB_HOST"
echo "DB_PORT: $DB_PORT"
echo "DB_DATABASE: $DB_DATABASE"
echo "DB_USERNAME: $DB_USERNAME"
echo "DB_PASSWORD: ${DB_PASSWORD:0:5}..." 

echo ""
echo "Testing database connection..."
if php artisan db:show; then
    echo "✓ Database connection successful!"
else
    echo "✗ Database connection FAILED - check your DB variables"
    echo "Attempting to continue anyway..."
fi

echo ""
echo "Running migrations..."
if php artisan migrate --force; then
    echo "✓ Migrations completed successfully!"
else
    echo "✗ Migration FAILED"
    echo "Attempting to continue anyway..."
fi

echo "Clearing and caching config..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

echo "Checking Laravel installation..."
php artisan --version

echo "Listing storage directory..."
ls -la storage/

echo "Checking logs..."
touch storage/logs/laravel.log
chmod 777 storage/logs/laravel.log

echo "Starting server..."
php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
