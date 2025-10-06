# --- Stage 1: Dépendances & Build ---
# (Aucun changement ici)
FROM php:8.2-fpm as vendor
# ...

# --- Stage 2: Production ---
FROM php:8.2-fpm-alpine

# Installation des paquets
RUN apk --no-cache add nginx supervisor postgresql-client

# Copie des configurations
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf
# MODIFICATION ICI : Copier la configuration PHP-FPM
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

# Définir le répertoire de travail
WORKDIR /var/www

# Copier le code de l'application
COPY --from=vendor /var/www /var/www

# Changer les permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Exposer le port 80
EXPOSE 80

# Copier le script de démarrage
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Commande de démarrage
CMD ["/usr/local/bin/entrypoint.sh"]
