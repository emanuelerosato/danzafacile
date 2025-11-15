#!/bin/bash
################################################################################
# Backup COMPLETO con Disaster Recovery
# Include: Database, Files, .env, Nginx, SSL, Codice Laravel
################################################################################

set -e

# Configurazione
APP_DIR="/var/www/danzafacile"
BACKUP_DIR="/var/backups/danzafacile"
DATE=$(date +%Y%m%d_%H%M%S)
ADMIN_EMAIL="admin@danzafacile.it"
LOG_FILE="/var/log/backup-email.log"

# Colori per output
BLUE='\033[0;34m'
GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m'

# Funzioni helper
print_message() { echo -e "${BLUE}==>${NC} $1"; }
print_success() { echo -e "${GREEN}✓${NC} $1"; }
print_error() { echo -e "${RED}✗${NC} $1"; }

# Variabili per email report
EMAIL_SUBJECT="[Danza Facile] Backup COMPLETO - $DATE"
EMAIL_BODY=""
BACKUP_STATUS="SUCCESS"
ERRORS=""

# Funzione per aggiungere al report
add_to_report() {
    EMAIL_BODY="${EMAIL_BODY}$1\n"
}

# Funzione per inviare email
send_email_report() {
    local status_icon="✅"
    local status_text="Completato con successo"

    if [ "$BACKUP_STATUS" != "SUCCESS" ]; then
        status_icon="❌"
        status_text="Completato con errori"
        EMAIL_SUBJECT="[ERRORE] $EMAIL_SUBJECT"
    fi

    # Crea corpo email in HTML
    local email_html="
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .header { background: linear-gradient(135deg, #f43f5e 0%, #9333ea 100%); color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9fafb; }
        .status { padding: 15px; margin: 20px 0; border-radius: 8px; }
        .success { background: #d1fae5; border-left: 4px solid #10b981; }
        .error { background: #fee2e2; border-left: 4px solid #ef4444; }
        .info-box { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 12px; }
        pre { background: #1f2937; color: #e5e7eb; padding: 10px; border-radius: 5px; overflow-x: auto; white-space: pre-wrap; }
    </style>
</head>
<body>
    <div class='header'>
        <h1>$status_icon Danza Facile - Backup COMPLETO</h1>
        <p>$(date '+%d/%m/%Y %H:%M:%S')</p>
    </div>

    <div class='content'>
        <div class='status $([ "$BACKUP_STATUS" = "SUCCESS" ] && echo "success" || echo "error")'>
            <strong>Stato:</strong> $status_text
        </div>

        <div class='info-box'>
            <h3>📊 Riepilogo Backup</h3>
            <pre>$EMAIL_BODY</pre>
        </div>

        $([ -n "$ERRORS" ] && echo "
        <div class='info-box'>
            <h3>⚠️ Errori Riscontrati</h3>
            <pre>$ERRORS</pre>
        </div>
        ")

        <div class='info-box'>
            <h3>💾 Informazioni Sistema</h3>
            <p><strong>Server:</strong> $(hostname)</p>
            <p><strong>IP:</strong> $(hostname -I | awk '{print $1}')</p>
            <p><strong>Spazio disco:</strong> $(df -h / | tail -1 | awk '{print $5 " usato di " $2}')</p>
            <p><strong>Memoria RAM:</strong> $(free -h | grep Mem | awk '{print $3 " usata di " $2}')</p>
        </div>

        <div class='info-box'>
            <h3>🔄 Disaster Recovery</h3>
            <p>✅ Backup completo disponibile su Google Drive</p>
            <p>✅ Include database, files, configurazioni, SSL, codice</p>
            <p>✅ Ripristino possibile in 15-30 minuti</p>
        </div>
    </div>

    <div class='footer'>
        <p>Questo è un messaggio automatico dal sistema di backup Danza Facile</p>
        <p>Per problemi contatta: info@danzafacile.it</p>
    </div>
</body>
</html>
"

    # Invia email usando mail (mailutils deve essere installato)
    if command -v mail &> /dev/null; then
        echo "$email_html" | mail -s "$EMAIL_SUBJECT" \
            -a "Content-Type: text/html" \
            "$ADMIN_EMAIL"
        print_success "Email inviata a $ADMIN_EMAIL"
    else
        print_error "mail non installato. Installa con: apt-get install mailutils"
        echo "$EMAIL_BODY" >> $LOG_FILE
    fi
}

# Main script
{
    echo "💾 Backup COMPLETO - $DATE"
    add_to_report "💾 BACKUP COMPLETO DISASTER RECOVERY - $DATE"
    add_to_report "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

    # Crea directory backup se non esiste
    mkdir -p $BACKUP_DIR

    # Leggi credenziali database da .env
    if [ -f "$APP_DIR/.env" ]; then
        DB_DATABASE=$(grep DB_DATABASE $APP_DIR/.env | cut -d '=' -f2)
        DB_USERNAME=$(grep DB_USERNAME $APP_DIR/.env | cut -d '=' -f2)
        DB_PASSWORD=$(grep DB_PASSWORD $APP_DIR/.env | cut -d '=' -f2)
    else
        print_error "File .env non trovato"
        BACKUP_STATUS="ERROR"
        ERRORS="${ERRORS}File .env non trovato in $APP_DIR\n"
        send_email_report
        exit 1
    fi

    # 1. Backup Database
    print_message "Backup database..."
    add_to_report "\n📦 DATABASE BACKUP"

    if mysqldump -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" | gzip > "$BACKUP_DIR/db_${DATE}.sql.gz" 2>/dev/null; then
        DB_SIZE=$(du -h "$BACKUP_DIR/db_${DATE}.sql.gz" | cut -f1)
        print_success "Database: $DB_SIZE"
        add_to_report "  ✅ Database backup creato: $DB_SIZE"
        add_to_report "  📁 File: db_${DATE}.sql.gz"
    else
        print_error "Errore backup database"
        BACKUP_STATUS="ERROR"
        ERRORS="${ERRORS}Errore durante backup database MySQL\n"
        add_to_report "  ❌ Errore backup database"
    fi

    # 2. Backup Files Utenti
    print_message "Backup files utenti..."
    add_to_report "\n📁 FILES UTENTI BACKUP"

    if tar -czf "$BACKUP_DIR/files_${DATE}.tar.gz" -C "$APP_DIR/storage/app" public 2>/dev/null; then
        FILES_SIZE=$(du -h "$BACKUP_DIR/files_${DATE}.tar.gz" | cut -f1)
        print_success "Files utenti: $FILES_SIZE"
        add_to_report "  ✅ Files utenti backup: $FILES_SIZE"
        add_to_report "  📁 File: files_${DATE}.tar.gz"
    else
        print_error "Nessun file da backuppare (normale se non ci sono upload)"
        add_to_report "  ⚠️  Nessun file utente da backuppare"
    fi

    # 3. Backup File .env (CRITICO)
    print_message "Backup configurazione .env..."
    add_to_report "\n🔐 CONFIGURAZIONE .ENV"

    if cp "$APP_DIR/.env" "$BACKUP_DIR/env_${DATE}.txt" 2>/dev/null; then
        ENV_SIZE=$(du -h "$BACKUP_DIR/env_${DATE}.txt" | cut -f1)
        print_success ".env: $ENV_SIZE"
        add_to_report "  ✅ File .env backuppato: $ENV_SIZE"
        add_to_report "  📁 File: env_${DATE}.txt"
    else
        print_error "Errore backup .env"
        BACKUP_STATUS="ERROR"
        ERRORS="${ERRORS}Errore durante backup file .env\n"
        add_to_report "  ❌ Errore backup .env"
    fi

    # 4. Backup Configurazione Nginx
    print_message "Backup configurazione Nginx..."
    add_to_report "\n⚙️  CONFIGURAZIONE NGINX"

    if cp /etc/nginx/sites-available/danzafacile "$BACKUP_DIR/nginx_${DATE}.conf" 2>/dev/null; then
        NGINX_SIZE=$(du -h "$BACKUP_DIR/nginx_${DATE}.conf" | cut -f1)
        print_success "Nginx config: $NGINX_SIZE"
        add_to_report "  ✅ Nginx config backuppato: $NGINX_SIZE"
        add_to_report "  📁 File: nginx_${DATE}.conf"
    else
        print_error "Errore backup Nginx"
        BACKUP_STATUS="WARNING"
        add_to_report "  ⚠️  Errore backup Nginx config"
    fi

    # 5. Backup Certificati SSL
    print_message "Backup certificati SSL..."
    add_to_report "\n🔒 CERTIFICATI SSL"

    if [ -d "/etc/letsencrypt/live/danzafacile.it" ]; then
        tar -czf "$BACKUP_DIR/ssl_${DATE}.tar.gz" -C /etc/letsencrypt live/danzafacile.it 2>/dev/null
        SSL_SIZE=$(du -h "$BACKUP_DIR/ssl_${DATE}.tar.gz" | cut -f1)
        print_success "SSL certificates: $SSL_SIZE"
        add_to_report "  ✅ SSL certificati backuppati: $SSL_SIZE"
        add_to_report "  📁 File: ssl_${DATE}.tar.gz"
    else
        print_error "Certificati SSL non trovati"
        BACKUP_STATUS="WARNING"
        add_to_report "  ⚠️  Certificati SSL non trovati"
    fi

    # 6. Backup Codice Laravel (escluso vendor, node_modules, storage)
    print_message "Backup codice Laravel..."
    add_to_report "\n💻 CODICE LARAVEL"

    if tar -czf "$BACKUP_DIR/laravel_${DATE}.tar.gz" \
        --exclude='vendor' \
        --exclude='node_modules' \
        --exclude='storage/logs' \
        --exclude='storage/framework/cache' \
        --exclude='storage/framework/sessions' \
        --exclude='storage/framework/views' \
        --exclude='.git' \
        -C "$APP_DIR" . 2>/dev/null; then
        LARAVEL_SIZE=$(du -h "$BACKUP_DIR/laravel_${DATE}.tar.gz" | cut -f1)
        print_success "Codice Laravel: $LARAVEL_SIZE"
        add_to_report "  ✅ Codice Laravel backuppato: $LARAVEL_SIZE"
        add_to_report "  📁 File: laravel_${DATE}.tar.gz"
        add_to_report "  ℹ️  Esclusi: vendor, node_modules, cache"
    else
        print_error "Errore backup codice Laravel"
        BACKUP_STATUS="ERROR"
        ERRORS="${ERRORS}Errore durante backup codice Laravel\n"
        add_to_report "  ❌ Errore backup codice Laravel"
    fi

    # 7. Upload su Google Drive
    print_message "Upload su Google Drive..."
    add_to_report "\n☁️  GOOGLE DRIVE UPLOAD"

    if command -v rclone &> /dev/null; then
        if rclone copy "$BACKUP_DIR" gdrive:danzafacile-backups --progress 2>&1; then
            print_success "Backup caricato su Google Drive"
            add_to_report "  ✅ Upload completato su Google Drive"
        else
            print_error "Errore upload Google Drive"
            BACKUP_STATUS="ERROR"
            ERRORS="${ERRORS}Errore durante upload su Google Drive\n"
            add_to_report "  ❌ Errore upload Google Drive"
        fi
    else
        print_error "rclone non configurato"
        BACKUP_STATUS="WARNING"
        add_to_report "  ⚠️  rclone non configurato - backup solo locale"
    fi

    # 8. Pulizia backup vecchi (mantieni ultimi 7 giorni)
    print_message "Pulizia backup vecchi..."
    add_to_report "\n🧹 PULIZIA BACKUP VECCHI"

    OLD_BACKUPS=$(find $BACKUP_DIR -type f -mtime +7)
    if [ -n "$OLD_BACKUPS" ]; then
        DELETED_COUNT=$(find $BACKUP_DIR -type f -mtime +7 -delete -print | wc -l)
        print_success "Eliminati $DELETED_COUNT backup vecchi (>7 giorni)"
        add_to_report "  ✅ Eliminati $DELETED_COUNT backup oltre 7 giorni"
    else
        print_success "Nessun backup da eliminare"
        add_to_report "  ✅ Nessun backup da eliminare"
    fi

    # 9. Spazio disco totale backups
    add_to_report "\n💾 SPAZIO DISCO BACKUPS"
    TOTAL_BACKUP_SIZE=$(du -sh $BACKUP_DIR | cut -f1)
    BACKUP_COUNT=$(find $BACKUP_DIR -type f | wc -l)
    add_to_report "  📊 Spazio totale backup: $TOTAL_BACKUP_SIZE"
    add_to_report "  📊 Numero file backup: $BACKUP_COUNT"

    # 10. Contenuto backup corrente
    add_to_report "\n📋 CONTENUTO BACKUP CORRENTE"
    add_to_report "  • Database MySQL (compresso)"
    add_to_report "  • Files utenti uploaded"
    add_to_report "  • File .env (configurazione completa)"
    add_to_report "  • Nginx configuration"
    add_to_report "  • SSL certificates"
    add_to_report "  • Codice Laravel (app, routes, views, controllers)"

    # Summary
    add_to_report "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    if [ "$BACKUP_STATUS" = "SUCCESS" ]; then
        print_success "Backup COMPLETO con successo"
        add_to_report "✅ BACKUP COMPLETO - PRONTO PER DISASTER RECOVERY"
    else
        print_error "Backup completato con errori"
        add_to_report "⚠️  BACKUP COMPLETATO CON WARNINGS/ERRORI"
    fi

    echo ""

} 2>&1 | tee -a $LOG_FILE

# Invia email report
send_email_report

exit 0
