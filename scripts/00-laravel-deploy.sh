#!/usr/bin/env bash
set -e

echo "🚀 Démarrage du déploiement Laravel"

echo "📦 Running composer install..."
composer install --working-dir=/var/www/html --no-interaction --prefer-dist --optimize-autoloader

echo "✅ Vérification que vendor existe..."
ls -la /var/www/html/vendor || echo "❌ ERREUR: vendor n'existe pas!"

echo "🔑 Generating application key..."
php artisan key:generate --show --force

# ✅ CRITIQUE : Vider le cache de configuration TÔT pour que le .env et l5-swagger.php mis à jour soient lus.
echo "🧹 Clearing ALL caches (config, route, cache)..."
php artisan config:clear
php artisan route:clear
php artisan cache:clear || true
php artisan view:clear

# ✅ Créer les répertoires CORRECTS
echo "📁 Creating necessary directories..."
mkdir -p /var/www/html/storage/api-docs
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/public/docs/asset  
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/public

# ✅ Copier les assets Swagger (filet de sécurité)
echo "📦 Copying Swagger UI assets (fallback)..."
if [ -d "/var/www/html/vendor/swagger-api/swagger-ui/dist" ]; then
    echo "✅ Found Swagger UI in vendor, copying to public/docs/asset/..."
    cp -r /var/www/html/vendor/swagger-api/swagger-ui/dist/* /var/www/html/public/docs/asset/
else
    echo "❌ Swagger UI not found in vendor!"
fi

# ✅ Publier via artisan
echo "📦 Publishing L5 Swagger assets via artisan..."
php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider" --force || echo "⚠️ Vendor publish failed"

# ✅ Générer la documentation Swagger APRÈS le clear cache et la publication
echo "📖 Generating Swagger documentation..."
php artisan l5-swagger:generate

# Lancer la mise en cache de la configuration APRÈS toutes les modifications de configuration
echo "📝 Caching config..."
php artisan config:cache

echo "🛣️  Caching routes..."
php artisan route:cache

echo "🗄️  Running migrations..."
php artisan migrate:fresh --force

echo "🌱 Running seeders..."
php artisan db:seed --force || true


# ✅ Vérifications finales
echo "🔍 Final verification..."
echo "Public docs/asset directory:"
ls -la /var/www/html/public/docs/asset/ 2>/dev/null || echo "❌ docs/asset not found"

echo "Storage api-docs directory:"
ls -la /var/www/html/storage/api-docs/ 2>/dev/null || echo "❌ api-docs not found"

echo "Checking for key Swagger files:"
[ -f "/var/www/html/public/docs/asset/swagger-