#!/bin/sh
# Runs every time the LifeLine container starts online (see Dockerfile and docs/deployment.md).
set -e

# The host tells the app which port to use (Railway and Render set PORT); Apache defaults to 80.
PORT="${PORT:-80}"
sed -i "s/Listen 80$/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Apache may load only one MPM, and PHP needs prefork. Some hosts (Railway) end up with two enabled,
# which stops Apache with "AH00534: More than one MPM loaded".
rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.*
[ -e /etc/apache2/mods-enabled/mpm_prefork.load ] || a2enmod mpm_prefork

# Faster pages: read settings, routes and views once.
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create or update the tables, then add the demo data only if the database is still empty.
php artisan migrate --force
php artisan lifeline:seed-if-empty

exec apache2-foreground
