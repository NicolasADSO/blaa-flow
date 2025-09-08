# Imagen base de PHP 8.2 con Apache
FROM php:8.2-cli

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    unzip git curl libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libzip-dev zip \
    libicu-dev g++ \
    && docker-php-ext-install pdo pdo_mysql gd mbstring zip exif pcntl intl bcmath


# Copiar archivos de la app
COPY . /var/www/html
WORKDIR /var/www/html

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# Crear carpetas necesarias con permisos correctos ANTES de Composer
RUN mkdir -p bootstrap/cache \
    && mkdir -p storage/framework/{cache,sessions,views} \
    && mkdir -p database \
    && chmod -R 775 bootstrap/cache storage database \
    && chown -R www-data:www-data bootstrap/cache storage database

# Instalar dependencias PHP (sin ejecutar scripts artisan aún)
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Exponer puerto
EXPOSE 80

# ⚡ El comando final lo controlamos desde render.yaml (startCommand)
