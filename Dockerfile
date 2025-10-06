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
    
    # Installer les dépendances Composer (SANS les scripts)
    # MODIFICATION 1: Ajout de --no-scripts
    RUN composer install --no-interaction --no-plugins --prefer-dist --optimize-autoloader --no-scripts
    
    # Copier le reste du code de l'application
    COPY . .
    
    # Exécuter l'installation une seconde fois pour s'assurer que tout est correct (toujours SANS les scripts)
    # MODIFICATION 2: Ajout de --no-scripts
    RUN composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader --no-scripts
    
    
    # --- Stage 2: Production ---
    FROM php:8.2-fpm-alpine
    
    # Installation des dépendances Nginx et de supervision
    RUN apk --no-cache add nginx supervisor
    
    # Copier la configuration Nginx
    COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
    
    # Copier la configuration du superviseur
    COPY docker/supervisor/supervisord.conf /etc/supervisord.conf
    
    # Copier le code de l'application et les dépendances depuis le stage précédent
    COPY --from=vendor /var/www /var/www
    
    # Définir le répertoire de travail
    WORKDIR /var/www
    
    # Permissions pour le stockage et le cache
    RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && \
        chmod -R 775 /var/www/storage /var/www/bootstrap/cache
    
    # Exposer le port 80
    EXPOSE 80
    
    # Utiliser le script d'entrée pour les migrations et les optimisations
    COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
    RUN chmod +x /usr/local/bin/entrypoint.sh
    
    # Commande de démarrage
    CMD ["/usr/local/bin/entrypoint.sh"]
    