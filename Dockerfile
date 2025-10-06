FROM php:8.2-cli

# Installer les extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
    libpq-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo pdo_pgsql pgsql

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers du projet
COPY . .

# Rendre le script build.sh exécutable
RUN chmod +x build.sh

# Installer les dépendances et optimiser l'autoload
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Exposer un port fixe pour le build (Render remplacera par $PORT à l'exécution)
EXPOSE 8000

# Commande de démarrage
CMD ["sh", "-c", "./build.sh && php artisan serve --host=0.0.0.0 --port=$PORT"]
