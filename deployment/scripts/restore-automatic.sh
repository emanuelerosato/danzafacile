#!/bin/bash
################################################################################
# DISASTER RECOVERY - Ripristino Automatico Completo
# Ripristina TUTTO da backup in modo completamente automatico
################################################################################

set -e

# Colori per output
BLUE='\033[0;34m'
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

print_step() { echo -e "\n${BLUE}▶${NC} $1"; }
print_success() { echo -e "${GREEN}✓${NC} $1"; }
print_error() { echo -e "${RED}✗${NC} $1"; }
print_warning() { echo -e "${YELLOW}⚠${NC} $1"; }

# Configurazione
BACKUP_SOURCE="/var/backups/danzafacile"  # Locale
GDRIVE_SOURCE="gdrive:danzafacile-backups"  # Google Drive
APP_DIR="/var/www/danzafacile"
RESTORE_LOG="/var/log/restore-$(date +%Y%m%d_%H%M%S).log"

# Timer
START_TIME=$(date +%s)

# Banner
clear
echo "════════════════════════════════════════════════════════════"
echo "  🆘 DISASTER RECOVERY - Ripristino Automatico Completo"
echo "════════════════════════════════════════════════════════════"
echo ""
echo "Questo script ripristinerà TUTTO il sistema da backup."
echo "Include: Database, Files, Codice, Nginx, SSL, .env"
echo ""
echo "Log salvato in: $RESTORE_LOG"
echo ""

# Funzione per calcolare tempo trascorso
elapsed_time() {
    local END_TIME=$(date +%s)
    local ELAPSED=$((END_TIME - START_TIME))
    echo "$((ELAPSED / 60)) minuti e $((ELAPSED % 60)) secondi"
}

# Funzione per verificare se siamo su server nuovo o esistente
check_existing_installation() {
    if [ -f "$APP_DIR/.env" ] && [ -d "$APP_DIR/vendor" ]; then
        print_warning "ATTENZIONE: Installazione esistente rilevata!"
        echo ""
        echo "Directory $APP_DIR già esiste con dati."
        echo ""
        read -p "Vuoi SOVRASCRIVERE tutto? (scrivi 'SI' per confermare): " confirm
        if [ "$confirm" != "SI" ]; then
            print_error "Ripristino annullato."
            exit 1
        fi
        EXISTING_INSTALL=true
    else
        EXISTING_INSTALL=false
    fi
}

# Funzione per scegliere fonte backup
choose_backup_source() {
    echo ""
    echo "Da dove vuoi ripristinare il backup?"
    echo ""
    echo "1) Backup LOCALE (/var/backups/danzafacile)"
    echo "2) Google Drive (richiede rclone configurato)"
    echo ""
    read -p "Scegli (1 o 2): " choice

    case $choice in
        1)
            BACKUP_DIR="$BACKUP_SOURCE"
            if [ ! -d "$BACKUP_DIR" ] || [ -z "$(ls -A $BACKUP_DIR 2>/dev/null)" ]; then
                print_error "Directory backup locale vuota o non esistente!"
                exit 1
            fi
            ;;
        2)
            if ! command -v rclone &> /dev/null; then
                print_error "rclone non installato!"
                echo "Installa con: apt install rclone && rclone config"
                exit 1
            fi
            # Scarica backup da Google Drive
            print_step "Download backup da Google Drive..."
            BACKUP_DIR="/tmp/restore-backup-$(date +%s)"
            mkdir -p "$BACKUP_DIR"
            rclone copy "$GDRIVE_SOURCE" "$BACKUP_DIR" --progress
            print_success "Backup scaricato da Google Drive"
            ;;
        *)
            print_error "Scelta non valida"
            exit 1
            ;;
    esac
}

