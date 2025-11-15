# 🔧 Manutenzione Automatica Server - Danza Facile

**Server**: 157.230.114.252 (danzafacile)
**Data Setup**: 2025-01-15
**Data Attivazione Email**: 2025-11-15
**Status**: ✅ Attivo e Monitorato con Email Report

---

## 📋 Script Automatici Installati

### 1. 💾 Backup Automatico
**Script**: `/root/backup.sh`
**Cronjob**: `0 */3 * * *` (Ogni 3 ore)
**Log**: `/var/log/backup.log`
**Orari**: 00:00, 03:00, 06:00, 09:00, 12:00, 15:00, 18:00, 21:00 (8 backup/giorno)

**Funzionalità DISASTER RECOVERY**:
- ✅ Backup database MySQL (gzip compressed)
- ✅ Backup files uploaded (`storage/app/public`)
- ✅ **Backup file .env** (configurazione completa)
- ✅ **Backup Nginx config** (web server)
- ✅ **Backup SSL certificates** (HTTPS)
- ✅ **Backup codice Laravel** (app, routes, controllers)
- ✅ Upload automatico su Google Drive
- ✅ Retention: 7 giorni (elimina backup vecchi)
- ✅ **Invio email report** dopo ogni backup
- ✅ **Ripristino completo in 15-30 minuti**

**Email Report Include**:
- Status backup (successo/errori)
- Dimensione files backuppati
- Upload Google Drive status
- Spazio disco totale backup
- Alert se ci sono errori

### 2. 🔍 Health Monitor
**Script**: `/root/monitor.sh`
**Cronjob**: `*/15 * * * *` (Ogni 15 minuti)
**Log**: `/var/log/monitor.log`
**Frequenza**: 96 check al giorno (alta reattività)

**Controlla**:
- ✅ Servizi: Nginx, PHP-FPM, MySQL, Redis
- ✅ Spazio disco (alert se > 80%)
- ✅ Memoria RAM (alert se > 80%)
- ✅ CPU Load Average
- ✅ Website online (HTTP 200)
- ✅ **Invio email SOLO se problemi**

**Email Alert Include**:
- Lista servizi down
- Metriche risorse critiche
- Azioni consigliate
- Comandi SSH per accesso rapido

### 3. 🔄 System Update
**Script**: `/root/update-system.sh`
**Cronjob**: Manuale (non automatico per sicurezza)
**Esecuzione**: Manuale quando necessario

**Operazioni**:
- ✅ Update repository APT
- ✅ Upgrade pacchetti
- ✅ Security updates (dist-upgrade)
- ✅ SSL certificate renewal (certbot)
- ✅ Cleanup (autoremove/autoclean)
- ⚠️ Alert se riavvio necessario

---

## 🕐 Cronologia Esecuzione

### Cronjobs Attivi

```bash
# Backup automatico - Ogni 3 ore (8 volte al giorno)
0 */3 * * * /root/backup.sh >> /var/log/backup.log 2>&1

# Monitor sistema - Ogni 15 minuti (96 check/giorno)
*/15 * * * * /root/monitor.sh >> /var/log/monitor.log 2>&1

# Laravel scheduler - Ogni minuto (per queue, tasks)
* * * * * cd /var/www/danzafacile && php artisan schedule:run >> /dev/null 2>&1
```

### Verificare Cronjobs

```bash
# Visualizza cronjobs attivi
crontab -l

# Modifica cronjobs
crontab -e
```

---

## 📧 Configurazione Email

### SMTP Configurato: SendGrid

Le email sono inviate tramite **SendGrid SMTP** (stesso servizio usato da Laravel).

**Configurazione Postfix**:
- **SMTP Server**: `smtp.sendgrid.net:2525`
- **Username**: `apikey`
- **Password**: SendGrid API Key (condivisa con Laravel)
- **Mittente**: `info@danzafacile.it` (Sender Identity verificato)
- **Destinatario**: `admin@danzafacile.it`

**Files configurazione**:
- `/etc/postfix/sasl_passwd` - Credenziali SMTP SendGrid
- `/etc/postfix/generic` - Sender rewriting (root → info@danzafacile.it)
- `/etc/postfix/main.cf` - Configurazione Postfix principale

### Come Funziona

1. Script bash inviano email tramite comando `mail`
2. Postfix intercetta email e le reindirizza a SendGrid
3. SendGrid valida mittente (`info@danzafacile.it`) e invia
4. Email arriva a `admin@danzafacile.it`

### Personalizzare Email Destinataria

Per cambiare l'email destinataria, modifica negli script:

```bash
# In /root/backup.sh
ADMIN_EMAIL="nuova-email@example.com"

# In /root/monitor.sh
ADMIN_EMAIL="nuova-email@example.com"

# Nessuna modifica Postfix necessaria!
```

### Test Invio Email

