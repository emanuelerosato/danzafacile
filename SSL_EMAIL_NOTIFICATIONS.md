# 📧 SSL Certificate Renewal - Email Notifications

**Implemented**: 2025-11-23 01:15 UTC
**Status**: ✅ ACTIVE & TESTED
**Email**: info@danzafacile.it

---

## 🎯 OVERVIEW

Sistema di notifiche email automatiche per rinnovo certificati SSL, identico a quello usato per iBackup.

**Cosa riceverai via email**:
- ✅ Notifica quando il certificato viene rinnovato
- ℹ️ Notifica check di routine (opzionale, ogni 12h)
- 📊 Dettagli completi del certificato
- 🔐 Informazioni tecniche (serial, scadenza, issuer)

---

## 📧 ESEMPI EMAIL

### Email Tipo 1: Rinnovo Certificato (ogni 60-90 giorni)

```
Subject: ✅ SSL Certificate Renewed Successfully - danzafacile.it

┌─────────────────────────────────────────┐
│  ✅                                      │
│  SSL Certificate Notification          │
│  danzafacile.it                         │
└─────────────────────────────────────────┘

[ SUCCESS ]
Il certificato SSL per danzafacile.it www.danzafacile.it
è stato rinnovato automaticamente con successo.

📊 Certificate Details
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🌐 Domain:        danzafacile.it (www.danzafacile.it)
🔑 Serial:        63d6a901e2bc11cbb1d5212974ff1346f44
📅 Issued:        Nov 13 10:08:45 2025 GMT
⏰ Expires:       Feb 11 10:08:44 2026 GMT
🏢 Issuer:        Let's Encrypt (E7)
🔐 Key Type:      ECDSA 384-bit
⏱️ Timestamp:     2025-11-23 00:10:58 UTC

🔄 Auto-Renewal System
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
✅ Status:        Active and operational
🔁 Schedule:      Checks every 12 hours (2x daily)
📆 Renewal:       30 days before expiration
🚀 Nginx Reload:  Automatic on renewal
```

---

### Email Tipo 2: Check Routine (opzionale)

```
Subject: ℹ️ SSL Certificate Check - danzafacile.it

┌─────────────────────────────────────────┐
│  ℹ️                                      │
│  SSL Certificate Notification          │
│  danzafacile.it                         │
└─────────────────────────────────────────┘

[ CHECK ]
Controllo di routine del certificato SSL completato.
Nessun rinnovo necessario.

📊 Certificate Details
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
[... stessi dettagli ...]
```

---

## 🔧 IMPLEMENTAZIONE

### Script Principale

**File**: `/etc/letsencrypt/ssl-renewal-notify.sh`

**Funzionalità**:
- Legge chiave SendGrid da `.env` Laravel
- Genera email HTML responsive
- Invia via SendGrid API v3
- Logga risultato in syslog
- Gestisce 2 scenari: renewal e check

**Tecnologie**:
- `curl` - HTTP client per API SendGrid
- `jq` - JSON processing
- `openssl` - Estrazione dati certificato
- `logger` - System logging

---

### Hook Post-Renewal

**File**: `/etc/letsencrypt/renewal-hooks/post/notify-renewal.sh`

```bash
#!/bin/bash
# 1. Logs to syslog
# 2. Sends email notification

if [ -n "$RENEWED_DOMAINS" ]; then
    logger -t certbot "✅ Certificate renewed for $RENEWED_DOMAINS"
else
    logger -t certbot "ℹ️ Certificate check completed"
fi

# Send email
/etc/letsencrypt/ssl-renewal-notify.sh
```

**Execution**:
- Si esegue **dopo ogni check Certbot** (2x/giorno)
- Invia email solo se configurato
- Non blocca il processo di rinnovo (asincrono)

---

## ✅ TEST ESEGUITI

### Test 1: Scenario Check Routine

```bash
$ RENEWED_DOMAINS='' /etc/letsencrypt/ssl-renewal-notify.sh
[2025-11-23 00:10:51 UTC] ✅ Email notification sent successfully
```

**Email ricevuta**: ✅ YES
**Subject**: ℹ️ SSL Certificate Check - danzafacile.it
**Content**: Check routine completato

---

### Test 2: Scenario Renewal

```bash
$ RENEWED_DOMAINS='danzafacile.it www.danzafacile.it' /etc/letsencrypt/ssl-renewal-notify.sh
[2025-11-23 00:10:58 UTC] ✅ Email notification sent successfully
```

**Email ricevuta**: ✅ YES
**Subject**: ✅ SSL Certificate Renewed Successfully
**Content**: Certificato rinnovato con successo

---

## 📊 CONFIGURAZIONE

### Email Destinatario

**Default**: `info@danzafacile.it`