# Funzione per trovare backup più recente
find_latest_backup() {
    print_step "Ricerca backup più recente..."

    # Trova file database più recente
    LATEST_DB=$(ls -t "$BACKUP_DIR"/db_*.sql.gz 2>/dev/null | head -1)

    if [ -z "$LATEST_DB" ]; then
        print_error "Nessun backup database trovato!"
        exit 1
    fi

    # Estrai timestamp dal nome file
    BACKUP_DATE=$(basename "$LATEST_DB" | sed 's/db_\(.*\)\.sql\.gz/\1/')

    print_success "Backup trovato: $BACKUP_DATE"

    # Verifica che tutti i file necessari esistano
    DB_FILE="$BACKUP_DIR/db_${BACKUP_DATE}.sql.gz"
    FILES_FILE="$BACKUP_DIR/files_${BACKUP_DATE}.tar.gz"
    ENV_FILE="$BACKUP_DIR/env_${BACKUP_DATE}.txt"
    NGINX_FILE="$BACKUP_DIR/nginx_${BACKUP_DATE}.conf"
    SSL_FILE="$BACKUP_DIR/ssl_${BACKUP_DATE}.tar.gz"
    LARAVEL_FILE="$BACKUP_DIR/laravel_${BACKUP_DATE}.tar.gz"

    echo ""
    echo "Files trovati:"
    [ -f "$DB_FILE" ] && print_success "Database: $(du -h $DB_FILE | cut -f1)" || print_warning "Database: NON TROVATO"
    [ -f "$FILES_FILE" ] && print_success "Files utenti: $(du -h $FILES_FILE | cut -f1)" || print_warning "Files: NON TROVATO"
    [ -f "$ENV_FILE" ] && print_success ".env: $(du -h $ENV_FILE | cut -f1)" || print_warning ".env: NON TROVATO"
    [ -f "$NGINX_FILE" ] && print_success "Nginx: $(du -h $NGINX_FILE | cut -f1)" || print_warning "Nginx: NON TROVATO"
    [ -f "$SSL_FILE" ] && print_success "SSL: $(du -h $SSL_FILE | cut -f1)" || print_warning "SSL: NON TROVATO"
    [ -f "$LARAVEL_FILE" ] && print_success "Laravel: $(du -h $LARAVEL_FILE | cut -f1)" || print_warning "Laravel: NON TROVATO"

    echo ""
    read -p "Procedere con questo backup? (s/n): " confirm
    if [ "$confirm" != "s" ]; then
        print_error "Ripristino annullato."
        exit 1
    fi
}

# Funzione per leggere credenziali da .env backup
read_db_credentials() {
    if [ ! -f "$ENV_FILE" ]; then
        print_error "File .env backup non trovato!"
        exit 1
    fi

    DB_DATABASE=$(grep "^DB_DATABASE=" "$ENV_FILE" | cut -d '=' -f2)
    DB_USERNAME=$(grep "^DB_USERNAME=" "$ENV_FILE" | cut -d '=' -f2)
    DB_PASSWORD=$(grep "^DB_PASSWORD=" "$ENV_FILE" | cut -d '=' -f2)

    if [ -z "$DB_DATABASE" ] || [ -z "$DB_USERNAME" ]; then
        print_error "Credenziali database non trovate in .env!"
        exit 1
    fi
}

# STEP 1: Verifica installazione esistente
{
    print_step "STEP 1/8: Verifica installazione esistente"
    check_existing_installation
} | tee -a "$RESTORE_LOG"

# STEP 2: Scegli fonte backup
{
    print_step "STEP 2/8: Selezione fonte backup"
    choose_backup_source
} | tee -a "$RESTORE_LOG"

# STEP 3: Trova backup più recente
{
    print_step "STEP 3/8: Ricerca backup"
    find_latest_backup
} | tee -a "$RESTORE_LOG"

# STEP 4: Leggi credenziali
{
    print_step "STEP 4/8: Lettura credenziali database"
    read_db_credentials
    print_success "Credenziali lette: DB=$DB_DATABASE, USER=$DB_USERNAME"
} | tee -a "$RESTORE_LOG"

