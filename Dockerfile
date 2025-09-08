# Imagen base de PHP 8.2 con Apache
FROM php:8.2-apache

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    unzip git curl libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libzip-dev zip \
    libicu-dev g++ \
    && docker-php-ext-install pdo pdo_mysql gd mbstring zip exif pcntl intl bcmath

# Configurar Apache
RUN a2enmod rewrite

# Configurar DocumentRoot de Laravel (public/)
WORKDIR /var/www/html
COPY . /var/www/html

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# 🔹 Crear directorios necesarios antes de instalar dependencias
RUN mkdir -p bootstrap/cache \
    && mkdir -p storage/framework/{cache,sessions,views} \
    && chown -R www-data:www-data storage bootstrap/cache

# Instalar dependencias PHP
RUN composer install --no-dev --optimize-autoloader

# 🚫 Eliminamos npm install y npm run build (innecesarios para Filament)

# Ajustar permisos de storage y bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Exponer puerto
EXPOSE 80

# Iniciar con Apache en vez de php artisan serve
CMD ["apache2-foreground"]
