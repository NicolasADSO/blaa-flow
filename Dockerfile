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

# Instalar dependencias de PHP
RUN composer install --no-dev --optimize-autoloader

# Generar assets de Vite
RUN apt-get install -y nodejs npm \
    && npm install \
    && npm run build

# Configurar permisos de Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Exponer puerto
EXPOSE 80

# Comando por defecto (Render.yaml manejará migraciones)
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=80"]
