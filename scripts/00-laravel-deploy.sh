#!/usr/bin/env bash
echo "Running composer"
#!/usr/bin/env bash
set -e  # stoppe le script en cas d’erreur

echo "Running composer"
composer install --no-dev --prefer-dist --optimize-autoloader --working-dir=/var/www/html

echo "generating application key..."
php artisan key:generate --show

# debug friendly: clear caches (pour que .env et env vars runtime soient utilisés)
php artisan config:clear
php artisan route:clear
php artisan optimize:clear
echo "Running migrations..."
php artisan migrate --force
php artisan db:seed --force

# après tes migrations / seed
echo "=== show last 200 lines of laravel logs ==="
ls -la storage/logs || true
tail -n 200 storage/logs/*.log || true

# aussi: forcer output php-fpm / nginx si possible
php -v || true