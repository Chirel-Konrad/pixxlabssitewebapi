FROM php:8.2-fpm-alpine

# Installer les dépendances système de base
RUN apk update && apk add --no-cache nginx supervisor postgresql-client

# Définir le répertoire de travail
WORKDIR /var/www

# Copier tous les fichiers de l'application
COPY . .

# Installer Composer et les dépendances PHP
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Copier les configurations des services
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Exposer le port et lancer le script
EXPOSE 80
CMD ["/usr/local/bin/entrypoint.sh"]
