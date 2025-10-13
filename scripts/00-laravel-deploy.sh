#!/usr/bin/env bash
set -e

echo "🚀 Démarrage du déploiement Laravel"

echo "📦 Running composer install WITH dev dependencies for seeding..."
composer install --working-dir=/var/www/html --no-interaction --prefer-dist --optimize-autoloader

echo "✅ Vérification que vendor existe..."
ls -la /var/www/html/vendor || echo "❌ ERREUR: vendor n'existe pas!"

echo "🔑 Generating application key..."
php artisan key:generate --show --force

echo "🧹 Clearing all caches BEFORE migration..."
php artisan config:clear
php artisan route:clear
php artisan cache:clear || true
php artisan view:clear || true

echo "🗄️  Running migrations with fresh database..."
php artisan migrate:fresh --seed --force

echo "🧹 Clearing caches AFTER migration..."
php artisan config:clear
php artisan route:clear
php artisan view:clear || true

echo "🧹 Removing dev dependencies..."
composer install --no-dev --working-dir=/var/www/html --no-interaction --prefer-dist --optimize-autoloader

echo "🧹 Clearing caches AFTER removing dev dependencies..."
php artisan config:clear
php artisan cache:clear || true

echo "📝 Caching config..."
php artisan config:cache

echo "🛣️  Caching routes..."
php artisan route:cache

echo "📋 Configuration des logs Laravel..."
# S'assurer que le répertoire de logs existe et a les bonnes permissions
mkdir -p /var/www/html/storage/logs
chmod -R 775 /var/www/html/storage
chown -R www-data:www-data /var/www/html/storage || true

# Supprimer le fichier laravel.log s'il existe
rm -f /var/www/html/storage/logs/laravel.log

# Créer un lien symbolique vers stderr
ln -sf /dev/stderr /var/www/html/storage/logs/laravel.log

echo "✅ Déploiement terminé avec succès!"
echo "📂 Contenu de /var/www/html:"
ls -la /var/www/html/