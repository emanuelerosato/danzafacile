#!/bin/bash
################################################################################
# SCUOLA DI DANZA - VPS Setup Script
# Ubuntu 25.10 Compatible Version
################################################################################

set -e
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; BLUE='\033[0;34m'; NC='\033[0m'
print_message() { echo -e "${BLUE}==>${NC} $1"; }
print_success() { echo -e "${GREEN}✓${NC} $1"; }
print_error() { echo -e "${RED}✗${NC} $1"; }
print_warning() { echo -e "${YELLOW}⚠${NC} $1"; }

echo ""
echo "╔═══════════════════════════════════════════════════════════╗"
echo "║         🩰 SCUOLA DI DANZA - VPS Setup                   ║"
echo "║         Ubuntu 25.10 Version                             ║"
echo "╚═══════════════════════════════════════════════════════════╝"
echo ""

[[ $EUID -ne 0 ]] && { print_error "Esegui come root: sudo $0"; exit 1; }

print_warning "Setup completo VPS. Tempo: ~15 minuti"
read -p "Continuare? (y/n) " -n 1 -r; echo
[[ ! $REPLY =~ ^[Yy]$ ]] && exit 1

print_message "Step 1/10: Update sistema..."
apt-get update -qq && apt-get upgrade -y -qq
apt-get install -y -qq software-properties-common curl wget git unzip
print_success "Sistema aggiornato"

print_message "Step 2/10: Installazione Nginx..."
apt-get install -y -qq nginx
systemctl enable nginx && systemctl start nginx
print_success "Nginx installato"

print_message "Step 3/10: Installazione PHP..."
# Ubuntu 25.10 ha PHP 8.3 nei repo ufficiali
apt-get install -y -qq php-fpm php-cli php-common php-mysql php-zip php-gd \
    php-mbstring php-curl php-xml php-bcmath php-redis php-intl php-soap

# Trova versione PHP installata
PHP_VERSION=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')
print_message "PHP $PHP_VERSION rilevato"

# Configura PHP
PHP_INI="/etc/php/$PHP_VERSION/fpm/php.ini"
sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 50M/' $PHP_INI
sed -i 's/post_max_size = 8M/post_max_size = 50M/' $PHP_INI
sed -i 's/memory_limit = 128M/memory_limit = 256M/' $PHP_INI
sed -i 's/max_execution_time = 30/max_execution_time = 300/' $PHP_INI

systemctl enable php$PHP_VERSION-fpm && systemctl start php$PHP_VERSION-fpm
print_success "PHP $PHP_VERSION installato"

print_message "Step 4/10: Installazione MySQL..."
apt-get install -y -qq mysql-server
systemctl enable mysql && systemctl start mysql
# Configura MySQL per localhost only
MYSQL_CNF=$(find /etc/mysql -name "mysqld.cnf" | head -1)
if [ -n "$MYSQL_CNF" ]; then
    sed -i 's/bind-address.*/bind-address = 127.0.0.1/' $MYSQL_CNF
fi
systemctl restart mysql
print_success "MySQL installato"

print_message "Step 5/10: Installazione Redis..."
apt-get install -y -qq redis-server
sed -i 's/supervised no/supervised systemd/' /etc/redis/redis.conf
sed -i 's/# maxmemory <bytes>/maxmemory 256mb/' /etc/redis/redis.conf
sed -i 's/# maxmemory-policy noeviction/maxmemory-policy allkeys-lru/' /etc/redis/redis.conf
systemctl enable redis-server && systemctl restart redis-server
print_success "Redis installato"

print_message "Step 6/10: Installazione Composer..."
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer && chmod +x /usr/local/bin/composer
print_success "Composer $(composer --version 2>/dev/null | head -n1 | cut -d' ' -f3 || echo 'installato')"

print_message "Step 7/10: Installazione Node.js 20 LTS..."
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt-get install -y -qq nodejs
print_success "Node.js $(node -v) + npm $(npm -v)"

print_message "Step 8/10: Installazione Certbot..."
apt-get install -y -qq certbot python3-certbot-nginx
print_success "Certbot installato"

print_message "Step 9/10: Configurazione Firewall..."
ufw --force reset && ufw default deny incoming && ufw default allow outgoing
ufw allow ssh && ufw allow 'Nginx Full' && ufw --force enable
print_success "Firewall configurato"

print_message "Step 10/10: Installazione Fail2Ban..."
apt-get install -y -qq fail2ban
systemctl enable fail2ban && systemctl start fail2ban
print_success "Fail2Ban attivo"

print_message "Creazione utente deploy..."
id "deploy" &>/dev/null || useradd -m -s /bin/bash -G www-data deploy
mkdir -p /var/www/danzafacile && chown -R deploy:www-data /var/www/danzafacile
print_success "Utente deploy creato"

apt-get autoremove -y -qq && apt-get autoclean -y -qq

# Salva versione PHP per script successivi
echo $PHP_VERSION > /tmp/php_version.txt

echo ""
echo "╔═══════════════════════════════════════════════════════════╗"
echo "║         ✅ SETUP COMPLETATO!                             ║"
echo "╚═══════════════════════════════════════════════════════════╝"
echo ""
print_success "Server pronto per Laravel 12"
echo ""
echo "📦 Software installato:"
echo "   • Nginx $(nginx -v 2>&1 | cut -d'/' -f2)"
echo "   • PHP $PHP_VERSION"
echo "   • MySQL $(mysql --version | cut -d' ' -f6 | cut -d',' -f1)"
echo "   • Redis $(redis-server --version | cut -d'=' -f2 | cut -d' ' -f1)"
echo "   • Node.js $(node -v)"
echo ""
echo "🎯 Prossimi passi:"
echo "   1. Configura database MySQL:"
echo "      mysql -u root"
echo "      CREATE DATABASE danzafacile;"
echo "      CREATE USER 'danzafacile'@'localhost' IDENTIFIED BY 'PASSWORD';"
echo "      GRANT ALL ON danzafacile.* TO 'danzafacile'@'localhost';"
echo "      FLUSH PRIVILEGES;"
echo ""
echo "   2. Esegui deploy-first-time.sh"
echo ""