```bash
# Test manuale invio email
echo "Test email" | mail -s "Test Subject" admin@danzafacile.it

# Verifica log email
tail -f /var/log/mail.log

# Cerca conferma invio:
# "status=sent (250 Ok: queued as...)" = SUCCESSO
# "status=bounced" = FALLITO
```

### Troubleshooting Email

**Email non ricevute:**

```bash
# 1. Verifica Postfix attivo
systemctl status postfix

# 2. Controlla log errori
tail -50 /var/log/mail.log | grep -i error

# 3. Test SendGrid
echo "Test" | mail -s "Test SendGrid" admin@danzafacile.it
tail -20 /var/log/mail.log

# 4. Verifica credenziali SendGrid
cat /etc/postfix/sasl_passwd
# Deve contenere: [smtp.sendgrid.net]:2525 apikey:SG.xxx

# 5. Verifica sender rewriting
cat /etc/postfix/generic
# Deve contenere: root info@danzafacile.it
```

**Errore "550 Sender Identity":**

Significa che il mittente non è verificato su SendGrid.

```bash
# Verifica che /etc/postfix/generic esista
cat /etc/postfix/generic

# Rigenera hash
postmap /etc/postfix/generic

# Riavvia Postfix
systemctl restart postfix
```

---

## 🧪 Testing Script

### Test Backup (manuale)

```bash
# Esegui backup manualmente
/root/backup.sh

# Controlla log
tail -50 /var/log/backup.log

# Verifica backup creati
ls -lh /var/backups/danzafacile/

# Controlla email ricevuta
```

### Test Monitor (manuale)

```bash
# Esegui monitor manualmente
/root/monitor.sh

# Controlla log
tail -50 /var/log/monitor.log

# Simula errore (ferma Nginx per test)
systemctl stop nginx
/root/monitor.sh  # Dovrebbe inviare email alert
systemctl start nginx
```

### Test Update (manuale)

```bash
# Esegui update (ATTENZIONE: può richiedere riavvio)
/root/update-system.sh

# Verifica se riavvio necessario
cat /var/run/reboot-required 2>/dev/null && echo "Riavvio necessario" || echo "OK"
```

---

## 📊 Monitoring Dashboard

### Verifica Stato Sistema

```bash
# SSH al server
ssh root@157.230.114.252

# Check servizi
systemctl status nginx php8.4-fpm mysql redis-server

# Check risorse
df -h              # Spazio disco
free -h            # RAM
uptime             # Load average
htop               # Processo interattivo
```

### Check Backup

```bash
# Lista backup
ls -lh /var/backups/danzafacile/

# Ultimo backup
ls -lt /var/backups/danzafacile/ | head -5

# Spazio totale backup
du -sh /var/backups/danzafacile/

# Google Drive sync status (se rclone configurato)
rclone ls gdrive:danzafacile-backups
```

### Check Logs

```bash
# Backup log
tail -f /var/log/backup.log

# Monitor log
tail -f /var/log/monitor.log

# Laravel log
tail -f /var/www/danzafacile/storage/logs/laravel.log

# Nginx access log
tail -f /var/log/nginx/access.log

# Nginx error log
tail -f /var/log/nginx/error.log

# MySQL error log
tail -f /var/log/mysql/error.log
```

---

## ⚙️ Configurazione Avanzata

### Modificare Frequenza Backup

```bash
# Edita crontab
crontab -e

# Esempi:
0 */3 * * *   # Ogni 3 ore (attuale - 8 backup/giorno)
0 */6 * * *   # Ogni 6 ore (4 backup/giorno)
0 */12 * * *  # Ogni 12 ore (2 backup/giorno)
0 3 * * *     # Una volta al giorno alle 3:00 AM
```

### Modificare Frequenza Monitor

```bash
# Edita crontab
crontab -e

# Esempi:
*/15 * * * *  # Ogni 15 minuti (attuale - 96 check/giorno)
*/30 * * * *  # Ogni 30 minuti (48 check/giorno)
0 * * * *     # Ogni ora (24 check/giorno)
*/5 * * * *   # Ogni 5 minuti (288 check/giorno - molto aggressivo)
```

### Aggiungere Notifiche Slack/Telegram

Per inviare notifiche anche su Slack o Telegram, modifica gli script aggiungendo:

```bash
# Slack Webhook
SLACK_WEBHOOK="https://hooks.slack.com/services/YOUR/WEBHOOK/URL"
curl -X POST -H 'Content-type: application/json' \
    --data "{\"text\":\"$MESSAGE\"}" \
    $SLACK_WEBHOOK

# Telegram Bot
TELEGRAM_BOT_TOKEN="your_bot_token"
TELEGRAM_CHAT_ID="your_chat_id"
curl -s -X POST "https://api.telegram.org/bot${TELEGRAM_BOT_TOKEN}/sendMessage" \
    -d chat_id=${TELEGRAM_CHAT_ID} \
    -d text="$MESSAGE"
```

