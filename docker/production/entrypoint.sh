#!/bin/sh
# Entrypoint container backend SILKABK.
# Dijalankan setiap container start, sebelum php-fpm.
set -e
cd /var/www/html

if [ -z "$APP_KEY" ]; then
    echo "[entrypoint] APP_KEY belum diisi. Buat dengan: php artisan key:generate --show" >&2
    exit 1
fi

# Volume storage bisa kosong saat pertama kali dipasang
mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions \
         storage/framework/views storage/logs

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "[entrypoint] Menjalankan migration..."
    php artisan migrate --force
fi

echo "[entrypoint] Cache config, route, view..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
