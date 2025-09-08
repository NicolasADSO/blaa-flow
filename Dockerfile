# Imagen base de PHP 8.2 con Apache
FROM php:8.2-apache

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    unzip git curl libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libzip-dev zip \
    libicu-dev g++ \
    && docker-php-ext-install pdo pdo_mysql gd mbstring zip exif pcntl intl bcmath

# Copiar archivos del proyecto
COPY . /var/www/html
WORKDIR /var/www/html

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# Crear carpetas necesarias (cache, sesiones, vistas, database)
RUN mkdir -p bootstrap/cache \
    && mkdir -p storage/framework/{cache,sessions,views} \
    && mkdir -p database \
    && chmod -R 775 bootstrap/cache storage database \
    && chown -R www-data:www-data bootstrap/cache storage database

# Instalar dependencias PHP (sin artisan aún)
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Configurar Apache para servir Laravel desde /public
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|/var/www/|/var/www/html/public|g' /etc/apache2/apache2.conf

# 🔹 Habilitar mod_rewrite para que funcionen las rutas de Laravel
RUN a2enmod rewrite \
    && echo "<Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>" > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

# Exponer puerto
EXPOSE 80

# Usar Apache como servidor
CMD ["apache2-foreground"]
