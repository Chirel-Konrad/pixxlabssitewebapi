# --- Stage 1: Dépendances & Build ---
# (Aucun changement dans cette partie, elle est correcte)
FROM php:8.2-fpm as vendor

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libxml2-dev \
    && docker-php-ext-install pdo_pgsql pgsql zip exif pcntl bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY database/ database/
COPY composer.json composer.lock ./

RUN composer install --no-interaction --no-plugins --prefer-dist --optimize-autoloader --no-scripts

COPY . .

RUN composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader --no-scripts


# --- Stage 2: Production ---
FROM php:8.2-fpm-alpine

# Installation des dépendances Nginx, supervisor et postgresql-client
RUN apk --no-cache add nginx supervisor postgresql-client

# Copier les configurations
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf

# Définir le répertoire de travail
WORKDIR /var/www

# --- CORRECTION ICI ---
# 1. D'abord, on copie le code de l'application. Les dossiers existeront après cette ligne.
COPY --from=vendor /var/www /var/www

# 2. Ensuite, on change les permissions sur les dossiers qui viennent d'être copiés.
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Exposer le port 80
EXPOSE 80

# Copier et rendre le script d'entrée exécutable
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Commande de démarrage
CMD ["/usr/local/bin/entrypoint.sh"]
