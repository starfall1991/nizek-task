#!/usr/bin/env bash
set -e

su octane -c "php $*"

env_file="/var/www/html/.env"
app_key_line=$(grep 'APP_KEY=' "$env_file")

if [ -z "$app_key_line" ]; then
  echo "APP_KEY field not found in the .env file."
else
  echo "Found APP_KEY line"
  app_key_value=$(echo "$app_key_line" | cut -d '=' -f2 | tr -d '[:space:]')

  if [ -z "$app_key_value" ]; then
    echo "APP_KEY is not filled."
    su -s /bin/sh -c "php artisan key:generate" octane
  else
    echo "APP_KEY is filled."
  fi
fi

php artisan migrate
php artisan package:discover --ansi
php artisan event:cache
php artisan config:cache
php artisan route:cache
php artisan storage:link

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
