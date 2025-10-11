FROM wyveo/nginx-php-fpm:php82

WORKDIR /var/www/html
COPY . .

RUN chmod +x scripts/00-laravel-deploy.sh

ENV WEBROOT /var/www/html/public
ENV RUN_SCRIPTS 1
ENV PHP_ERRORS_STDERR 1
ENV COMPOSER_ALLOW_SUPERUSER 1
ENV APP_ENV=production
ENV APP_DEBUG=true
ENV LOG_CHANNEL=stderr

CMD ["/start.sh"]
