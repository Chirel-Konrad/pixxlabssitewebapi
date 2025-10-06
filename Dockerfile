# --- Stage 1: Dépendances & Build ---
# (Aucun changement dans cette partie)
FROM php:8.2-fpm as vendor
# ... (tout le reste du stage 1 reste identique)


# --- Stage 2: Production ---
FROM php:8.2-fpm-alpine

# MODIFICATION ICI: Ajout de postgresql-client
RUN apk --no-cache add nginx supervisor postgresql-client

# Copier la configuration Nginx
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Copier la configuration du superviseur
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf

# Copier le code de l'application et les dépendances depuis le stage précédent
COPY --from=vendor /var/www /var/www

# Définir le répertoire de travail
WORKDIR /var/www

# Permissions pour le stockage et le cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Exposer le port 80
EXPOSE 80

# Utiliser le script d'entrée pour les migrations et les optimisations
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Commande de démarrage
CMD ["/usr/local/bin/entrypoint.sh"]
