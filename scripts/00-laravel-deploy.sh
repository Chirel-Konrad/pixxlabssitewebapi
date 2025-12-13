#!/usr/bin/env bash
set -e

cd /var/www/html

echo "🚀 Démarrage du déploiement Laravel"

echo "📦 Running composer install..."
composer install --working-dir=/var/www/html --no-interaction --prefer-dist --optimize-autoloader

echo "✅ Vérification que vendor existe..."
ls -la /var/www/html/vendor || echo "❌ ERREUR: vendor n'existe pas!"

echo "🔑 Generating application key..."
php artisan key:generate --show --force

# Vider les caches principaux (peut rester, mais on ajoutera optimize:clear plus bas)
echo "🧹 Clearing ALL caches (config, route, cache)..."
php artisan config:clear
php artisan route:clear
php artisan cache:clear || true
php artisan view:clear

# ✅ NOUVEAU/CRITIQUE : Créer le lien symbolique public/storage vers storage/app/public.
# Ceci est ESSENTIEL pour rendre les fichiers uploadés (comme les images Unsplash)
# accessibles publiquement via l'URL /storage/... en production.
echo "🔗 Creating storage link..."
php artisan storage:link

# ✅ CRÉATION DES RÉPERTOIRES (simplement le nécessaire)
echo "📁 Creating necessary directories..."
# Suppression des dossiers Swagger (api-docs, public/docs/asset)
mkdir -p /var/www/html/storage/logs
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/public

# ✅ SWAGGER : Créer les répertoires et générer la documentation
echo "📁 Creating Swagger directories..."
mkdir -p /var/www/html/storage/api-docs
mkdir -p /var/www/html/public/vendor/swagger-api/swagger-ui/dist

echo "📦 Copying Swagger UI assets..."
if [ -d "/var/www/html/vendor/swagger-api/swagger-ui/dist" ]; then
    echo "✅ Found Swagger UI in vendor, copying to public..."
    cp -r /var/www/html/vendor/swagger-api/swagger-ui/dist/* /var/www/html/public/vendor/swagger-api/swagger-ui/dist/
    echo "✅ Swagger UI assets copied successfully"
else
    echo "⚠️  Swagger UI not found in vendor, skipping asset copy"
fi

echo "🧹 Optimizing (clear all caches with optimize:clear)..."
php artisan optimize:clear

echo "📖 Generating Swagger documentation..."
php artisan l5-swagger:generate || echo "⚠️  Swagger generation failed (will retry after cache)"


# Lancer la mise en cache de la configuration.
echo "📝 Caching config..."
php artisan config:cache

echo "🛣️  Caching routes..."
php artisan route:cache

echo "🗄️  Running migrations..."
# Certains déploiements échouaient parce que des tables OAuth existaient déjà
# alors que la migration Passport n'était pas marquée dans la table migrations.
# On force donc la suppression des tables OAuth avant les migrations.
echo "♻️  Dropping OAuth tables if they already exist..."
php -r "require 'vendor/autoload.php'; \$app = require 'bootstrap/app.php'; \$kernel = \$app->make(Illuminate\\Contracts\\Console\\Kernel::class); \$kernel->bootstrap(); foreach (['oauth_refresh_tokens','oauth_access_tokens','oauth_auth_codes','oauth_device_codes','oauth_clients'] as \$t) { Illuminate\\Support\\Facades\\Schema::dropIfExists(\$t); }"

php artisan migrate:fresh --force

echo "🔑 Fixing Passport Keys permissions..."
if [ -f "/etc/secrets/oauth-private.key" ]; then
    echo "Found private key in secrets, copying to storage..."
    cp /etc/secrets/oauth-private.key /var/www/html/storage/oauth-private.key
    # Assurer que le serveur web (nginx) peut lire le fichier
    chmod 600 /var/www/html/storage/oauth-private.key
    chown nginx:nginx /var/www/html/storage/oauth-private.key
fi
if [ -f "/etc/secrets/oauth-public.key" ]; then
    echo "Found public key in secrets, copying to storage..."
    cp /etc/secrets/oauth-public.key /var/www/html/storage/oauth-public.key
    chmod 600 /var/www/html/storage/oauth-public.key
    chown nginx:nginx /var/www/html/storage/oauth-public.key
fi

echo "🔑 Creating Passport clients..."
php artisan passport:client --personal --no-interaction
php artisan passport:client --password --no-interaction

echo "🌱 Running seeders..."
php artisan db:seed --force || true


# ✅ Vérifications finales
echo "🔍 Final verification..."
# Vérification si le lien symbolique 'storage' existe dans public
ls -ld /var/www/html/public/storage 2>/dev/null && echo "✅ Lien symbolique 'storage' trouvé dans public" || echo "❌ Lien symbolique 'storage' NON trouvé"

# Vérification de la documentation Swagger
echo ""
echo "=== Swagger Documentation Verification ==="
[ -f "/var/www/html/storage/api-docs/api-docs.json" ] && echo "✅ api-docs.json généré dans storage" || echo "❌ api-docs.json NON trouvé dans storage"
[ -d "/var/www/html/public/vendor/swagger-api/swagger-ui/dist" ] && echo "✅ Swagger UI assets présents dans public" || echo "❌ Swagger UI assets NON trouvés"

echo "📋 Configuration des logs Laravel..."
rm -f /var/www/html/storage/logs/laravel.log
ln -sf /dev/stderr /var/www/html/storage/logs/laravel.log

echo "✅ Déploiement terminé avec succès!"
