#!/usr/bin/env bash
# exit on error
set -o errexit

composer install --no-dev --working-dir=/opt/render/project/src
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link