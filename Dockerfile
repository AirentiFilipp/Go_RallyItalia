# ── Base: PHP 8.2 + Apache ──────────────────────────────
FROM php:8.2-apache

# Estensioni PHP necessarie
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_mysql mysqli \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Abilita mod_rewrite per URL puliti
RUN a2enmod rewrite

# Configurazione Apache: AllowOverride per .htaccess
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Document root
WORKDIR /var/www/html

# Permessi corretti
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