# STEP 5: Ripristina Database
{
    print_step "STEP 5/8: Ripristino Database MySQL"

    # Verifica se MySQL è installato
    if ! command -v mysql &> /dev/null; then
        print_error "MySQL non installato!"
        echo "Installa con: apt install mysql-server"
        exit 1
    fi

    # Richiedi password root MySQL
    echo ""
    read -sp "Password root MySQL: " MYSQL_ROOT_PASS
    echo ""

    # Crea database se non esiste
    mysql -u root -p"$MYSQL_ROOT_PASS" -e "CREATE DATABASE IF NOT EXISTS $DB_DATABASE;" 2>/dev/null || {
        print_error "Errore accesso MySQL. Password corretta?"
        exit 1
    }

    # Crea utente se non esiste
    mysql -u root -p"$MYSQL_ROOT_PASS" -e "CREATE USER IF NOT EXISTS '$DB_USERNAME'@'localhost' IDENTIFIED BY '$DB_PASSWORD';" 2>/dev/null
    mysql -u root -p"$MYSQL_ROOT_PASS" -e "GRANT ALL PRIVILEGES ON $DB_DATABASE.* TO '$DB_USERNAME'@'localhost';" 2>/dev/null
    mysql -u root -p"$MYSQL_ROOT_PASS" -e "FLUSH PRIVILEGES;" 2>/dev/null

    print_success "Database e utente creati"

    # Ripristina dump
    gunzip < "$DB_FILE" | mysql -u root -p"$MYSQL_ROOT_PASS" "$DB_DATABASE"

    # Verifica tabelle
    TABLE_COUNT=$(mysql -u root -p"$MYSQL_ROOT_PASS" "$DB_DATABASE" -e "SHOW TABLES;" | wc -l)
    print_success "Database ripristinato: $((TABLE_COUNT - 1)) tabelle"

} | tee -a "$RESTORE_LOG"

# STEP 6: Ripristina Codice Laravel
{
    print_step "STEP 6/8: Ripristino Codice Laravel"

    # Backup directory esistente se presente
    if [ "$EXISTING_INSTALL" = true ]; then
        print_warning "Backup directory esistente..."
        mv "$APP_DIR" "$APP_DIR.backup-$(date +%s)"
    fi

    # Crea directory app
    mkdir -p "$APP_DIR"

    # Estrai codice Laravel
    tar -xzf "$LARAVEL_FILE" -C "$APP_DIR"
    print_success "Codice Laravel estratto"

    # Ripristina .env
    cp "$ENV_FILE" "$APP_DIR/.env"
    print_success "File .env ripristinato"

    # Ripristina files utenti
    if [ -f "$FILES_FILE" ]; then
        mkdir -p "$APP_DIR/storage/app/public"
        tar -xzf "$FILES_FILE" -C "$APP_DIR/storage/app/"
        print_success "Files utenti ripristinati"
    fi

    # Installa dipendenze Composer
    if command -v composer &> /dev/null; then
        cd "$APP_DIR"
        print_step "Installazione dipendenze Composer..."
        composer install --no-dev --optimize-autoloader --quiet
        print_success "Dipendenze installate"
    else
        print_warning "Composer non installato - salta dipendenze"
    fi

    # Permessi corretti
    chown -R www-data:www-data "$APP_DIR"
    chmod -R 755 "$APP_DIR"
    chmod -R 775 "$APP_DIR/storage"
    chmod -R 775 "$APP_DIR/bootstrap/cache"
    print_success "Permessi configurati"

    # Ottimizzazione Laravel
    cd "$APP_DIR"
    php artisan config:cache 2>/dev/null || true
    php artisan route:cache 2>/dev/null || true
    php artisan view:cache 2>/dev/null || true
    print_success "Cache Laravel ottimizzata"

} | tee -a "$RESTORE_LOG"

