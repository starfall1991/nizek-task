#!/usr/bin/env bash
set -e

su octane -c "php $*"
php artisan octane:install
php artisan optimize:clear

php artisan migrate --force
php artisan package:discover --ansi
php artisan event:cache
php artisan config:cache
php artisan route:cache
php artisan storage:link

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
