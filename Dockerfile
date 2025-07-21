# Usar imagen con PHP y extensiones preinstaladas
FROM php:8.2-apache

# Instalar dependencias básicas y MongoDB extension
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libssl-dev \
    pkg-config \
    libcurl4-openssl-dev \
    && pecl install --configureoptions 'enable-mongodb-developer-flags="no"' mongodb \
    && docker-php-ext-enable mongodb \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos del proyecto
COPY . .

# Instalar dependencias de Composer (incluyendo MongoDB)
RUN composer install --no-dev --optimize-autoloader --ignore-platform-req=ext-mongodb

# Configurar permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Crear directorios necesarios
RUN mkdir -p logs temp \
    && chmod -R 777 logs temp

# Habilitar módulos Apache necesarios
RUN a2enmod rewrite headers deflate expires

# Configurar Apache
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html\n\
    DirectoryIndex dashboard.html index.php index.html\n\
    <Directory /var/www/html>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Exponer puerto 80
EXPOSE 80

# Comando para iniciar Apache
CMD ["apache2-foreground"]