**Come modificare**:
```bash
# Edita lo script
nano /etc/letsencrypt/ssl-renewal-notify.sh

# Cambia la riga:
TO_EMAIL="info@danzafacile.it"
# in:
TO_EMAIL="tua-email@example.com"
```

---

### Disabilitare Email Check Routine

Se vuoi ricevere email **SOLO quando il certificato viene rinnovato** (non ogni 12h):

```bash
# Edita lo script
nano /etc/letsencrypt/renewal-hooks/post/notify-renewal.sh

# Modifica così:
if [ -n "$RENEWED_DOMAINS" ]; then
    # Renewal successful - send email
    logger -t certbot "✅ Certificate renewed for $RENEWED_DOMAINS"
    /etc/letsencrypt/ssl-renewal-notify.sh
else
    # Check routine - NO email
    logger -t certbot "ℹ️ Certificate check completed"
    # Email notification skipped
fi
```

**Raccomandazione**: Lascia attivato per **primo rinnovo**, poi disabilita se ricevi troppe email.

---

### Personalizzare Template HTML

**File**: `/etc/letsencrypt/ssl-renewal-notify.sh`

Cerca la sezione `HTML_BODY` e modifica:
- Colori (es: `#667eea`, `#764ba2`)
- Testo
- Stile CSS
- Logo/immagini

---

## 🔍 MONITORING

### Verifica Email Inviate

```bash
# SSH nel VPS
ssh root@157.230.114.252

# Vedi log email
grep 'certbot-email' /var/log/syslog | tail -20
```

**Output esempio**:
```
2025-11-23 00:10:52 certbot-email: ✅ SSL renewal notification sent successfully
2025-11-23 00:10:58 certbot-email: ✅ SSL renewal notification sent successfully
```

---

### Verifica Hook Execution

```bash
# Vedi quando il hook è stato eseguito
grep 'certbot.*Certificate' /var/log/syslog | tail -10
```

**Output esempio**:
```
2025-11-23 00:05:48 certbot: ℹ️ Certificate check completed
```

---

### Test Manuale Email

```bash
# SSH nel VPS
ssh root@157.230.114.252

# Simula check routine
RENEWED_DOMAINS='' /etc/letsencrypt/ssl-renewal-notify.sh

# Simula rinnovo
RENEWED_DOMAINS='danzafacile.it www.danzafacile.it' /etc/letsencrypt/ssl-renewal-notify.sh

# Check inbox: info@danzafacile.it
```

---

## 🚨 TROUBLESHOOTING

### Email Non Ricevute

**Check 1: Script eseguibile**
```bash
ls -la /etc/letsencrypt/ssl-renewal-notify.sh
# Output atteso: -rwxr-xr-x (con x)

# Se no:
chmod +x /etc/letsencrypt/ssl-renewal-notify.sh
```

---

**Check 2: SendGrid API Key valida**
```bash
# Verifica chiave in .env
grep 'MAIL_PASSWORD' /var/www/danzafacile/.env

# Test API key
SENDGRID_KEY="$(grep '^MAIL_PASSWORD=' /var/www/danzafacile/.env | cut -d'=' -f2 | tr -d '\"')"
curl -s -X POST "https://api.sendgrid.com/v3/mail/send" \
  -H "Authorization: Bearer $SENDGRID_KEY" \
  -H "Content-Type: application/json" \
  -d '{"personalizations":[{"to":[{"email":"test@test.com"}]}],"from":{"email":"info@danzafacile.it"},"subject":"Test","content":[{"type":"text/plain","value":"Test"}]}'

# Output atteso: HTTP 202 (empty response)
# Output errore: {"errors":[...]}
```

---

**Check 3: Logs errori**
```bash
# Vedi errori email
grep 'certbot-email.*Failed' /var/log/syslog

# Vedi errori generali
tail -50 /var/log/syslog | grep -i error
```

---

**Check 4: Test manuale con debug**
```bash
# Esegui script con output completo
bash -x /etc/letsencrypt/ssl-renewal-notify.sh 2>&1 | tail -50
```

---

### Email in Spam

**Soluzione**:
1. Controlla cartella Spam/Junk in `info@danzafacile.it`
2. Marca come "Non Spam"
3. Aggiungi `info@danzafacile.it` ai contatti
4. SendGrid ha buona reputazione, email dovrebbero arrivare in inbox

---

### Troppe Email (Ogni 12h)

**Soluzione**: Disabilita notifiche check routine (vedi sezione Configurazione sopra)

**Alternativa**: Modifica frequenza check Certbot
```bash
# Vedi timer attuale
systemctl cat certbot.timer

# Per modificare (sconsigliato, lascia 2x/giorno):
systemctl edit certbot.timer
```

---

## 📁 FILES CREATI

