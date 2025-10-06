#!/bin/sh
set -e

# Générer la clé d'application si elle n'existe pas
# C'est une sécurité importante pour le premier lancement
php artisan key:generate --force

# Exécuter les migrations et optimisations Laravel
# C'est ici que l'environnement (.env) est disponible
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force

# Lancer le superviseur qui gère Nginx et PHP-FPM
exec /usr/bin/supervisord -c /etc/supervisord.conf
