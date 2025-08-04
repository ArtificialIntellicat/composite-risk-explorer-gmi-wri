# Basis-Image mit PHP 8.3, Composer, nginx, Node.js und Alpine-Linux
FROM webdevops/php-nginx:8.3-alpine

# Arbeitsverzeichnis setzen
WORKDIR /app

ENV WEB_DOCUMENT_ROOT=/app/public

# Projektdateien kopieren
COPY . .

# Entrypoint-Skript für Laufzeit-Setup kopieren
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Laravel-Verzeichnisse anlegen und Rechte setzen
RUN mkdir -p \
    storage/app \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chown -R application:application storage bootstrap/cache \
    && find storage bootstrap/cache -type d -exec chmod 775 {} \; \
    && find storage bootstrap/cache -type f -exec chmod 664 {} \;


# Abhängigkeiten und Assets installieren
RUN apk update && \
    apk add --no-cache npm git zip unzip oniguruma-dev libzip-dev libpng-dev libjpeg-turbo-dev freetype-dev mysql-client && \
    composer install --no-dev --optimize-autoloader && \
    npm install && \
    npm run build

# HTTP-Port freigeben
EXPOSE 80

# Start-Befehl: Laufzeit-Setup + nginx + PHP
CMD ["/entrypoint.sh", "supervisord"]
