#!/bin/sh
set -e

# Lancer PHP-FPM en arrière-plan
php-fpm &

# Attendre que le socket PHP-FPM soit créé
while [ ! -S /var/run/php-fpm.sock ]; do
    sleep 1
done

# Donner les bonnes permissions au socket
chmod 777 /var/run/php-fpm.sock

# Exécuter les migrations et optimisations Laravel
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Lancer Nginx en avant-plan (ce qui maintient le conteneur en vie)
nginx -g 'daemon off;'
