#!/bin/bash
################################################################################
# Health Monitor - Ubuntu 25.10 Compatible
################################################################################
GREEN='\033[0;32m'; RED='\033[0;31m'; YELLOW='\033[1;33m'; NC='\033[0m'
print_ok() { echo -e "${GREEN}✓${NC} $1"; }
print_error() { echo -e "${RED}✗${NC} $1"; }
print_warning() { echo -e "${YELLOW}⚠${NC} $1"; }

# Rileva versione PHP
PHP_VERSION=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null || echo "8.4")

ERRORS=0; WARNINGS=0

echo "🔍 Health Check - $(date '+%Y-%m-%d %H:%M:%S')"
echo ""
echo "📦 Servizi:"
systemctl is-active --quiet nginx && print_ok "Nginx" || { print_error "Nginx DOWN"; ((ERRORS++)); }
systemctl is-active --quiet php${PHP_VERSION}-fpm && print_ok "PHP-FPM" || { print_error "PHP-FPM DOWN"; ((ERRORS++)); }
systemctl is-active --quiet mysql && print_ok "MySQL" || { print_error "MySQL DOWN"; ((ERRORS++)); }
systemctl is-active --quiet redis-server && print_ok "Redis" || { print_error "Redis DOWN"; ((ERRORS++)); }

echo ""
echo "💾 Spazio Disco:"
DISK_USAGE=$(df / | tail -1 | awk '{print $5}' | sed 's/%//')
if [ $DISK_USAGE -lt 80 ]; then
    print_ok "Spazio: ${DISK_USAGE}%"
elif [ $DISK_USAGE -lt 90 ]; then
    print_warning "Spazio: ${DISK_USAGE}%"; ((WARNINGS++))
else
    print_error "Spazio critico: ${DISK_USAGE}%"; ((ERRORS++))
fi

echo ""
echo "🧠 Memoria:"
MEM_USAGE=$(free | grep Mem | awk '{printf("%.0f", $3/$2 * 100)}')
if [ $MEM_USAGE -lt 80 ]; then
    print_ok "RAM: ${MEM_USAGE}%"
elif [ $MEM_USAGE -lt 90 ]; then
    print_warning "RAM: ${MEM_USAGE}%"; ((WARNINGS++))
else
    print_error "RAM critica: ${MEM_USAGE}%"; ((ERRORS++))
fi

echo ""
echo "════════════════════════════════════════════════════════════"
[ $ERRORS -eq 0 ] && [ $WARNINGS -eq 0 ] && echo -e "${GREEN}✓ Sistema OK${NC}"
[ $ERRORS -eq 0 ] && [ $WARNINGS -gt 0 ] && echo -e "${YELLOW}⚠ $WARNINGS warnings${NC}"
[ $ERRORS -gt 0 ] && echo -e "${RED}✗ $ERRORS errori${NC}"
echo "════════════════════════════════════════════════════════════"
echo ""
exit $ERRORS
