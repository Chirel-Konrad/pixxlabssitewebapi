#!/bin/sh
set -e

# Se placer dans le répertoire de l'application
cd /var/www

# Exécuter les migrations
php artisan migrate --force

# Créer les caches pour la production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Changer les permissions de manière récursive sur les dossiers critiques
# C'est l'étape la plus importante pour la stabilité
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Lancer les services via Supervisor
# exec est important pour que Supervisor devienne le processus principal
exec /usr/bin/supervisord -c /etc/supervisord.conf
