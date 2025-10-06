# --- STAGE 1: BUILD ---
    FROM php:8.2-fpm as vendor
    ENV DEBIAN_FRONTEND=noninteractive
    RUN apt-get update && apt-get install -y \
        libpq-dev libzip-dev zip unzip git curl libpng-dev libjpeg-dev libfreetype6-dev libxml2-dev \
        && docker-php-ext-install pdo_pgsql pgsql zip exif pcntl bcmath gd \
        && apt-get clean && rm -rf /var/lib/apt/lists/*
    COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
    WORKDIR /var/www
    COPY database/ database/
    COPY composer.json composer.lock ./
    RUN composer install --no-interaction --no-plugins --prefer-dist --optimize-autoloader --no-scripts
    COPY . .
    RUN composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader --no-scripts
    
    # --- STAGE 2: PRODUCTION ---
    FROM php:8.2-fpm-alpine
    RUN apk --no-cache add nginx supervisor postgresql-client
    COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
    COPY docker/supervisor/supervisord.conf /etc/supervisord.conf
    COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf
    WORKDIR /var/www
    COPY --from=vendor /var/www /var/www
    
    # --- MODIFICATION ICI ---
    # La commande de permissions a été déplacée dans entrypoint.sh pour plus de fiabilité
    # RUN chown -R www-data:www-data /var/www/storage ... (LIGNE SUPPRIMÉE)
    
    EXPOSE 80
    COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
    RUN chmod +x /usr/local/bin/entrypoint.sh
    CMD ["/usr/local/bin/entrypoint.sh"]
    