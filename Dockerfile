# Base image PHP CLI (pas fpm)
FROM php:8.2-cli

# Installer les dépendances système nécessaires
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    libonig-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libxml2-dev \
    && docker-php-ext-install pdo_pgsql pgsql zip mbstring exif pcntl bcmath gd

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier le code
COPY . .

# Installer les dépendances Composer
RUN composer install --no-dev --optimize-autoloader

# Permissions pour bootstrap/cache et storage
RUN chown -R www-data:www-data bootstrap/cache storage \
    && chmod -R 775 bootstrap/cache storage

# Rendre build.sh exécutable
RUN chmod +x build.sh

# Exposer le port (Render injecte $PORT)
EXPOSE $PORT

# Commande de démarrage
CMD ["sh", "-c", "./build.sh && php artisan serve --host=0.0.0.0 --port=$PORT"]