# Basis-Image mit PHP 8.3, Composer, nginx, Node.js und Alpine-Linux
FROM webdevops/php-nginx:8.3-alpine

# Arbeitsverzeichnis setzen
WORKDIR /app

ENV WEB_DOCUMENT_ROOT=/app/public

# Projektdateien kopieren
COPY . .

# Abhängigkeiten und Assets installieren
RUN apk update && \
    apk add --no-cache npm git zip unzip oniguruma-dev libzip-dev libpng-dev libjpeg-turbo-dev freetype-dev mysql-client && \
    composer install --no-dev --optimize-autoloader && \
    npm install && \
    npm run build

# Port freigeben (Standard HTTP)
EXPOSE 80

# Container-Start: PHP + nginx über Supervisord
CMD ["supervisord"]
