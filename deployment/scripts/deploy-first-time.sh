#!/bin/bash
################################################################################
# SCUOLA DI DANZA - First Time Deploy
################################################################################
set -e
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; BLUE='\033[0;34m'; NC='\033[0m'
print_message() { echo -e "${BLUE}==>${NC} $1"; }
print_success() { echo -e "${GREEN}✓${NC} $1"; }
print_error() { echo -e "${RED}✗${NC} $1"; }
print_warning() { echo -e "${YELLOW}⚠${NC} $1"; }

echo ""
echo "╔═══════════════════════════════════════════════════════════╗"
echo "║         🚀 SCUOLA DI DANZA - First Deploy                ║"
echo "╚═══════════════════════════════════════════════════════════╝"
echo ""

[[ $EUID -ne 0 ]] && { print_error "Esegui come root"; exit 1; }

read -p "📝 Dominio (es: scuoladidanza.it): " DOMAIN
[[ -z "$DOMAIN" ]] && { print_error "Dominio obbligatorio!"; exit 1; }

read -p "📝 Nome database (default: scuoladidanza): " DB_NAME
DB_NAME=${DB_NAME:-scuoladidanza}

read -p "📝 Username database (default: scuoladidanza): " DB_USER
DB_USER=${DB_USER:-scuoladidanza}

read -sp "🔐 Password database: " DB_PASSWORD
echo ""
[[ -z "$DB_PASSWORD" ]] && { print_error "Password obbligatoria!"; exit 1; }

read -p "📧 Email Aruba (es: admin@scuoladidanza.it): " ARUBA_EMAIL
[[ -z "$ARUBA_EMAIL" ]] && { print_error "Email obbligatoria!"; exit 1; }

read -sp "🔐 Password email Aruba: " ARUBA_PASSWORD
echo ""
[[ -z "$ARUBA_PASSWORD" ]] && { print_error "Password email obbligatoria!"; exit 1; }

read -p "📦 Repository GitHub (default: emanuelerosato/scuoladidanza): " GITHUB_REPO
GITHUB_REPO=${GITHUB_REPO:-emanuelerosato/scuoladidanza}

read -p "🌿 Branch (default: main): " BRANCH
BRANCH=${BRANCH:-main}

APP_DIR="/var/www/scuoladidanza"

print_message "Step 1/9: Verifica database..."
if ! mysql -u root -e "USE $DB_NAME" 2>/dev/null; then
    mysql -u root <<EOF
CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
EOF
fi
print_success "Database configurato"

print_message "Step 2/9: Clone repository..."
if [ -d "$APP_DIR/.git" ]; then
    cd $APP_DIR && sudo -u deploy git pull origin $BRANCH
else
    rm -rf $APP_DIR
    sudo -u deploy git clone -b $BRANCH https://github.com/$GITHUB_REPO.git $APP_DIR
fi
cd $APP_DIR
print_success "Repository clonato"

print_message "Step 3/9: Installazione dipendenze..."
sudo -u deploy composer install --no-dev --optimize-autoloader --no-interaction
print_success "Composer completato"

print_message "Step 4/9: Build assets..."
sudo -u deploy npm ci --silent && sudo -u deploy npm run build
print_success "Assets compilati"

print_message "Step 5/9: Configurazione .env..."
APP_KEY=$(php artisan key:generate --show)
cat > $APP_DIR/.env <<EOF
APP_NAME="Scuola di Danza"
APP_ENV=production
APP_KEY=$APP_KEY
APP_DEBUG=false
APP_TIMEZONE=Europe/Rome
APP_URL=https://$DOMAIN
APP_LOCALE=it

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=$DB_NAME
DB_USERNAME=$DB_USER
DB_PASSWORD=$DB_PASSWORD

CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtps.aruba.it
MAIL_PORT=465
MAIL_USERNAME=$ARUBA_EMAIL
MAIL_PASSWORD=$ARUBA_PASSWORD
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=$ARUBA_EMAIL
MAIL_FROM_NAME="Scuola di Danza"

LOG_CHANNEL=stack
LOG_LEVEL=error
SANCTUM_STATEFUL_DOMAINS=$DOMAIN
SESSION_DOMAIN=$DOMAIN
EOF
chown deploy:www-data $APP_DIR/.env && chmod 640 $APP_DIR/.env
print_success ".env configurato"

print_message "Step 6/9: Setup Laravel..."
php artisan storage:link
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
chown -R deploy:www-data $APP_DIR
chmod -R 755 $APP_DIR
chmod -R 775 $APP_DIR/storage $APP_DIR/bootstrap/cache
print_success "Laravel configurato"

print_message "Step 7/9: Configurazione Nginx..."
cat > /etc/nginx/sites-available/scuoladidanza <<NGEOF
server {
    listen 80;
    server_name $DOMAIN www.$DOMAIN;
    root $APP_DIR/public;
    index index.php;
    
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\. {
        deny all;
    }
    
    client_max_body_size 50M;
}
NGEOF
ln -sf /etc/nginx/sites-available/scuoladidanza /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx
print_success "Nginx configurato"

print_message "Step 8/9: Setup SSL..."
read -p "DNS configurato? (y/n) " -n 1 -r; echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    certbot --nginx -d $DOMAIN -d www.$DOMAIN --non-interactive --agree-tos --email $ARUBA_EMAIL --redirect
    print_success "SSL installato"
else
    print_warning "Esegui manualmente: certbot --nginx -d $DOMAIN -d www.$DOMAIN"
fi

print_message "Step 9/9: Setup Cron..."
(crontab -l 2>/dev/null | grep -v "scuoladidanza"; echo "* * * * * cd $APP_DIR && php artisan schedule:run >> /dev/null 2>&1") | crontab -
print_success "Cron configurato"

echo ""
echo "╔═══════════════════════════════════════════════════════════╗"
echo "║         🎉 DEPLOY COMPLETATO!                            ║"
echo "╚═══════════════════════════════════════════════════════════╝"
echo ""
print_success "Sito online: https://$DOMAIN"
echo ""
