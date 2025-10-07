#!/bin/sh
set -e

# Se placer dans le répertoire de l'application
cd /var/www

# Remplacer la variable PORT dans la configuration Nginx
# C'est une bonne pratique, même si on écoute sur le port 80, on le confirme.
# Assurez-vous que votre default.conf contient bien 'listen ${PORT};'
envsubst '${PORT}' < /etc/nginx/conf.d/default.conf > /etc/nginx/conf.d/default.conf.tmp
mv /etc/nginx/conf.d/default.conf.tmp /etc/nginx/conf.d/default.conf

# --- CORRECTION DE L'ORDRE ---
# ÉTAPE 1 : CONFIGURER LES PERMISSIONS
echo "==> Configuration des permissions..."
# On donne la propriété de tous les fichiers à www-data, puis on s'assure
# que les dossiers de stockage sont accessibles en écriture.
chown -R www-data:www-data /var/www
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# ÉTAPE 2 : LANCER LES COMMANDES ARTISAN (maintenant que les permissions sont bonnes)
echo "==> Vérification de la base de données et migrations..."
# On exécute les commandes en tant qu'utilisateur www-data pour être cohérent
su -s /bin/sh www-data -c "php artisan migrate --force"

echo "==> Mise en cache des configurations..."
su -s /bin/sh www-data -c "php artisan config:cache"
su -s /bin/sh www-data -c "php artisan route:cache"
su -s /bin/sh www-data -c "php artisan view:cache"

# ÉTAPE 3 : LANCER LES SERVICES
echo "==> Lancement des services..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
