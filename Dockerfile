# --- Stage 1: Dépendances & Build ---
    FROM php:8.2-fpm as vendor

    # Variables d'environnement
    ENV DEBIAN_FRONTEND=noninteractive
    
    # Installation des dépendances système
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
    
    # Installation de Composer
    COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
    
    # Définir le répertoire de travail
    WORKDIR /var/www
    
    # Copier uniquement les fichiers nécessaires pour installer les dépendances
    COPY database/ database/
    COPY composer.json composer.lock ./
    
    # Installer les dépendances Composer (sans les scripts pour éviter les erreurs)
    RUN composer install --no-interaction --no-plugins --no-scripts --prefer-dist --optimize-autoloader
    
    # Copier le reste du code de l'application
    COPY . .
    
    # Exécuter les scripts Composer après avoir copié tout le code
    RUN composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader
    
    
    # --- Stage 2: Production ---
    FROM nginx:stable-alpine
    
    # Copier la configuration Nginx
    # (Nous allons créer ce fichier à l'étape suivante)
    COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
    
    # Copier le code de l'application et les dépendances depuis le stage précédent
    COPY --from=vendor /var/www /var/www
    
    # Définir le répertoire de travail
    WORKDIR /var/www
    
    # Exposer le port 80 (port standard pour Nginx)
    EXPOSE 80
    
    # Commande de démarrage
    # Render exécutera ce script pour lancer l'application
    CMD ["/var/www/docker/entrypoint.sh"]
    