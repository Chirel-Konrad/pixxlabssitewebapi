FROM wyveo/nginx-php-fpm:php82

WORKDIR /var/www/html

# Copier les fichiers de l'application
COPY . .

# ✅ CRITIQUE : Créer la config Nginx directement dans le Dockerfile
RUN cat > /etc/nginx/sites-available/default <<'EOF'
server {
    listen 80 default_server;
    listen [::]:80 default_server;
    
    server_name _;
    root /var/www/html/public;
    
    index index.php;

    error_log /dev/stdout info;
    access_log /dev/stdout;

    charset utf-8;

    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    location /.git {
        deny all;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        fastcgi_buffering off;
    }

    location ~* \.(jpg|jpeg|gif|png|css|js|ico|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        access_log off;
        add_header Cache-Control "public, immutable";
    }
}
EOF

# Supprimer les configs par défaut qui peuvent interférer
RUN rm -f /etc/nginx/sites-enabled/default && \
    ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Permissions pour le script de déploiement
RUN chmod +x scripts/00-laravel-deploy.sh

# Permissions Laravel (storage et bootstrap/cache)
RUN chown -R nginx:nginx /var/www/html && \
    chmod -R 775 /var/www/html/storage && \
    chmod -R 775 /var/www/html/bootstrap/cache

# Variables d'environnement
ENV WEBROOT=/var/www/html/public
ENV RUN_SCRIPTS=1
ENV PHP_ERRORS_STDERR=1
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV APP_ENV=production
ENV APP_DEBUG=true
ENV LOG_CHANNEL=stderr

# ✅ Vérifier que la config Nginx est bien en place
RUN echo "=== Vérification config Nginx ===" && \
    cat /etc/nginx/sites-available/default && \
    nginx -t

CMD ["/start.sh"]