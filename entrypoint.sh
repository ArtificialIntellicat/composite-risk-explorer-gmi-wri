#!/bin/sh

# Set proper permissions at runtime
chown -R application:application storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Optional: .env aus Umgebungsvariablen erzeugen, wenn nötig
cat <<EOF > .env
APP_KEY=${APP_KEY}
APP_ENV=${APP_ENV}
APP_DEBUG=${APP_DEBUG}
APP_URL=${APP_URL}

DB_CONNECTION=${DB_CONNECTION}
DB_HOST=${DB_HOST}
DB_PORT=${DB_PORT}
DB_DATABASE=${DB_DATABASE}
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}

CACHE_DRIVER=file
SESSION_DRIVER=file
EOF

# Laravel Setup (optional aber sinnvoll)
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Start supervisord (nginx + PHP)
exec "$@"

echo "==== .env content ===="
cat .env