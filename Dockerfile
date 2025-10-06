# --- STAGE 1: BUILD ---
# On utilise une image PHP complète pour installer les dépendances
FROM php:8.2-fpm as vendor

# On définit les variables pour une installation non-interactive
ENV DEBIAN_FRONTEND=noninteractive

# On installe les dépendances système nécessaires pour Laravel et Postgres
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

# On installe Composer (le gestionnaire de paquets PHP)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# On définit le répertoire de travail
WORKDIR /var/www

# On copie uniquement les fichiers de dépendances et on installe les paquets
# L'option --no-scripts est CRUCIALE pour éviter que Laravel ne se lance pendant le build
COPY database/ database/
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-plugins --prefer-dist --optimize-autoloader --no-scripts

# On copie le reste du code de l'application
COPY . .

# On relance composer install pour générer l'autoloader final, toujours sans scripts
RUN composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader --no-scripts


# --- STAGE 2: PRODUCTION ---
# On part d'une image "alpine" beaucoup plus légère pour la production
FROM php:8.2-fpm-alpine

# On installe uniquement les paquets nécessaires pour faire tourner le serveur
RUN apk --no-cache add nginx supervisor postgresql-client

# On copie les fichiers de configuration que nous avons créés
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

# On définit le répertoire de travail
WORKDIR /var/www

# --- CORRECTION DE L'ORDRE ---
# 1. On copie d'abord le code de l'application depuis le stage de build.
#    C'est cette ligne qui crée les dossiers /var/www/storage et /var/www/bootstrap.
COPY --from=vendor /var/www /var/www

# 2. Maintenant que les dossiers existent, on peut changer leurs permissions.
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# On expose le port 80 pour que le monde extérieur puisse parler à Nginx
EXPOSE 80

# On copie le script de démarrage et on le rend exécutable
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# On définit la commande qui sera lancée au démarrage du conteneur
CMD ["/usr/local/bin/entrypoint.sh"]
