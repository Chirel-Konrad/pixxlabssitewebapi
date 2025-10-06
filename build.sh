#!/usr/bin/env bash
set -o errexit
set -o xtrace

# Copier .env si absent
[ ! -f .env ] && cp .env.example .env

# Permissions
chmod -R 775 bootstrap/cache storage

# Installer les dépendances
composer install --no-dev --optimize-autoloader

# Générer APP_KEY si absent
php artisan key:generate --force

# Migrer la base de données
php artisan migrate --force

# Cacher config, routes et views
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Créer storage link (ignore erreur si déjà existant)
php artisan storage:link || true
