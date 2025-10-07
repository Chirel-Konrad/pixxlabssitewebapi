#!/bin/sh
set -e

# Se placer dans le répertoire de l'application
cd /var/www

echo "==> Configuration Nginx avec PORT=$PORT..."
# Remplacer ${PORT} dans la config Nginx par la valeur réelle
envsubst '${PORT}' < /etc/nginx/conf.d/default.conf > /etc/nginx/conf.d/default.conf.tmp
mv /etc/nginx/conf.d/default.conf.tmp /etc/nginx/conf.d/default.conf

echo "==> Exécution des migrations..."
php artisan migrate --force

echo "==> Mise en cache des configurations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Configuration des permissions..."
chown -R www-data:www-data /var/www
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

echo "==> Lancement des services..."
exec /usr/bin/supervisord -c /etc/supervisord.conf