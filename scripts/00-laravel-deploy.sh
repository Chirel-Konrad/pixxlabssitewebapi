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
php artisan view:clear

# ✅ Créer les répertoires nécessaires
echo "📁 Creating necessary directories..."
mkdir -p /var/www/html/storage/api-docs
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/public/docs/asset
mkdir -p /var/www/html/public/docs
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/public

# ✅ Copier les assets Swagger
echo "📦 Copying Swagger UI assets..."
if [ -d "/var/www/html/vendor/swagger-api/swagger-ui/dist" ]; then
    echo "✅ Found Swagger UI in vendor, copying to public/docs/asset/..."
    cp -r /var/www/html/vendor/swagger-api/swagger-ui/dist/* /var/www/html/public/docs/asset/
    echo "✅ Assets copied successfully"
    ls -la /var/www/html/public/docs/asset/
else
    echo "❌ Swagger UI not found in vendor!"
fi

# ✅ Publier les assets via artisan
echo "📦 Publishing Swagger assets via artisan..."
php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider" --force || echo "⚠️ Vendor publish failed"

# ✅ IMPORTANT : Générer la documentation AVANT le cache
echo "📖 Generating Swagger documentation..."
php artisan l5-swagger:generate

# ✅ Copier api-docs.json dans public/docs pour l'accès direct
# ✅ Copier api-docs.json dans public/docs pour l'accès direct
echo "📄 Copying api-docs.json to public/docs..."
if [ -f "/var/www/html/storage/api-docs/api-docs.json" ]; then
    cp /var/www/html/storage/api-docs/api-docs.json /var/www/html/public/docs/api-docs.json
    echo "✅ api-docs.json copied to public/docs/"
else
    echo "❌ api-docs.json not found in storage!"
fi

echo "📝 Caching config..."
php artisan config:cache

echo "🛣️  Caching routes..."
php artisan route:cache

echo "🗄️  Running migrations..."
php artisan migrate:fresh --force

echo "🌱 Running seeders..."
php artisan db:seed --force || true

# ✅ Vérifications finales
echo "🔍 Final verification..."
echo ""
echo "=== Public docs/asset directory ==="
ls -la /var/www/html/public/docs/asset/ 2>/dev/null || echo "❌ docs/asset not found"

echo ""
echo "=== Public docs directory ==="
ls -la /var/www/html/public/docs/ 2>/dev/null || echo "❌ docs not found"

echo ""
echo "=== Storage api-docs directory ==="
ls -la /var/www/html/storage/api-docs/ 2>/dev/null || echo "❌ api-docs not found"

echo ""
echo "=== Checking for key Swagger files ==="
[ -f "/var/www/html/public/docs/asset/swagger-ui.css" ] && echo "✅ swagger-ui.css found" || echo "❌ swagger-ui.css NOT found"
[ -f "/var/www/html/public/docs/asset/swagger-ui-bundle.js" ] && echo "✅ swagger-ui-bundle.js found" || echo "❌ swagger-ui-bundle.js NOT found"
[ -f "/var/www/html/public/docs/api-docs.json" ] && echo "✅ api-docs.json in public/docs found" || echo "❌ api-docs.json in public/docs NOT found"
[ -f "/var/www/html/storage/api-docs/api-docs.json" ] && echo "✅ api-docs.json in storage found" || echo "❌ api-docs.json in storage NOT found"

echo ""
echo "=== Testing api-docs.json content ==="
if [ -f "/var/www/html/public/docs/api-docs.json" ]; then
    head -n 5 /var/www/html/public/docs/api-docs.json
    echo "..."
fi

echo ""
echo "📋 Configuration des logs Laravel..."
rm -f /var/www/html/storage/logs/laravel.log
ln -sf /dev/stderr /var/www/html/storage/logs/laravel.log

echo ""
echo "✅ Déploiement terminé avec succès!"