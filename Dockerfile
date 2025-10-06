# Étape 1: Utiliser une image PHP avec les dépendances de build
FROM php:8.2-fpm-alpine as builder

# Installer les dépendances système et PHP
RUN apk update && apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    libpng-dev \
    jpeg-dev \
    freetype-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_pgsql zip exif pcntl bcmath

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Préparer le répertoire de l'application
WORKDIR /var/www

# Copier les fichiers de dépendances et installer
COPY database/ database/
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Copier le reste de l'application
COPY . .

# --- Étape 2: Créer l'image de production finale ---
FROM php:8.2-fpm-alpine

# Installer uniquement les dépendances nécessaires à l'exécution
RUN apk update && apk --no-cache add nginx supervisor postgresql-client

# Définir le répertoire de travail
WORKDIR /var/www

# Copier les fichiers de l'application et les dépendances depuis l'étape de build
COPY --from=builder /var/www .
COPY --from=builder /usr/local/lib/php/extensions/no-debug-non-zts-20220829 /usr/local/lib/php/extensions/no-debug-non-zts-20220829
COPY --from=builder /usr/local/etc/php/conf.d /usr/local/etc/php/conf.d

# Copier les configurations
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Exposer le port et définir la commande de démarrage
EXPOSE 80
CMD ["/usr/local/bin/entrypoint.sh"]
