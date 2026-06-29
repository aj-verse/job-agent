#!/bin/bash

echo "Running startup initialization scripts..."

# Optimize Laravel configuration and routes
echo "Caching Laravel config and routes..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force

echo "Initialization complete!"