---

## 🔐 Sicurezza

### Protezione Script

```bash
# Solo root può eseguire
chmod 700 /root/*.sh

# Verifica permessi
ls -l /root/*.sh
```

### Protezione Log

```bash
# Solo root può leggere
chmod 600 /var/log/backup.log /var/log/monitor.log

# Rotazione log automatica
# Crea /etc/logrotate.d/danzafacile:

/var/log/backup.log /var/log/monitor.log {
    weekly
    rotate 4
    compress
    delaycompress
    missingok
    notifempty
}
```

---

## 🚨 Troubleshooting

### Email Non Ricevute

**1. Verifica mail installato**
```bash
command -v mail || apt-get install mailutils
```

**2. Test invio manuale**
```bash
echo "Test" | mail -s "Test" dds16042007@gmail.com
```

**3. Check mail log**
```bash
tail -f /var/log/mail.log
```

**4. Verifica spam folder**
Le email automatiche potrebbero finire nello spam.

### Backup Fallito

**1. Check spazio disco**
```bash
df -h /var/backups
```

**2. Check permessi**
```bash
ls -ld /var/backups/danzafacile
```

**3. Check credenziali MySQL**
```bash
cat /var/www/danzafacile/.env | grep DB_
```

**4. Test manuale mysqldump**
```bash
mysqldump -u USER -pPASSWORD DATABASE | gzip > test.sql.gz
```

### Monitor Alert Falsi

**1. Verifica soglie**
```bash
# Modifica soglie in /root/monitor.sh
# Linee ~40-50: DISK_USAGE, MEM_USAGE thresholds
```

**2. Disabilita email temporaneamente**
```bash
# In /root/monitor.sh, commenta:
# SEND_EMAIL=true
```

---

## 📝 Checklist Manutenzione

### Ogni 3 Ore (Automatica)
- [x] Backup database + files (00:00, 03:00, 06:00, 09:00, 12:00, 15:00, 18:00, 21:00)
- [x] Email report backup (8 email/giorno)
- [x] Upload automatico su Google Drive
- [x] Retention 7 giorni

### Ogni 15 Minuti (Automatica)
- [x] Health check servizi (Nginx, PHP-FPM, MySQL, Redis)
- [x] Monitor risorse (disco, RAM, CPU, website)
- [x] Email alert SOLO se problemi rilevati
- [x] 96 controlli al giorno (alta reattività)

### Settimanale (Manuale)
- [ ] Review backup logs
- [ ] Verifica spazio disco backup
- [ ] Test restore backup (sample)

### Mensile (Manuale)
- [ ] Update sistema (`/root/update-system.sh`)
- [ ] Review security updates
- [ ] Cleanup vecchi log
- [ ] Review cronjobs

### Trimestrale (Manuale)
- [ ] Full system audit
- [ ] Update dependencies
- [ ] Security audit
- [ ] Disaster recovery test

---

## 📞 Supporto

**Server Issues**:
- SSH: `ssh root@157.230.114.252`
- Email: info@danzafacile.it

**Script Issues**:
- Log: `/var/log/backup.log`, `/var/log/monitor.log`
- Scripts: `/root/backup.sh`, `/root/monitor.sh`

**Disaster Recovery**:
- Guida completa: `/deployment/DISASTER_RECOVERY.md`
- Backup location: `/var/backups/danzafacile/`
- Google Drive: `gdrive:danzafacile-backups`
- Tempo ripristino: 15-30 minuti

---

**Ultima modifica**: 2025-11-15
**Versione**: 3.0.0 - **DISASTER RECOVERY COMPLETO**
**Status**: ✅ Produzione con backup completo ogni 3 ore

## ✅ Test Eseguiti

### Test Backup COMPLETO (15/11/2025 14:44)
- ✅ Database: 27 KB (compresso)
- ✅ Files utenti: 4.0 KB
- ✅ File .env: 1.3 KB (NUOVO - configurazione completa)
- ✅ Nginx config: 1.6 KB (NUOVO - web server)
- ✅ SSL certificates: 690 B (NUOVO - HTTPS)
- ✅ Codice Laravel: 1.4 MB (NUOVO - app completa)
- ✅ Upload Google Drive: Successo (tutti i 6 file)
- ✅ Email HTML inviata a admin@danzafacile.it
- ✅ **Sistema ripristinabile in 15-30 minuti**

### Test Email Monitor (15/11/2025 14:31)
- ✅ Check servizi: TUTTI OK (Nginx, PHP-FPM, MySQL, Redis)
- ✅ Spazio disco: 22% (OK)
- ✅ RAM: 53% (OK)
- ✅ Website: HTTP 200 (OK)
- ✅ Nessuna email inviata (corretto - solo se problemi)
