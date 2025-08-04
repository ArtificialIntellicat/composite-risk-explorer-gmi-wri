#!/bin/sh

echo "APP_KEY=${APP_KEY}" > .env
echo "APP_ENV=${APP_ENV}" >> .env
echo "APP_DEBUG=${APP_DEBUG}" >> .env
echo "DB_CONNECTION=${DB_CONNECTION}" >> .env
echo "DB_HOST=${DB_HOST}" >> .env
echo "DB_PORT=${DB_PORT}" >> .env
echo "DB_DATABASE=${DB_DATABASE}" >> .env
echo "DB_USERNAME=${DB_USERNAME}" >> .env
echo "DB_PASSWORD=${DB_PASSWORD}" >> .env

# Optional: Laravel vorbereiten
php artisan config:clear
php artisan config:cache
php artisan migrate --force || true

# Starte den Supervisord (PHP + nginx)
exec "$@"

echo "==== .env content ===="
cat .env