#!/bin/sh
set -e

# Gera as chaves do Passport se não existirem como env vars e nem em storage/
if [ -z "$PASSPORT_PRIVATE_KEY" ] && [ ! -f storage/oauth-private.key ]; then
    php artisan passport:keys --force
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

if [ "$#" -gt 0 ]; then
    exec "$@"
fi

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
