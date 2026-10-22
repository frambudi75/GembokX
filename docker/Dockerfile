FROM php:8.3-apache

# Instal dependensi dan ekstensi PHP yang dibutuhkan (termasuk GD untuk kompresi avatar)
RUN apt-get update && apt-get install -y \
    libonig-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libfreetype6-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install pdo pdo_mysql mbstring opcache gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Aktifkan modul Apache rewrite dan headers
RUN a2enmod rewrite headers

# Salin konfigurasi Apache & PHP kustom
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/passvault.ini
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Set working directory
WORKDIR /var/www/html

EXPOSE 80
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]
