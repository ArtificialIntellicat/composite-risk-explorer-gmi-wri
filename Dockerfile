FROM webdevops/php-nginx:8.3-alpine

WORKDIR /app

# System-Abhängigkeiten installieren
RUN apt-get update && apt-get install -y \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libjpeg-dev \
    libfreetype6-dev \
    curl \
    git \
    npm \
    nodejs \
    mysql-client \
    && docker-php-ext-install pdo pdo_mysql zip

# Composer installieren
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Projektdateien kopieren
COPY . .

# Apache Rewrite aktivieren (für Laravel Routing)
RUN a2enmod rewrite

# Laravel vorbereiten
RUN composer install --no-dev --optimize-autoloader \
 && npm install \
 && npm run build \
 && php artisan key:generate \
 && php artisan config:cache \
 && php artisan route:cache \
 && php artisan view:cache \
 && php artisan migrate --force \
 && php artisan db:seed --class=CombinedDataSeeder

# Port öffnen
EXPOSE 80

# Apache starten
CMD ["apache2-foreground"]
