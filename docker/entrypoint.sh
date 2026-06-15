#!/bin/sh
set -e

if [ -n "$PASSPORT_PRIVATE_KEY" ]; then
    # Chaves estáveis via Fly secrets — escreve nos arquivos a cada boot
    printf '%s' "$PASSPORT_PRIVATE_KEY" > storage/oauth-private.key
    printf '%s' "$PASSPORT_PUBLIC_KEY"  > storage/oauth-public.key
elif [ ! -f storage/oauth-private.key ]; then
    php artisan passport:keys --force
fi

# php-fpm workers rodam como www-data; garante que podem ler as chaves (0600)
chown www-data:www-data storage/oauth-private.key storage/oauth-public.key
chmod 600 storage/oauth-private.key storage/oauth-public.key

php artisan config:cache
php artisan route:cache
php artisan view:cache

if [ "$#" -gt 0 ]; then
    exec "$@"
fi

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
