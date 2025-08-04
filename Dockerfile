# Basis-Image mit PHP 8.3, Composer, nginx, Node.js und Alpine-Linux
FROM webdevops/php-nginx:8.3-alpine

# Arbeitsverzeichnis setzen
WORKDIR /app

ENV WEB_DOCUMENT_ROOT=/app/public

# Projektdateien kopieren.
COPY . .

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# .env-Datei zur Laufzeit erzeugen aus Umgebungsvariablen
RUN echo "APP_KEY=${APP_KEY}" > .env && \
    echo "APP_ENV=${APP_ENV}" >> .env && \
    echo "APP_DEBUG=${APP_DEBUG}" >> .env && \
    echo "DB_CONNECTION=${DB_CONNECTION}" >> .env && \
    echo "DB_HOST=${DB_HOST}" >> .env && \
    echo "DB_PORT=${DB_PORT}" >> .env && \
    echo "DB_DATABASE=${DB_DATABASE}" >> .env && \
    echo "DB_USERNAME=${DB_USERNAME}" >> .env && \
    echo "DB_PASSWORD=${DB_PASSWORD}" >> .env

# Wichtige Laravel-Ordner anlegen und Rechte setzen
RUN mkdir -p \
    storage/app \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
 && chown -R application:application storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache


# Abhängigkeiten und Assets installieren
RUN apk update && \
    apk add --no-cache npm git zip unzip oniguruma-dev libzip-dev libpng-dev libjpeg-turbo-dev freetype-dev mysql-client && \
    composer install --no-dev --optimize-autoloader && \
    npm install && \
    npm run build

# Port freigeben (Standard HTTP)
EXPOSE 80

# Container-Start: PHP + nginx über Supervisord
CMD ["/entrypoint.sh", "supervisord"]