# STEP 7: Ripristina Nginx
{
    print_step "STEP 7/8: Ripristino Nginx"

    if [ ! -f "$NGINX_FILE" ]; then
        print_warning "Nginx config non trovato - skip"
    else
        # Backup config esistente
        if [ -f "/etc/nginx/sites-available/danzafacile" ]; then
            mv /etc/nginx/sites-available/danzafacile /etc/nginx/sites-available/danzafacile.backup-$(date +%s)
        fi

        # Ripristina config
        cp "$NGINX_FILE" /etc/nginx/sites-available/danzafacile

        # Crea symlink
        ln -sf /etc/nginx/sites-available/danzafacile /etc/nginx/sites-enabled/danzafacile

        # Rimuovi default se presente
        rm -f /etc/nginx/sites-enabled/default

        # Test configurazione
        if nginx -t 2>/dev/null; then
            print_success "Nginx config ripristinata"
            systemctl reload nginx
            print_success "Nginx riavviato"
        else
            print_error "Errore configurazione Nginx"
        fi
    fi

} | tee -a "$RESTORE_LOG"

# STEP 8: Ripristina SSL
{
    print_step "STEP 8/8: Ripristino Certificati SSL"

    if [ ! -f "$SSL_FILE" ]; then
        print_warning "Certificati SSL non trovati"
        print_warning "Genera nuovi certificati con: certbot --nginx -d danzafacile.it -d www.danzafacile.it"
    else
        # Estrai certificati
        tar -xzf "$SSL_FILE" -C /etc/letsencrypt/
        print_success "Certificati SSL ripristinati"

        # Riavvia Nginx per applicare SSL
        systemctl reload nginx
    fi

} | tee -a "$RESTORE_LOG"

# Riepilogo finale
{
    echo ""
    echo "════════════════════════════════════════════════════════════"
    echo "  ✅ RIPRISTINO COMPLETATO CON SUCCESSO!"
    echo "════════════════════════════════════════════════════════════"
    echo ""
    echo "📊 Statistiche:"
    echo "   • Tempo totale: $(elapsed_time)"
    echo "   • Database: $((TABLE_COUNT - 1)) tabelle ripristinate"
    echo "   • Codice Laravel: ✓ ripristinato"
    echo "   • Files utenti: ✓ ripristinati"
    echo "   • Configurazione: ✓ ripristinata"
    echo ""
    echo "🔧 Servizi:"
    systemctl is-active nginx && echo "   • Nginx: ✓ RUNNING" || echo "   • Nginx: ✗ STOPPED"
    systemctl is-active php8.4-fpm && echo "   • PHP-FPM: ✓ RUNNING" || echo "   • PHP-FPM: ✗ STOPPED"
    systemctl is-active mysql && echo "   • MySQL: ✓ RUNNING" || echo "   • MySQL: ✗ STOPPED"
    systemctl is-active redis-server && echo "   • Redis: ✓ RUNNING" || echo "   • Redis: ✗ STOPPED"
    echo ""
    echo "🌐 Verifica applicazione:"
    echo "   • URL: https://www.danzafacile.it"
    echo "   • Login: https://www.danzafacile.it/login"
    echo ""
    echo "📋 Log completo salvato in: $RESTORE_LOG"
    echo ""
    echo "════════════════════════════════════════════════════════════"
    echo ""

} | tee -a "$RESTORE_LOG"

# Test finale connessione
print_step "Test finale applicazione..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://www.danzafacile.it 2>/dev/null || echo "000")

if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "302" ]; then
    print_success "Website online! HTTP $HTTP_CODE"
else
    print_warning "Website non raggiungibile (HTTP $HTTP_CODE)"
    echo "Verifica manualmente: curl -I https://www.danzafacile.it"
fi

echo ""
print_success "Ripristino completato in $(elapsed_time)!"
echo ""

exit 0
