# Stage 1 : Build
FROM php:8.2-cli AS build

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    git unzip curl libzip-dev libonig-dev \
    && docker-php-ext-install zip pdo pdo_mysql

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers de l'application
COPY . .

# Copier build.sh et rendre exécutable
COPY build.sh .
RUN chmod +x build.sh
