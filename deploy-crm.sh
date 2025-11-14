#!/bin/bash

###############################################################################
# DEPLOY SCRIPT - Sistema CRM Lead Management
# Esegui questo script sul VPS per deployare le nuove funzionalità
###############################################################################

echo "🚀 Deploy Sistema CRM Lead Management"
echo "======================================"
echo ""

# Colori per output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Directory progetto
PROJECT_DIR="/var/www/danzafacile"

echo -e "${YELLOW}📂 Cambio directory...${NC}"
cd $PROJECT_DIR || { echo -e "${RED}❌ Errore: directory non trovata${NC}"; exit 1; }

echo -e "${YELLOW}🔄 Pull modifiche da GitHub...${NC}"
git pull origin deploy/vps-setup || { echo -e "${RED}❌ Errore git pull${NC}"; exit 1; }

echo -e "${YELLOW}📦 Composer install...${NC}"
composer install --no-dev --optimize-autoloader || { echo -e "${RED}❌ Errore composer${NC}"; exit 1; }

echo -e "${YELLOW}🗄️  Eseguo migration...${NC}"
php artisan migrate --force || { echo -e "${RED}❌ Errore migration${NC}"; exit 1; }

echo -e "${YELLOW}🧹 Clear cache...${NC}"
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo -e "${YELLOW}⚡ Optimize...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo -e "${YELLOW}🔄 Restart services...${NC}"
systemctl restart php8.4-fpm
systemctl restart nginx

echo -e "${YELLOW}🔥 Reset opcache...${NC}"
php -r "opcache_reset();"

echo ""
echo -e "${GREEN}✅ Deploy completato con successo!${NC}"
echo ""
echo "📊 Prossimi passi:"
echo "  1. Testa il form su https://www.danzafacile.it"
echo "  2. Compila il form per creare un lead di test"
echo "  3. Accedi come super admin"
echo "  4. Vai su 'Lead CRM' nel menu"
echo "  5. Verifica che il lead sia presente"
echo ""
echo "🔗 URL Dashboard: https://www.danzafacile.it/super-admin/leads"
echo ""
