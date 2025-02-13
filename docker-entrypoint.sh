#!/bin/sh

# Wait for database to be ready
echo "Waiting for database to be ready..."
while ! nc -z db 3306; do
  sleep 0.1
done
echo "Database is ready!"

# Install dependencies if vendor directory doesn't exist
if [ ! -d "vendor" ]; then
    echo "Installing composer dependencies..."
    composer install --no-interaction --optimize-autoloader --no-dev
fi

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Generate key if not set
php artisan key:generate --no-interaction --force

# Create storage link
php artisan storage:link --force

# Run migrations
echo "Running database migrations..."
php artisan migrate --force

# Optimize for production
php artisan optimize
php artisan view:cache
php artisan config:cache
php artisan route:cache

# Set correct permissions
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache

# Start PHP-FPM
echo "Starting PHP-FPM..."
php-fpm
