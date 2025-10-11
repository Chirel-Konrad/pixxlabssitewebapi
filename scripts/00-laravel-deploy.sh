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
echo "=== SHOWING LARAVEL LOGS ==="
ls -l storage/logs
tail -n 100 storage/logs/laravel.log || true
echo "=== DEBUG MODE: DUMPING LARAVEL EXCEPTIONS TO STDOUT ==="

# Crée un fichier d'override de config pour forcer Laravel à loguer dans stderr (visible par Render)
cat <<'EOF' > /var/www/html/config/logging.php
<?php

use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;

return [

    'default' => env('LOG_CHANNEL', 'stderr'),

    'channels' => [
        'stderr' => [
            'driver' => 'single',
            'path' => 'php://stderr',
            'level' => 'debug',
        ],

        'stack' => [
            'driver' => 'stack',
            'channels' => ['stderr'],
            'ignore_exceptions' => false,
        ],
    ],

];
EOF

echo "=== Vérification PHP-FPM ==="
ps aux | grep php-fpm || true
ls -la /run/php/ || true


echo "=== LAST 200 LINES OF STORAGE LOGS ==="
ls -la storage/logs || true
tail -n 200 storage/logs/*.log 2>/dev/null || true

echo "=== READY: ALL ERRORS WILL APPEAR IN RENDER LOGS ==="
