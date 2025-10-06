#!/bin/sh
set -e

# On se place dans le bon répertoire
cd /var/www

# --- ÉTAPE 1 : PRÉPARATION DE LARAVEL ---
echo "==> Préparation de Laravel..."

# On attend que la base de données soit prête
echo "En attente de la base de données..."
timeout 30s sh -c 'until pg_isready -h $DB_HOST -p $DB_PORT -U $DB_USERNAME; do echo "Patienter..."; sleep 2; done'
echo "Base de données prête !"

# On exécute les migrations
php artisan migrate --force

# On crée les caches pour la production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# --- ÉTAPE 2 : LANCEMENT DES SERVICES ---
echo "==> Lancement des services (Nginx & PHP-FPM)..."

# On lance Supervisor, qui va démarrer Nginx et PHP-FPM
# C'est la dernière commande du script. Elle ne se termine jamais.
exec /usr/bin/supervisord -c /etc/supervisord.conf
