#!/bin/sh
set -e

# --- Attendre que la base de données soit prête ---
echo "==> En attente de la base de données sur $DB_HOST..."

# La boucle attendra jusqu'à 30 secondes que la base de données soit prête.
# pg_isready utilise les variables d'environnement PGHOST, PGUSER, etc.
# que Laravel utilise aussi (DB_HOST, DB_USERNAME...).
# Assurez-vous que vos variables sur Render correspondent.
timeout 30s sh -c 'until pg_isready -h $DB_HOST -p $DB_PORT -U $DB_USERNAME; do echo "En attente..."; sleep 2; done'

echo "==> Base de données prête !"

# --- Préparation de Laravel ---
# Générer la clé d'application si elle n'existe pas
php artisan key:generate --force

# Vider les anciens caches au cas où
php artisan config:clear
php artisan route:clear
php artisan view:clear

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
