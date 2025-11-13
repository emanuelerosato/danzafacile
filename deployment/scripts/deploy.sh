#!/bin/bash
################################################################################
# Deploy Aggiornamenti
################################################################################
set -e
BLUE='\033[0;34m'; GREEN='\033[0;32m'; NC='\033[0m'
print_message() { echo -e "${BLUE}==>${NC} $1"; }
print_success() { echo -e "${GREEN}✓${NC} $1"; }

APP_DIR="/var/www/scuoladidanza"
BRANCH="${1:-main}"

echo "🚀 Deploy da branch: $BRANCH"
cd $APP_DIR

print_message "Step 1/8: Modalità manutenzione..."
php artisan down --retry=60
print_success "Sito in manutenzione"

print_message "Step 2/8: Git pull..."
sudo -u deploy git fetch origin && sudo -u deploy git checkout $BRANCH && sudo -u deploy git pull origin $BRANCH
print_success "Codice aggiornato"

print_message "Step 3/8: Composer update..."
sudo -u deploy composer install --no-dev --optimize-autoloader --no-interaction
print_success "Dipendenze aggiornate"

print_message "Step 4/8: Build assets..."
sudo -u deploy npm ci --silent && sudo -u deploy npm run build
print_success "Assets compilati"

print_message "Step 5/8: Migrazioni..."
php artisan migrate --force
print_success "Database aggiornato"

print_message "Step 6/8: Pulizia cache..."
php artisan config:clear && php artisan route:clear && php artisan view:clear && php artisan cache:clear
print_success "Cache pulita"

print_message "Step 7/8: Ottimizzazione..."
php artisan config:cache && php artisan route:cache && php artisan view:cache
print_success "Ottimizzato"

print_message "Step 8/8: Restart servizi..."
systemctl reload php8.2-fpm nginx
php artisan up
print_success "Sito online!"

echo ""
echo "✅ Deploy completato!"
echo ""
