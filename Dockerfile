FROM php:8.2-fpm-alpine

RUN apk update && apk --no-cache add \
    nginx \
    supervisor \
    postgresql-client \
    git \
    unzip \
    # Paquets de développement nécessaires pour les extensions
    postgresql-dev \
    libzip-dev \
    libpng-dev \
    jpeg-dev \
    freetype-dev

# Installer les extensions PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo_pgsql zip exif pcntl bcmath

WORKDIR /var/www

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY . .

RUN composer install --no-interaction --no-dev --optimize-autoloader --no-scripts
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80
CMD ["/usr/local/bin/entrypoint.sh"]
