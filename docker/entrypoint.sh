#!/bin/sh
set -e

# Se placer dans le répertoire de l'application
cd /var/www

echo "==> Configuration Nginx avec PORT=$PORT..."
# Remplacer ${PORT} dans la config Nginx par la valeur réelle
envsubst '${PORT}' < /etc/nginx/conf.d/default.conf > /etc/nginx/conf.d/default.conf.tmp
mv /etc/nginx/conf.d/default.conf.tmp /etc/nginx/conf.d/default.conf

echo "==> Vérification des variables d'environnement..."
echo "APP_KEY=${APP_KEY:0:20}..." # Affiche les 20 premiers caractères
echo "DB_HOST=$DB_HOST"
echo "DB_DATABASE=$DB_DATABASE"
echo "DB_USERNAME=$DB_USERNAME"

echo "==> Test de connexion à la base de données..."
if ! php artisan db:show 2>&1; then
    echo "ERREUR: Impossible de se connecter à la base de données"
    echo "Démarrage quand même des services pour diagnostic..."
else
    echo "==> Connexion DB réussie, exécution des migrations..."
    php artisan migrate --force || echo "ATTENTION: Les migrations ont échoué mais on continue..."
fi

echo "==> Mise en cache des configurations..."
php artisan config:cache || echo "ATTENTION: config:cache a échoué"
php artisan route:cache || echo "ATTENTION: route:cache a échoué"
php artisan view:cache || echo "ATTENTION: view:cache a échoué"

echo "==> Configuration des permissions..."
chown -R www-data:www-data /var/www
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

echo "==> Lancement des services..."
exec /usr/bin/supervisord -c /etc/supervisord.conf