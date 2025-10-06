# --- Stage 1: Dépendances & Build ---
    FROM php:8.2-fpm as vendor

    ENV DEBIAN_FRONTEND=noninteractive
    
    # Installation des dépendances système pour Laravel
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
    
    # Définition du répertoire de travail
    WORKDIR /var/www
    
    # Installation des dépendances Composer (sans scripts pour éviter les erreurs de build)
    COPY database/ database/
    COPY composer.json composer.lock ./
    RUN composer install --no-interaction --no-plugins --prefer-dist --optimize-autoloader --no-scripts
    
    # Copie du reste du code de l'application
    COPY . .
    RUN composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader --no-scripts
    
    
    # --- Stage 2: Production ---
    FROM php:8.2-fpm-alpine
    
    # Installation des paquets nécessaires pour la production (Nginx, Supervisor, client Postgres)
    RUN apk --no-cache add nginx supervisor postgresql-client
    
    # Copie des fichiers de configuration
    COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
    COPY docker/supervisor/supervisord.conf /etc/supervisord.conf
    
    # --- CORRECTION DE L'ORDRE DES COMMANDES ---
    
    # 1. Définir le répertoire de travail
    WORKDIR /var/www
    
    # 2. Copier le code de l'application (c'est ce qui crée les dossiers `storage` et `bootstrap`)
    COPY --from=vendor /var/www /var/www
    
    # 3. Changer les permissions (maintenant que les dossiers existent)
    RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && \
        chmod -R 775 /var/www/storage /var/www/bootstrap/cache
    
    # Exposer le port 80 pour Nginx
    EXPOSE 80
    
    # Copier et rendre le script de démarrage exécutable
    COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
    RUN chmod +x /usr/local/bin/entrypoint.sh
    
    # Commande de démarrage du conteneur
    CMD ["/usr/local/bin/entrypoint.sh"]
    