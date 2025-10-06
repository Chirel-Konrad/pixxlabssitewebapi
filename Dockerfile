# On part d'une image PHP 8.2 FPM Alpine, légère et optimisée
FROM php:8.2-fpm-alpine

# On définit le répertoire de travail
WORKDIR /var/www

# On installe les dépendances système nécessaires
# build-base est nécessaire pour compiler certains paquets Composer
RUN apk update && apk add --no-cache \
    nginx \
    supervisor \
    postgresql-client \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_pgsql zip exif pcntl bcmath

# On installe Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# On copie UNIQUEMENT composer.json pour installer les dépendances
COPY composer.json .

# On lance composer install. Sans composer.lock, il va calculer les dépendances
# parfaites pour l'environnement Alpine Linux.
RUN composer install --no-interaction --no-dev --optimize-autoloader --no-scripts

# Maintenant, on copie le reste du code de l'application
COPY . .

# On définit les permissions pour Laravel
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# On copie les configurations pour Nginx, PHP et Supervisor
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf

# On copie le script de démarrage et on le rend exécutable
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# On expose le port 80 et on définit la commande de démarrage
EXPOSE 80
CMD ["/usr/local/bin/entrypoint.sh"]
