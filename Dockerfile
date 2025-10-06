FROM php:8.2-fpm-alpine

# Installer les dépendances
RUN apk --no-cache add nginx supervisor postgresql-client

# Copier les configurations
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

# Copier le code de l'application
WORKDIR /var/www
COPY . .

# Installer les dépendances Composer
# On le fait ici, une fois que tout le code est présent
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-interaction --no-dev --optimize-autoloader --no-scripts

# Définir les permissions
RUN chown -R www-data:www-data storage bootstrap/cache

# Exposer le port et lancer le script de démarrage
EXPOSE 80
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
CMD ["/usr/local/bin/entrypoint.sh"]
