# On part d'une image PHP 8.2 FPM Alpine, légère et optimisée
FROM php:8.2-fpm-alpine

# Installer les dépendances système de base, y compris les -dev pour la compilation
RUN apk update && apk --no-cache add \
    nginx \
    supervisor \
    postgresql-client \
    libzip-dev \
    libpng-dev \
    jpeg-dev \
    freetype-dev \
    postgresql-dev

# Installer les extensions PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_pgsql zip exif pcntl bcmath

# Définir le répertoire de travail
WORKDIR /var/www

# Copier tous les fichiers de l'application
COPY . .

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# --- LA CORRECTION EST ICI ---
# Installer les dépendances PHP SANS lancer de scripts. C'est crucial.
RUN composer install --no-interaction --no-dev --optimize-autoloader --no-scripts

# Copier les configurations des services
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Exposer le port et lancer le script
EXPOSE 80
CMD ["/usr/local/bin/entrypoint.sh"]
