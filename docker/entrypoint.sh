#!/bin/sh
set -e

# Se placer dans le répertoire de l'application
cd /var/www

# Exécuter les migrations et les caches
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# --- LA CORRECTION DÉFINITIVE EST ICI ---
# Changer le propriétaire de TOUS les fichiers de l'application à www-data
# Puis, donner des permissions d'écriture complètes sur les dossiers critiques.
# C'est une approche "force brute" qui garantit qu'il n'y aura aucun conflit.
chown -R www-data:www-data /var/www
chmod -R 775 /var/www/storage /var/www/bootstrap/cache
# --- FIN DE LA CORRECTION ---

# Lancer les services via Supervisor
echo "==> Lancement des services..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
