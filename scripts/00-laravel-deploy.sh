#!/usr/bin/env bash
set -e

echo "Running composer"
composer global require hirak/prestissimo
composer install --no-dev --working-dir=/var/www/html

echo "generating application key..."
php artisan key:generate --show

echo "Caching config..."
php artisan config:cache

echo "Caching routes..."
php artisan route:cache

echo "Running migrations..."
php artisan migrate --force
php artisan db:seed --force

# ✅ NOUVEAUTÉ : Créer un lien symbolique pour voir les logs Laravel dans Render
echo "📋 Configuration des logs Laravel pour Render..."

# Créer un lien symbolique de laravel.log vers stderr
rm -f /var/www/html/storage/logs/laravel.log
ln -sf /dev/stderr /var/www/html/storage/logs/laravel.log

# Afficher les derniers logs s'ils existent
echo "📝 Logs Laravel existants:"
ls -la /var/www/html/storage/logs/ || true
tail -n 50 /var/www/html/storage/logs/*.log 2>/dev/null || echo "Aucun log existant"

echo "✅ Déploiement terminé - Les logs Laravel seront visibles dans Render"
# Lier les logs Laravel vers stderr pour Render
rm -f /var/www/html/storage/logs/laravel.log
ln -sf /dev/stderr /var/www/html/storage/logs/laravel.log