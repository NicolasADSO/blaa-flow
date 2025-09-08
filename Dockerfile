# Imagen base de PHP 8.2 con Apache
FROM php:8.2-apache

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    unzip git curl libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libzip-dev zip \
    libicu-dev g++ \
    && docker-php-ext-install pdo pdo_mysql gd mbstring zip exif pcntl intl bcmath

# Configurar Apache
RUN a2enmod rewrite

# Copiar archivos de la app
COPY . /var/www/html
WORKDIR /var/www/html

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Permitir que Composer se ejecute como root
ENV COMPOSER_ALLOW_SUPERUSER=1

# 🔹 Crear carpetas necesarias ANTES de composer install
RUN mkdir -p bootstrap/cache \
    && mkdir -p storage/framework/{cache,sessions,views} \
    && mkdir -p database \
    && chmod -R 775 bootstrap/cache storage database \
    && chown -R www-data:www-data bootstrap/cache storage database

# Instalar dependencias de PHP SIN correr scripts de Artisan todavía
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Exponer puerto
EXPOSE 80

# 🔹 Ejecutar Artisan y migraciones SOLO cuando arranca el contenedor
CMD php artisan package:discover --ansi \
    && php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear \
    && php artisan migrate --seed --force \
    && php artisan serve --host=0.0.0.0 --port=80
