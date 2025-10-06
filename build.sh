#!/usr/bin/env bash
set -o errexit
set -o xtrace  # Affiche chaque commande pour debug

# Installer les dépendances PHP
composer install --no-dev --optimize-autoloader

# Appliquer les migrations en force
php artisan migrate --force

# Mettre en cache les configurations, routes et vues
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Créer le lien symbolique pour le storage
php artisan storage:link
