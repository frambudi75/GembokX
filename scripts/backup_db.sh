#!/bin/bash
# ==============================================================================
# GembokX — Automated Local Database Backup Script
# Creates compressed mysqldump in /var/www/html/storage/backups
# Keeps the last 7 backups automatically.
# ==============================================================================

set -e

BACKUP_DIR="/var/www/html/storage/backups"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
FILENAME="gembokx_db_${TIMESTAMP}.sql.gz"
TARGET_FILE="${BACKUP_DIR}/${FILENAME}"

mkdir -p "$BACKUP_DIR"
chmod 777 "$BACKUP_DIR" || true

DB_HOST="${DB_HOST:-db}"
DB_USER="${DB_USER:-passvault_user}"
DB_PASS="${DB_PASS:-passvault_secret123}"
DB_NAME="${DB_NAME:-passvault_db}"

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Starting GembokX database backup..."

if command -v mysqldump >/dev/null 2>&1; then
    mysqldump -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" | gzip > "$TARGET_FILE"
elif command -v mariadb-dump >/dev/null 2>&1; then
    mariadb-dump -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" | gzip > "$TARGET_FILE"
else
    echo "[!] mysqldump or mariadb-dump not found in web container. Dumping via docker exec..."
    docker exec gembokx_db mariadb-dump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" | gzip > "$TARGET_FILE"
fi

chmod 644 "$TARGET_FILE"
FILE_SIZE=$(du -h "$TARGET_FILE" | cut -f1)

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Backup successfully created: $TARGET_FILE ($FILE_SIZE)"

# Rotate: remove backups older than 7 days
find "$BACKUP_DIR" -name "gembokx_db_*.sql.gz" -type f -mtime +7 -delete 2>/dev/null || true
echo "[$(date '+%Y-%m-%d %H:%M:%S')] Old backups cleanup complete."
