FROM wyveo/nginx-php-fpm:php82

WORKDIR /var/www/html

# Copier les fichiers de l'application
COPY . .

# ✅ CRITIQUE : Copier et activer la config Nginx AVANT le démarrage
COPY nginx-site.conf /etc/nginx/sites-available/default
COPY nginx-site.conf /etc/nginx/conf.d/default.conf

# Supprimer les configs par défaut qui peuvent interférer
RUN rm -f /etc/nginx/sites-enabled/default && \
    ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Permissions pour le script de déploiement
RUN chmod +x scripts/00-laravel-deploy.sh

# Permissions Laravel (storage et bootstrap/cache)
RUN chown -R nginx:nginx /var/www/html && \
    chmod -R 775 /var/www/html/storage && \
    chmod -R 775 /var/www/html/bootstrap/cache

# Variables d'environnement
ENV WEBROOT=/var/www/html/public
ENV RUN_SCRIPTS=1
ENV PHP_ERRORS_STDERR=1
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV APP_ENV=production
ENV APP_DEBUG=true
ENV LOG_CHANNEL=stderr

# ✅ Vérifier que la config Nginx est bien en place
RUN echo "=== Vérification config Nginx ===" && \
    cat /etc/nginx/sites-available/default && \
    nginx -t

CMD ["/start.sh"]