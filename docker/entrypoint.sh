#!/bin/sh
set -e

# Se placer dans le répertoire de l'application
cd /var/www

# --- LA COMMANDE LA PLUS IMPORTANTE ---
# Donner la propriété de TOUS les fichiers à www-data.
# C'est une approche "force brute" pour éliminer tout conflit.
chown -R www-data:www-data /var/www

# Maintenant, et seulement maintenant, lancer les commandes artisan en tant que www-data
su -s /bin/sh www-data -c "php artisan migrate --force"
su -s /bin/sh www-data -c "php artisan config:cache"
su -s /bin/sh www-data -c "php artisan route:cache"

# Lancer le serveur
exec /usr/bin/supervisord -c /etc/supervisord.conf
