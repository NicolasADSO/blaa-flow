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

# Crear rutas necesarias antes de instalar
RUN mkdir -p /var/www/html/storage/framework/cache/data /var/www/html/storage/framework/sessions /var/www/html/storage/framework/views /var/www/html/bootstrap/cache /var/www/html/database \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Instalar dependencias de PHP (sin correr scripts de Artisan en build)
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Generar assets de Vite
RUN apt-get install -y nodejs npm \
    && npm install \
    && npm run build

# Configurar permisos de Laravel (repetimos para asegurar)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Crear archivo SQLite si no existe
RUN touch /var/www/html/database/database.sqlite \
    && chown www-data:www-data /var/www/html/database/database.sqlite \
    && chmod 664 /var/www/html/database/database.sqlite

# Exponer puerto
EXPOSE 80

# Comando por defecto
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=80"]