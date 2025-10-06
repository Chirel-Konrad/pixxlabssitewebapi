#!/bin/sh
set -e
cd /var/www
php artisan migrate --force
php artisan config:cache
exec /usr/bin/supervisord -c /etc/supervisord.conf
