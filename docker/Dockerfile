FROM php:8.2-apache

# Instal dependensi dan ekstensi PHP yang dibutuhkan
RUN apt-get update && apt-get install -y \
    libonig-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_mysql mbstring opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Aktifkan modul Apache rewrite dan headers
RUN a2enmod rewrite headers

# Salin konfigurasi Apache & PHP kustom
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/passvault.ini

# Set working directory
WORKDIR /var/www/html

# Izin kepemilikan file untuk www-data
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
CMD ["apache2-foreground"]
