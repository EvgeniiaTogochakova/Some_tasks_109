#!/bin/sh

set -e

LOG_FILE=/var/log/entrypoint.sh.log
echo "Starting entrypoint script: $(date)" > $LOG_FILE

log() {
    echo "$(date) - $1" >> $LOG_FILE
    echo "$1"
}

# Wait for database
log "Waiting for database to be available..."
while ! nc -z db 3306; do
    log "Database is unavailable, waiting..."
    sleep 1
done
log "Database is available."

# Check and create .env file
if [ ! -f ".env" ] || [ ! -s ".env" ]; then
    log "Creating .env file from .env.example..."
    cp .env.example .env
    chmod 644 .env
fi

# Check and generate application key
if [ -z "$(grep '^APP_KEY=' .env)" ] || [ "$(grep '^APP_KEY=' .env | cut -d '=' -f2)" = "" ]; then
    log "Generating application key..."
    php artisan key:generate --no-interaction --force >> $LOG_FILE 2>&1
    log "Application key generated."
else
    log "Application key already exists."
fi

# Set directory permissions
log "Setting directory permissions..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Install dependencies if composer.json was modified
if [ -f "composer.json" ]; then
    log "Installing dependencies..."
    composer install --no-interaction --no-progress --no-suggest >> $LOG_FILE 2>&1
fi

# Run migrations
log "Running database migrations..."
php artisan migrate --force >> $LOG_FILE 2>&1

# Clear and optimize application
log "Clearing application cache..."
php artisan cache:clear >> $LOG_FILE 2>&1
php artisan config:clear >> $LOG_FILE 2>&1
php artisan route:clear >> $LOG_FILE 2>&1
php artisan view:clear >> $LOG_FILE 2>&1

log "Optimizing application..."
php artisan config:cache >> $LOG_FILE 2>&1
php artisan route:cache >> $LOG_FILE 2>&1
php artisan view:cache >> $LOG_FILE 2>&1

log "Initialization completed. Starting PHP-FPM..."

# Start PHP-FPM as PID 1
exec php-fpm