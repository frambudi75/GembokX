#!/bin/bash
set -e

# Pastikan direktori runtime dan upload selalu ada
mkdir -p /var/www/html/public/uploads/avatars
mkdir -p /var/www/html/storage/cache
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/storage/backups

# Berikan hak akses penuh kepada www-data untuk folder upload dan runtime storage
chown -R www-data:www-data /var/www/html/public/uploads /var/www/html/storage 2>/dev/null || true
chmod -R 777 /var/www/html/public/uploads /var/www/html/storage 2>/dev/null || true

exec "$@"
