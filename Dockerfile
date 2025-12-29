FROM php:8.2-apache

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    curl \
    && docker-php-ext-install zip pdo pdo_mysql \
    && docker-php-ext-enable pdo_mysql

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Habilitar mod_rewrite do Apache
RUN a2enmod rewrite

# Configurar Apache para permitir .htaccess
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Configurar permissões
RUN chown -R www-data:www-data /var/www/html

WORKDIR /var/www/html

