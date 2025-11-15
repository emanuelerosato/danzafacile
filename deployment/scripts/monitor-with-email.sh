#!/bin/bash
################################################################################
# Health Monitor con Notifica Email
# Monitora servizi, risorse e invia alert solo se ci sono problemi
################################################################################

# Configurazione
ADMIN_EMAIL="admin@danzafacile.it"
APP_DIR="/var/www/danzafacile"
LOG_FILE="/var/log/monitor-email.log"

# Colori
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

print_ok() { echo -e "${GREEN}✓${NC} $1"; }
print_error() { echo -e "${RED}✗${NC} $1"; }
print_warning() { echo -e "${YELLOW}⚠${NC} $1"; }

# Rileva versione PHP
PHP_VERSION=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null || echo "8.4")

# Variabili stato
ERRORS=0
WARNINGS=0
REPORT=""
SEND_EMAIL=false  # Invia email solo se ci sono problemi

# Funzione per aggiungere al report
add_to_report() {
    REPORT="${REPORT}$1\n"
}

# Funzione invio email
send_alert_email() {
    local severity="$1"  # ERROR, WARNING, INFO
    local subject_prefix=""
    local bg_color="#f43f5e"

    case $severity in
        ERROR)
            subject_prefix="🚨 [ERRORE]"
            bg_color="#ef4444"
            ;;
        WARNING)
            subject_prefix="⚠️ [WARNING]"
            bg_color="#f59e0b"
            ;;
        INFO)
            subject_prefix="ℹ️ [INFO]"
            bg_color="#3b82f6"
            ;;
    esac

    EMAIL_SUBJECT="$subject_prefix Danza Facile - Health Monitor $(date '+%d/%m/%Y %H:%M')"

    # HTML email
    email_html="
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .header { background: $bg_color; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9fafb; }
        .alert-box { padding: 15px; margin: 20px 0; border-radius: 8px; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .error { border-left: 4px solid #ef4444; }
        .warning { border-left: 4px solid #f59e0b; }
        .info-box { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        pre { background: #1f2937; color: #e5e7eb; padding: 10px; border-radius: 5px; overflow-x: auto; white-space: pre-wrap; }
        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 12px; }
        .metric { display: inline-block; margin: 10px; padding: 10px 15px; background: #e5e7eb; border-radius: 5px; }
    </style>
</head>
<body>
    <div class='header'>
        <h1>$subject_prefix Danza Facile Health Monitor</h1>
        <p>$(date '+%d/%m/%Y %H:%M:%S')</p>
    </div>

    <div class='content'>
        <div class='alert-box $([ $ERRORS -gt 0 ] && echo "error" || echo "warning")'>
            <h3>📊 Riepilogo Stato</h3>
            <div class='metric'>❌ Errori: <strong>$ERRORS</strong></div>
            <div class='metric'>⚠️ Warning: <strong>$WARNINGS</strong></div>
        </div>

        <div class='info-box'>
            <h3>📋 Dettaglio Status</h3>
            <pre>$REPORT</pre>
        </div>

        <div class='info-box'>
            <h3>💻 Informazioni Server</h3>
            <p><strong>Hostname:</strong> $(hostname)</p>
            <p><strong>IP:</strong> $(hostname -I | awk '{print $1}')</p>
            <p><strong>Uptime:</strong> $(uptime -p)</p>
            <p><strong>Load Average:</strong> $(uptime | awk -F'load average:' '{print $2}')</p>
        </div>

        <div class='info-box'>
            <h3>🔧 Azioni Consigliate</h3>
            $([ $ERRORS -gt 0 ] && echo "<p>⚠️ <strong>Accedi subito al server per verificare i servizi down</strong></p>" || echo "<p>✅ Monitorare situazione warnings</p>")
            <p>📱 Accesso SSH: <code>ssh root@$(hostname -I | awk '{print $1}')</code></p>
            <p>📊 Controlla logs: <code>tail -f /var/log/monitor.log</code></p>
        </div>
    </div>

    <div class='footer'>
        <p>Questo è un alert automatico dal sistema di monitoraggio Danza Facile</p>
        <p>Per problemi: info@danzafacile.it</p>
    </div>
</body>
</html>
"

    if command -v mail &> /dev/null; then
        echo "$email_html" | mail -s "$EMAIL_SUBJECT" \
            -a "Content-Type: text/html" \
            "$ADMIN_EMAIL"
        echo "📧 Email alert inviata a $ADMIN_EMAIL"
    else
        echo "⚠️ mail non installato - alert salvato in log"
    fi
}

# Main monitoring
{
    echo "🔍 Health Check - $(date '+%Y-%m-%d %H:%M:%S')"
    add_to_report "🔍 HEALTH CHECK - $(date '+%Y-%m-%d %H:%M:%S')"
    add_to_report "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

    # 1. Verifica Servizi
    echo ""
    echo "📦 Servizi:"
    add_to_report "\n📦 SERVIZI"

    # Nginx
    if systemctl is-active --quiet nginx; then
        print_ok "Nginx"
        add_to_report "  ✅ Nginx: RUNNING"
    else
        print_error "Nginx DOWN"
        add_to_report "  ❌ Nginx: DOWN"
        ((ERRORS++))
        SEND_EMAIL=true
    fi

    # PHP-FPM
    if systemctl is-active --quiet php${PHP_VERSION}-fpm; then
        print_ok "PHP-FPM ${PHP_VERSION}"
        add_to_report "  ✅ PHP-FPM ${PHP_VERSION}: RUNNING"
    else
        print_error "PHP-FPM DOWN"
        add_to_report "  ❌ PHP-FPM: DOWN"
        ((ERRORS++))
        SEND_EMAIL=true
    fi

    # MySQL
    if systemctl is-active --quiet mysql; then
        print_ok "MySQL"
        add_to_report "  ✅ MySQL: RUNNING"
    else
        print_error "MySQL DOWN"
        add_to_report "  ❌ MySQL: DOWN"
        ((ERRORS++))
        SEND_EMAIL=true
    fi

    # Redis
    if systemctl is-active --quiet redis-server; then
        print_ok "Redis"
        add_to_report "  ✅ Redis: RUNNING"
    else
        print_error "Redis DOWN"
        add_to_report "  ❌ Redis: DOWN"
        ((ERRORS++))
        SEND_EMAIL=true
    fi

    # 2. Spazio Disco
    echo ""
    echo "💾 Spazio Disco:"
    add_to_report "\n💾 SPAZIO DISCO"

    DISK_USAGE=$(df / | tail -1 | awk '{print $5}' | sed 's/%//')
    DISK_TOTAL=$(df -h / | tail -1 | awk '{print $2}')
    DISK_USED=$(df -h / | tail -1 | awk '{print $3}')

    if [ $DISK_USAGE -lt 80 ]; then
        print_ok "Spazio: ${DISK_USAGE}% ($DISK_USED/$DISK_TOTAL)"
        add_to_report "  ✅ Utilizzo: ${DISK_USAGE}% ($DISK_USED/$DISK_TOTAL)"
    elif [ $DISK_USAGE -lt 90 ]; then
        print_warning "Spazio: ${DISK_USAGE}% ($DISK_USED/$DISK_TOTAL)"
        add_to_report "  ⚠️  Utilizzo: ${DISK_USAGE}% ($DISK_USED/$DISK_TOTAL) - Vicino al limite"
        ((WARNINGS++))
        SEND_EMAIL=true
    else
        print_error "Spazio critico: ${DISK_USAGE}% ($DISK_USED/$DISK_TOTAL)"
        add_to_report "  ❌ Utilizzo CRITICO: ${DISK_USAGE}% ($DISK_USED/$DISK_TOTAL)"
        ((ERRORS++))
        SEND_EMAIL=true
    fi

    # 3. Memoria RAM
    echo ""
    echo "🧠 Memoria:"
    add_to_report "\n🧠 MEMORIA RAM"

    MEM_USAGE=$(free | grep Mem | awk '{printf("%.0f", $3/$2 * 100)}')
    MEM_TOTAL=$(free -h | grep Mem | awk '{print $2}')
    MEM_USED=$(free -h | grep Mem | awk '{print $3}')

    if [ $MEM_USAGE -lt 80 ]; then
        print_ok "RAM: ${MEM_USAGE}% ($MEM_USED/$MEM_TOTAL)"
        add_to_report "  ✅ Utilizzo: ${MEM_USAGE}% ($MEM_USED/$MEM_TOTAL)"
    elif [ $MEM_USAGE -lt 90 ]; then
        print_warning "RAM: ${MEM_USAGE}% ($MEM_USED/$MEM_TOTAL)"
        add_to_report "  ⚠️  Utilizzo: ${MEM_USAGE}% ($MEM_USED/$MEM_TOTAL) - Vicino al limite"
        ((WARNINGS++))
        SEND_EMAIL=true
    else
        print_error "RAM critica: ${MEM_USAGE}% ($MEM_USED/$MEM_TOTAL)"
        add_to_report "  ❌ Utilizzo CRITICO: ${MEM_USAGE}% ($MEM_USED/$MEM_TOTAL)"
        ((ERRORS++))
        SEND_EMAIL=true
    fi

    # 4. CPU Load
    echo ""
    echo "⚡ CPU Load:"
    add_to_report "\n⚡ CPU LOAD"

    LOAD_1=$(uptime | awk -F'load average:' '{print $2}' | awk -F',' '{print $1}' | xargs)
    LOAD_5=$(uptime | awk -F'load average:' '{print $2}' | awk -F',' '{print $2}' | xargs)
    LOAD_15=$(uptime | awk -F'load average:' '{print $2}' | awk -F',' '{print $3}' | xargs)

    print_ok "Load Average: $LOAD_1 (1m), $LOAD_5 (5m), $LOAD_15 (15m)"
    add_to_report "  📊 Load: $LOAD_1 (1m), $LOAD_5 (5m), $LOAD_15 (15m)"

    # 5. Verifica Applicazione
    echo ""
    echo "🌐 Applicazione Web:"
    add_to_report "\n🌐 APPLICAZIONE WEB"

    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://www.danzafacile.it 2>/dev/null || echo "000")

    if [ "$HTTP_CODE" = "200" ]; then
        print_ok "Website: ONLINE (HTTP $HTTP_CODE)"
        add_to_report "  ✅ Website: ONLINE (HTTP $HTTP_CODE)"
    else
        print_error "Website: PROBLEMI (HTTP $HTTP_CODE)"
        add_to_report "  ❌ Website: PROBLEMI (HTTP $HTTP_CODE)"
        ((ERRORS++))
        SEND_EMAIL=true
    fi

    # Summary
    echo ""
    echo "════════════════════════════════════════════════════════════"
    add_to_report "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

    if [ $ERRORS -eq 0 ] && [ $WARNINGS -eq 0 ]; then
        echo -e "${GREEN}✓ Sistema OK${NC}"
        add_to_report "✅ SISTEMA OK - Nessun problema rilevato"
    elif [ $ERRORS -eq 0 ] && [ $WARNINGS -gt 0 ]; then
        echo -e "${YELLOW}⚠ $WARNINGS warnings${NC}"
        add_to_report "⚠️  WARNINGS RILEVATI: $WARNINGS"
    else
        echo -e "${RED}✗ $ERRORS errori, $WARNINGS warnings${NC}"
        add_to_report "❌ ERRORI CRITICI: $ERRORS | WARNINGS: $WARNINGS"
    fi

    echo "════════════════════════════════════════════════════════════"
    echo ""

} 2>&1 | tee -a $LOG_FILE

# Invia email SOLO se ci sono problemi
if [ "$SEND_EMAIL" = true ]; then
    if [ $ERRORS -gt 0 ]; then
        send_alert_email "ERROR"
    elif [ $WARNINGS -gt 0 ]; then
        send_alert_email "WARNING"
    fi
fi

exit $ERRORS
