#!/bin/bash
set -e

echo "=== Starting E-Pharma Service ==="

# Generate app key jika belum ada
if [ -z "$APP_KEY" ]; then
    echo "Generating APP_KEY..."
    php artisan key:generate --force
fi

# Cache config untuk performa
echo "Caching config..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Jalankan migration
echo "Running migrations..."
php artisan migrate --force

# Buat symlink storage
echo "Creating storage symlink..."
php artisan storage:link || true

# Set permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Buat log directory supervisor
mkdir -p /var/log/supervisor

echo "=== Starting services ==="
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
