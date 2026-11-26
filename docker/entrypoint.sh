#!/bin/sh
set -e

# Render memberi port lewat $PORT; Apache secara default mendengarkan 80.
PORT="${PORT:-80}"
sed -i "s/^Listen 80$/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

php artisan config:cache
php artisan route:cache
php artisan view:cache

# Idempoten: seeder berhenti sendiri kalau data demo sudah ada.
php artisan migrate --force --seed

exec apache2-foreground
