FROM php:8.5-apache

# Instal utilitas sistem dasar
RUN apt-get update && apt-get install -y \
    zip \
    unzip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instal ekstensi PHP resmi menggunakan install-php-extensions (kompatibel penuh dengan PHP 8.5)
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_mysql gd opcache redis zip

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
