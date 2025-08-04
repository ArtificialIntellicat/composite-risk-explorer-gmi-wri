# Basis-Image mit PHP, Composer, Node.js und MySQL-Client
FROM laravelsail/php83-composer-node

# Arbeitsverzeichnis setzen
WORKDIR /var/www/html

# System-Abhängigkeiten installieren (für mysql & xlsx)
RUN apt-get update && apt-get install -y \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libjpeg-dev \
    libfreetype6-dev \
    mysql-client \
    && docker-php-ext-install pdo pdo_mysql zip

# Projektdateien kopieren
COPY . .

# Composer-Abhängigkeiten installieren
RUN composer install --no-dev --optimize-autoloader

# Frontend bauen
RUN npm install && npm run build

# Laravel vorbereiten
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

# App-Key generieren
RUN php artisan key:generate

# Migrationen und Seeder
RUN php artisan migrate --force && \
    php artisan db:seed --class=CombinedDataSeeder

# Port für Laravel öffnen
EXPOSE 8000

# Startbefehl
CMD php artisan serve --host=0.0.0.0 --port=8000
