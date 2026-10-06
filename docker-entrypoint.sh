#!/bin/bash
set -e

# Run migrations automatically
echo "Running database migrations..."
php artisan migrate --force

# Clear and optimize caches
echo "Optimizing application..."
php artisan optimize:clear
php artisan optimize

# Start Apache in the foreground
echo "Starting Apache..."
exec apache2-foreground
