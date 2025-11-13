#!/bin/bash
################################################################################
# System Update
################################################################################
set -e
BLUE='\033[0;34m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; NC='\033[0m'
print_message() { echo -e "${BLUE}==>${NC} $1"; }
print_success() { echo -e "${GREEN}✓${NC} $1"; }
print_warning() { echo -e "${YELLOW}⚠${NC} $1"; }

echo "🔄 System Update"
[[ $EUID -ne 0 ]] && { echo "Esegui come root"; exit 1; }

print_message "Step 1/5: Update repository..."
apt-get update -qq
print_success "Repository aggiornati"

print_message "Step 2/5: Upgrade pacchetti..."
apt-get upgrade -y
print_success "Pacchetti aggiornati"

print_message "Step 3/5: Security updates..."
apt-get dist-upgrade -y
print_success "Sicurezza aggiornata"

print_message "Step 4/5: SSL renewal..."
certbot renew --quiet
print_success "SSL verificato"

print_message "Step 5/5: Cleanup..."
apt-get autoremove -y && apt-get autoclean -y
print_success "Cleanup completato"

[ -f /var/run/reboot-required ] && print_warning "⚠️  RIAVVIO NECESSARIO: sudo reboot"

echo ""
echo "✅ Aggiornamento completato"
echo ""
