#!/bin/sh
set -e

# On se place dans le bon répertoire
cd /var/www

# On attend que la base de données soit prête
echo "==> En attente de la base de données..."
timeout 30s sh -c 'until pg_isready -h $DB_HOST -p $DB_PORT -U $DB_USERNAME; do sleep 2; done'
echo "==> Base de données prête !"

# On exécute les commandes Laravel nécessaires pour la production
echo "==> Préparation de l'application Laravel..."
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# On lance Supervisor, qui gère Nginx et PHP-FPM
echo "==> Lancement des services..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
