FROM richarvey/nginx-php-fpm:3.1.6

COPY . .

# Image config
ENV SKIP_COMPOSER=1
ENV WEBROOT=/var/www/html/public
ENV PHP_ERRORS_STDERR=1
ENV RUN_SCRIPTS=1
ENV REAL_IP_HEADER=1

# Laravel config
ENV APP_ENV=production
ENV APP_DEBUG=true
ENV LOG_CHANNEL=stderr
ENV LOG_LEVEL=debug

# Allow composer to run as root
ENV COMPOSER_ALLOW_SUPERUSER=1

# ✅ NOUVEAUTÉ : Configurer PHP pour afficher TOUTES les erreurs dans stderr
RUN echo "log_errors = On" >> /usr/local/etc/php/conf.d/docker-php-errors.ini && \
    echo "error_log = /dev/stderr" >> /usr/local/etc/php/conf.d/docker-php-errors.ini && \
    echo "display_errors = On" >> /usr/local/etc/php/conf.d/docker-php-errors.ini && \
    echo "display_startup_errors = On" >> /usr/local/etc/php/conf.d/docker-php-errors.ini

CMD ["/start.sh"]