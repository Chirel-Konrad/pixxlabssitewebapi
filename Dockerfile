# On part d'une image PHP 8.2 FPM Alpine, légère et optimisée
FROM php:8.2-fpm-alpine

# On définit le répertoire de travail
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

# 3. COPIE DE TOUTE L'APPLICATION
# On copie TOUS les fichiers de l'application d'un seul coup.
COPY . .

# 4. INSTALLATION DES DÉPENDANCES COMPOSER
# Maintenant que TOUS les fichiers sont là (y compris artisan), on peut lancer composer.
# L'option --no-scripts est la sécurité ultime pour empêcher toute exécution non désirée.
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-interaction --no-dev --optimize-autoloader --no-scripts

# 5. DÉFINITION DES PERMISSIONS
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
