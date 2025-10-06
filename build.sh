#!/bin/bash
set -e

# 1. Vérifier si .env existe
if [ ! -f .env ]; then
    echo ".env file missing, copying example..."
    cp .env.example .env
fi

# 2. Installer les dépendances PHP
composer install --no-dev --optimize-autoloader

# 3. Générer APP_KEY si absent
if ! grep -q 'APP_KEY=' .env || [ -z "$(grep APP_KEY .env | cut -d '=' -f2)" ]; then
    php artisan key:generate
fi

# 4. Permissions
chmod -R 775 bootstrap/cache storage
chown -R www-data:www-data bootstrap/cache storage

# 5. Clear et cache config
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
