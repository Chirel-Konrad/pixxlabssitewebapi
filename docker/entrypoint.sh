#!/bin/sh
set -e

cd /var/www

# Créer le fichier de log et définir les permissions AVANT tout le reste
touch storage/logs/laravel.log
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Attendre la base de données
echo "==> En attente de la base de données..."
timeout 30s sh -c 'until pg_isready -h $DB_HOST -p $DB_PORT -U $DB_USERNAME; do sleep 1; done'
echo "==> Base de données prête !"

# Lancer les commandes Laravel
echo "==> Préparation de l'application..."
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Lancer les services
echo "==> Lancement des services..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
