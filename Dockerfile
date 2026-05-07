# Usamos una imagen oficial de PHP con Apache
FROM php:8.2-apache

# Instalamos la extensión mysqli
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Habilitamos el módulo de reescritura de Apache
RUN a2enmod rewrite

# ✅ Configuración de Apache para permitir acceso a los archivos del volumen
RUN echo '<Directory /var/www/html>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/parktech.conf \
&& a2enconf parktech

# Correr Apache como root para evitar problemas de permisos con el volumen

