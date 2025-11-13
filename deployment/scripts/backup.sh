#!/bin/bash
################################################################################
# Backup Automatico
################################################################################
set -e
BLUE='\033[0;34m'; GREEN='\033[0;32m'; NC='\033[0m'
print_message() { echo -e "${BLUE}==>${NC} $1"; }
print_success() { echo -e "${GREEN}✓${NC} $1"; }

APP_DIR="/var/www/scuoladidanza"
BACKUP_DIR="/var/backups/scuoladidanza"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

DB_DATABASE=$(grep DB_DATABASE $APP_DIR/.env | cut -d '=' -f2)
DB_USERNAME=$(grep DB_USERNAME $APP_DIR/.env | cut -d '=' -f2)
DB_PASSWORD=$(grep DB_PASSWORD $APP_DIR/.env | cut -d '=' -f2)

echo "💾 Backup Automatico - $DATE"

print_message "Backup database..."
mysqldump -u $DB_USERNAME -p$DB_PASSWORD $DB_DATABASE | gzip > $BACKUP_DIR/db_${DATE}.sql.gz
print_success "Database: $(du -h $BACKUP_DIR/db_${DATE}.sql.gz | cut -f1)"

print_message "Backup files..."
tar -czf $BACKUP_DIR/files_${DATE}.tar.gz -C $APP_DIR/storage/app public 2>/dev/null || true
print_success "Files salvati"

print_message "Pulizia backup vecchi..."
find $BACKUP_DIR -type f -mtime +7 -delete
print_success "Backup completato"
echo ""