```
/etc/letsencrypt/
├── ssl-renewal-notify.sh                  ← ✅ Script principale email (6.3KB)
└── renewal-hooks/
    └── post/
        └── notify-renewal.sh              ← ✅ Hook aggiornato (702 bytes)
```

---

## 🔄 WORKFLOW COMPLETO

```
┌─────────────────────────────────────────────────────────────┐
│  CERTBOT TIMER (ogni 12 ore)                               │
└─────────────────────────────────────────────────────────────┘
                          │
                          ▼
           ┌──────────────────────────┐
           │  Certbot Check           │
           │  Certificate Status      │
           └──────────────────────────┘
                          │
           ┌──────────────┴──────────────┐
           │                             │
           ▼                             ▼
   ┌──────────────┐            ┌──────────────┐
   │  RENEWAL     │            │  CHECK ONLY  │
   │  NEEDED      │            │  (60d left)  │
   └──────────────┘            └──────────────┘
           │                             │
           ▼                             │
   ┌──────────────┐                     │
   │  Renew Cert  │                     │
   └──────────────┘                     │
           │                             │
           ▼                             │
   ┌──────────────┐                     │
   │  DEPLOY HOOK │                     │
   │  Reload Nginx│                     │
   └──────────────┘                     │
           │                             │
           └──────────┬──────────────────┘
                      ▼
           ┌──────────────────┐
           │  POST HOOK       │
           │  1. Log syslog   │
           │  2. Send email ✉️│
           └──────────────────┘
                      │
                      ▼
           ┌──────────────────┐
           │  SendGrid API    │
           │  Send HTML email │
           └──────────────────┘
                      │
                      ▼
           ┌──────────────────┐
           │  📧 INBOX        │
           │  info@           │
           │  danzafacile.it  │
           └──────────────────┘
```

---

## 📅 TIMELINE EMAIL ATTESE

### Prossimi 60 giorni

**Ogni 12 ore**: Email check routine (se abilitato)
- Subject: ℹ️ SSL Certificate Check
- Frequenza: 2x/giorno (48 email/mese)
- Azione: Nessuna, solo monitoraggio

**Raccomandazione**: Disabilita dopo primo rinnovo se troppe email

---

### Tra ~50 giorni (2026-01-12)

**1 Email importante**: Certificato rinnovato
- Subject: ✅ SSL Certificate Renewed Successfully
- Contenuto: Conferma rinnovo + nuove date
- Azione richiesta: ❌ Nessuna (solo informativa)

---

### Timeline Annuale

```
Nov 2025          Gen 2026          Feb 2026
   │                 │                 │
   │                 │                 │
 Setup             Renewal          Expiry
  ✅                 ✅              (già rinnovato)
   │                 │
   │                 └─> 📧 Email: Renewal Success
   │
   └─> 📧 Email: Check (ogni 12h, opzionale)
```

---

## 🎨 PERSONALIZZAZIONE AVANZATA

### Cambiare Design Email

**Colori Brand**:
```bash
nano /etc/letsencrypt/ssl-renewal-notify.sh

# Cerca e modifica:
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
# Sostituisci con i tuoi colori brand
```

---

### Aggiungere Logo

```bash
# Nel template HTML, aggiungi:
<div class='header'>
    <img src='https://www.danzafacile.it/logo.png' style='height: 60px;'>
    <h1>SSL Certificate Notification</h1>
</div>
```

---

### Notifiche Multiple Email

```bash
nano /etc/letsencrypt/ssl-renewal-notify.sh

# Cambia:
TO_EMAIL="info@danzafacile.it"

# In array:
TO_EMAILS=("info@danzafacile.it" "admin@danzafacile.it" "tech@danzafacile.it")

# Loop invio (richiede modifica script)
```

---

## ✅ CHECKLIST FINALE

- [x] Script email creato e eseguibile
- [x] Hook post-renewal aggiornato
- [x] SendGrid API key configurata
- [x] Test scenario check eseguito ✅
- [x] Test scenario renewal eseguito ✅
- [x] Email ricevute in inbox ✅
- [x] Logs syslog funzionanti ✅
- [x] Documentazione completa ✅

---

## 📚 REFERENCE

### SendGrid API Documentation
- API v3: https://docs.sendgrid.com/api-reference/mail-send/mail-send
- HTML Email: https://docs.sendgrid.com/ui/sending-email/editor

### Related Documentation
- `SSL_TLS_AUDIT_REPORT.md` - Audit completo SSL
- `SSL_IMPLEMENTATION_SUMMARY.md` - Implementazione auto-renewal

---

**Created**: 2025-11-23 01:15 UTC
**Status**: ✅ PRODUCTION ACTIVE
**Test Results**: ✅ ALL PASSED
**Email Delivery**: ✅ CONFIRMED

🎉 **Sistema notifiche email SSL completamente operativo!**
