#!/bin/bash
set -e

# Migrations et cache seulement
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache