FROM php:8.5-apache-bookworm

# Instalar dependencias para las extensiones de PDO
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo_mysql pdo_pgsql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copiar el VirtualHost de Apache y activarlo
COPY apache/jukebox.conf /etc/apache2/sites-available/jukebox.conf
RUN a2ensite jukebox.conf && a2dissite 000-default.conf