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

echo "📝 Caching config..."
php artisan config:cache

echo "🛣️  Caching routes..."
php artisan route:cache

echo "🗄️  Running migrations..."
php artisan migrate:fresh --force

echo "🌱 Running seeders..."
php artisan db:seed --force || true

# ✅ AJOUT : Générer la documentation Swagger
echo "📖 Generating Swagger documentation..."
php artisan l5-swagger:generate || echo "⚠️  Swagger generation failed"

echo "📋 Configuration des logs Laravel..."
# Créer un lien symbolique de laravel.log vers stderr
rm -f /var/www/html/storage/logs/laravel.log
ln -sf /dev/stderr /var/www/html/storage/logs/laravel.log

echo "✅ Déploiement terminé avec succès!"
echo "📂 Contenu de /var/www/html:"
ls -la /var/www/html/
