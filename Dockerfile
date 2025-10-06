FROM php:8.2-cli

# Installer les extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
    libpq-dev \
    zip \
    unzip \
    git \
    curl

# Installer l'extension PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql pgsql

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers du projet
COPY . .

# Installer les dépendances
RUN composer install --no-dev --optimize-autoloader

# Rendre le script build.sh exécutable
RUN chmod +x build.sh

# Exposer le port
EXPOSE $PORT

# Commande de démarrage
CMD ["sh", "-c", "./build.sh && php artisan serve --host=0.0.0.0 --port=$PORT"]