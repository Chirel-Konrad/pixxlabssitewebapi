#!/bin/sh
set -e

# --- Attendre que la base de données soit prête ---
echo "==> En attente de la base de données sur $DB_HOST..."
timeout 30s sh -c 'until pg_isready -h $DB_HOST -p $DB_PORT -U $DB_USERNAME; do echo "En attente..."; sleep 2; done'
echo "==> Base de données prête !"

# --- Préparation de Laravel ---
echo "==> Préparation de Laravel..."

# Vider les anciens caches pour éviter les conflits
php artisan config:clear
php artisan route:clear
php-fpm &
php artisan view:clear

# --- CORRECTION CRUCIALE ICI ---
# S'assurer que le fichier de log existe et a les bonnes permissions
touch storage/logs/laravel.log
chown -R www-data:www-data storage
chmod -R 775 storage
# --- FIN DE LA CORRECTION ---

# Exécuter les migrations
php artisan migrate --force

# Créer les caches d'optimisation pour la production
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Lancement de l'application (Nginx + PHP-FPM)..."

# --- Lancement des services ---
# Lancer le superviseur qui gère Nginx et PHP-FPM
exec /usr/bin/supervisord -c /etc/supervisord.conf
