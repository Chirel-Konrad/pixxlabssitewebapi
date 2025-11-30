#!/usr/bin/env bash
set -e

echo "🚀 Démarrage du déploiement Laravel"

echo "📦 Running composer install..."
composer install --working-dir=/var/www/html --no-interaction --prefer-dist --optimize-autoloader

echo "✅ Vérification que vendor existe..."
ls -la /var/www/html/vendor || echo "❌ ERREUR: vendor n'existe pas!"

echo "🔑 Generating application key..."
php artisan key:generate --show --force

echo "🧹 Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan cache:clear || true

# ✅ Créer les répertoires nécessaires
echo "📁 Creating necessary directories..."
mkdir -p /var/www/html/storage/api-docs
mkdir -p /var/www/html/public/vendor
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/public/vendor

# ✅ Publier les assets Swagger (IMPORTANT !)
echo "📦 Publishing Swagger assets..."
php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider" --force

# ✅ Vérifier que les assets ont été publiés
echo "🔍 Checking published assets..."
if [ -d "/var/www/html/public/vendor/swagger-api" ]; then
    echo "✅ Swagger assets published successfully"
    ls -la /var/www/html/public/vendor/swagger-api/
else
    echo "❌ Swagger assets not found!"
fi

echo "📝 Caching config..."
php artisan config:cache

echo "🛣️  Caching routes..."
php artisan route:cache

echo "🗄️  Running migrations..."
php artisan migrate:fresh --force

echo "🌱 Running seeders..."
php artisan db:seed --force || true

# ✅ Générer la documentation Swagger
echo "📖 Generating Swagger documentation..."
php artisan l5-swagger:generate

# ✅ Vérifier que la documentation a été générée
echo "🔍 Checking generated documentation..."
if [ -f "/var/www/html/storage/api-docs/api-docs.json" ]; then
    echo "✅ Swagger documentation generated successfully"
    ls -lh /var/www/html/storage/api-docs/
else
    echo "❌ Swagger documentation not generated!"
fi

echo "📋 Configuration des logs Laravel..."
rm -f /var/www/html/storage/logs/laravel.log
ln -sf /dev/stderr /var/www/html/storage/logs/laravel.log

echo "✅ Déploiement terminé avec succès!"
echo "📂 Structure des fichiers Swagger:"
echo "Public assets:"
ls -la /var/www/html/public/vendor/ || echo "Pas d'assets publics"
echo "Documentation JSON:"
ls -la /var/www/html/storage/api-docs/ || echo "Pas de documentation"