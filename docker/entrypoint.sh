#!/bin/sh
set -e

cd /var/www/html

export PORT="${PORT:-10000}"
sed -i "s/listen 10000 default_server;/listen ${PORT} default_server;/" /etc/nginx/http.d/default.conf

php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link || true

php artisan migrate --force --no-interaction
php artisan db:seed --force --no-interaction

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
