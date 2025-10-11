#!/usr/bin/env bash
set -e

echo "============================================"
echo "🚀 Démarrage du déploiement Laravel"
echo "============================================"

# 1. Installation des dépendances
echo "📦 Installation des dépendances Composer..."
composer install --no-dev --prefer-dist --optimize-autoloader --working-dir=/var/www/html

# 2. Génération de la clé d'application
echo "🔑 Génération de la clé d'application..."
php artisan key:generate --show --force

# 3. Clear des caches
echo "🧹 Nettoyage des caches..."
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear

# 4. Migrations et seeders
echo "🗄️  Exécution des migrations..."
php artisan migrate --force

echo "🌱 Exécution des seeders..."
php artisan db:seed --force || true

# 5. Optimisation
echo "⚡ Optimisation de l'application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Permissions
echo "🔐 Configuration des permissions..."
chown -R nginx:nginx /var/www/html/storage
chown -R nginx:nginx /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# 7. Vérifications de débogage
echo "============================================"
echo "🔍 VÉRIFICATIONS DE CONFIGURATION"
echo "============================================"

echo "📂 Structure du projet:"
ls -la /var/www/html/public/

echo ""
echo "🌐 Configuration Nginx active:"
cat /etc/nginx/sites-available/default

echo ""
echo "✅ Test de syntaxe Nginx:"
nginx -t

echo ""
echo "🐘 Vérification PHP-FPM:"
php -v
ps aux | grep php-fpm | head -5 || true

echo ""
echo "📝 Vérification du fichier index.php:"
ls -la /var/www/html/public/index.php

echo ""
echo "🔍 Routes Laravel disponibles:"
php artisan route:list | grep api || true

echo ""
echo "📋 Variables d'environnement Laravel:"
php artisan env

echo ""
echo "============================================"
echo "✅ Déploiement terminé avec succès!"
echo "============================================"