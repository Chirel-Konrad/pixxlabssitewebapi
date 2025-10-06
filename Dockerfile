# Base image PHP CLI
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
    && docker-php-ext-install pdo_pgsql pgsql zip mbstring exif pcntl bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Créer un utilisateur non-root
RUN useradd -ms /bin/bash appuser

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier le code
COPY --chown=appuser:appuser . .

# Basculer vers l'utilisateur non-root
USER appuser

# Installer les dépendances Composer
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Revenir en root pour les permissions
USER root

# Permissions pour bootstrap/cache et storage
RUN chown -R appuser:appuser bootstrap/cache storage \
    && chmod -R 775 bootstrap/cache storage

# Créer un script d'entrée
RUN echo '#!/bin/bash\n\
set -e\n\
php artisan config:clear\n\
php artisan migrate --force\n\
php artisan config:cache\n\
php artisan route:cache\n\
php artisan serve --host=0.0.0.0 --port=$PORT\n' > /entrypoint.sh && \
    chmod +x /entrypoint.sh && \
    chown appuser:appuser /entrypoint.sh

# Basculer vers l'utilisateur
USER appuser

# Exposer le port
EXPOSE $PORT

# Utiliser le script d'entrée
ENTRYPOINT ["/entrypoint.sh"]