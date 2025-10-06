# On part d'une image PHP 8.2 FPM Alpine, légère et optimisée
FROM php:8.2-fpm-alpine

# On définit le répertoire de travail D'ABORD
WORKDIR /var/www

# 1. INSTALLATION DES DÉPENDANCES SYSTÈME
# On installe tout ce dont PHP et Composer auront besoin
RUN apk update && apk --no-cache add \
    nginx \
    supervisor \
    postgresql-client \
    git \
    unzip \
    # Paquets de développement (-dev) nécessaires pour compiler les extensions PHP
    postgresql-dev \
    libzip-dev \
    libpng-dev \
    jpeg-dev \
    freetype-dev

# 2. INSTALLATION DES EXTENSIONS PHP
# Maintenant que les -dev sont là, on peut compiler les extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo_pgsql zip exif pcntl bcmath

# 3. INSTALLATION DES DÉPENDANCES COMPOSER
# On installe Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
# On copie UNIQUEMENT composer.json pour installer les paquets
# Le .dockerignore empêchera composer.lock d'être copié, forçant une installation propre
COPY composer.json ./
RUN composer install --no-interaction --no-dev --optimize-autoloader --no-scripts

# 4. COPIE DU RESTE DE L'APPLICATION
COPY . .

# 5. DÉFINITION DES PERMISSIONS
# Maintenant que tous les fichiers sont là, on peut définir les permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# 6. COPIE DES CONFIGURATIONS FINALES
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# 7. EXÉCUTION
EXPOSE 80
CMD ["/usr/local/bin/entrypoint.sh"]
