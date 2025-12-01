# Deploy Checklist - Sistema Eventi Pubblici

## Panoramica Deploy

Questa checklist guida il deployment del **Sistema Eventi Pubblici** con funzionalità di:
- Registrazione Guest con Magic Link
- Integrazione PayPal per Pagamenti
- Landing Pages Personalizzabili
- QR Code Check-in
- Automazione Email
- Dashboard Admin Avanzata

---

## 1. Pre-Deploy Checks

### 1.1 Database Backup
```bash
# Backup completo database production
php artisan backup:run --only-db

# Verifica backup
ls -lh storage/app/backup/
```

### 1.2 Verifica Migrations
```bash
# Lista migrations pendenti
php artisan migrate:status

# Dry-run migrations (se disponibile)
php artisan migrate --pretend
```

**Nuove colonne da verificare:**
- `users.is_guest` (boolean)
- `users.guest_token` (string)
- `users.is_archived` (boolean)
- `events.is_public` (boolean)
- `events.landing_config` (json)
- `event_registrations.reminder_sent_at` (datetime)
- `event_registrations.thank_you_sent_at` (datetime)
- `event_payments.*` (nuova tabella)
- `gdpr_consents.*` (nuova tabella)

### 1.3 Environment Variables (.env)

**OBBLIGATORIO configurare:**

```env
# PayPal Configuration
PAYPAL_MODE=sandbox                    # o 'live' per production
PAYPAL_SANDBOX_CLIENT_ID=your_sandbox_client_id
PAYPAL_SANDBOX_CLIENT_SECRET=your_sandbox_secret
PAYPAL_LIVE_CLIENT_ID=your_live_client_id        # Per production
PAYPAL_LIVE_CLIENT_SECRET=your_live_secret       # Per production
PAYPAL_WEBHOOK_ID=your_webhook_id

# Google reCAPTCHA
RECAPTCHA_SITE_KEY=your_recaptcha_site_key
RECAPTCHA_SECRET_KEY=your_recaptcha_secret_key

# Email Configuration
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_smtp_username
MAIL_PASSWORD=your_smtp_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# App URL (IMPORTANTE per magic links)
APP_URL=https://yourdomain.com
```

### 1.4 Clear Cache
```bash
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

---

## 2. Deploy Staging

### 2.1 Deploy Code
```bash
# Pull latest code
git fetch origin
git checkout main
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

### 2.2 Database Migration
```bash
# Esegui migrations
php artisan migrate

# Verifica tabelle create
php artisan db:show
```

### 2.3 Cache Optimization
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 2.4 Test End-to-End Staging

**Test Checklist:**

- [ ] Registrazione Guest a evento gratuito
- [ ] Registrazione Guest a evento a pagamento
- [ ] Ricezione email con Magic Link
- [ ] Login via Magic Link
- [ ] Visualizzazione My Events guest
- [ ] Pagamento PayPal Sandbox
- [ ] Webhook PayPal ricezione
- [ ] Conferma pagamento e aggiornamento status
- [ ] QR Code Scanner funzionante
- [ ] Admin Dashboard caricamento stats
- [ ] Export CSV guest registrations
- [ ] Personalizzazione Landing Page evento

**Test con Sandbox PayPal:**
1. Crea account test: https://developer.paypal.com/dashboard/accounts
2. Usa credenziali test per pagamento
3. Verifica webhook in PayPal Developer Dashboard

---

## 3. Deploy Production

### 3.1 Pre-Production Checklist

- [ ] **Backup database production completo**
- [ ] Tag release Git: `git tag v1.0-eventi-pubblici`
- [ ] Push tag: `git push origin v1.0-eventi-pubblici`
- [ ] Notifica team di deploy imminente
- [ ] Verifica window di manutenzione (se necessario)

### 3.2 Deploy Steps

```bash
# 1. Pull code
git fetch origin
git checkout v1.0-eventi-pubblici

# 2. Dependencies
composer install --no-dev --optimize-autoloader
npm install --production
npm run build

# 3. Maintenance Mode
php artisan down --message="Aggiornamento sistema in corso" --retry=60

# 4. Database Migration
php artisan migrate --force

# 5. Clear & Cache
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. File Permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 7. Up
php artisan up
```

### 3.3 Scheduler Setup (Cron Job)

Aggiungere a crontab del server:

```bash
# Apri crontab
crontab -e

# Aggiungi questa riga (sostituisci /path/to/app)
* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
```

**Verifica scheduler attivo:**
```bash
php artisan schedule:list
```

**Scheduled Tasks attivi:**
- `SendEventReminders` (daily 08:00) - Invia promemoria eventi
- `SendThankYouEmails` (daily 10:00) - Invia email ringraziamento post-evento
- `CleanupExpiredGuests` (weekly) - Pulizia guest account scaduti

### 3.4 PayPal Production Setup

**IMPORTANTE: Passaggio da Sandbox a Live**

1. **PayPal Developer Dashboard:**
   - Vai su https://developer.paypal.com/dashboard/applications
   - Crea App "Live" (non sandbox)
   - Copia Client ID e Secret **LIVE**

2. **Webhook Configuration:**
   ```
   Webhook URL: https://yourdomain.com/api/payments/webhook

   Eventi da ascoltare:
   - PAYMENT.SALE.COMPLETED
   - PAYMENT.SALE.DENIED
   - PAYMENT.SALE.REFUNDED
   ```

3. **Update .env Production:**
   ```env
   PAYPAL_MODE=live
   PAYPAL_LIVE_CLIENT_ID=your_production_client_id
   PAYPAL_LIVE_CLIENT_SECRET=your_production_secret
   PAYPAL_WEBHOOK_ID=your_webhook_id
   ```

4. **Test Pagamento Reale:**
   - Usa carta di credito reale (importo minimo)
   - Verifica ricezione webhook
   - Verifica aggiornamento database
   - **IMPORTANTE:** Rimborsa transazione test

---

## 4. Post-Deploy Verification

### 4.1 Smoke Tests Production

**Test Critici (5-10 minuti):**

- [ ] Homepage carica correttamente
- [ ] Admin Login funziona
- [ ] Admin Dashboard carica stats
- [ ] Eventi pubblici visibili su `/events`
- [ ] Form registrazione guest funziona (NO pagamento reale)
- [ ] Email magic link ricevuta
- [ ] Login magic link funziona
- [ ] QR Scanner accessibile

### 4.2 Monitoring Logs

```bash
# Tail logs in tempo reale
tail -f storage/logs/laravel.log

# Cerca errori
grep -i "error\|exception" storage/logs/laravel-$(date +%Y-%m-%d).log
```

### 4.3 Verifica Email Delivery

```bash
# Test email manuale
php artisan tinker

# In tinker:
Mail::raw('Test email sistema eventi', function($msg) {
    $msg->to('your@email.com')->subject('Test Deploy');
});
```

### 4.4 Database Health Check

```bash
# Verifica nuove tabelle
php artisan db:table event_payments
php artisan db:table gdpr_consents

# Verifica indici
php artisan db:show
```

---

## 5. Monitoring & Alerts

### 5.1 Setup Error Monitoring

**Opzione 1: Laravel Telescope (già installato)**
```bash
php artisan telescope:install
php artisan migrate
```
Accesso: `https://yourdomain.com/telescope`

**Opzione 2: Sentry (consigliato per production)**
```bash
composer require sentry/sentry-laravel
php artisan sentry:publish --dsn=your_sentry_dsn
```

### 5.2 Email Failure Alerts

Configurare notifiche per:
- Email bounce rate alto
- Queue jobs failed
- PayPal webhook failures

### 5.3 Key Metrics to Monitor

- **Registrazioni Guest:** `event_registrations.count()`
- **Pagamenti Pending:** `event_payments.where('status', 'pending').count()`
- **Email Failures:** `failed_jobs` table
- **PayPal Webhook Logs:** `storage/logs/paypal-webhook.log`

---

## 6. Rollback Plan

### 6.1 Se problemi critici post-deploy

```bash
# 1. Maintenance mode
php artisan down

# 2. Restore database backup
mysql -u user -p database_name < backup_file.sql

# 3. Rollback migrations (se necessario)
php artisan migrate:rollback --step=1

# 4. Deploy versione precedente
git checkout <previous-tag>
composer install --no-dev --optimize-autoloader
php artisan optimize:clear
php artisan config:cache

# 5. Up
php artisan up
```

### 6.2 Rollback Parziale

Se solo alcune features hanno problemi:

```bash
# Disabilita eventi pubblici temporaneamente
php artisan tinker

# In tinker:
Event::where('is_public', true)->update(['active' => false]);
```

---

## 7. Documentation & Support

### 7.1 Admin Help Resources

- **Documentazione Admin:** `/admin/help` (se implementato)
- **Video Tutorial:** Link a tutorial registrazione guest
- **FAQ:** Domande frequenti sistema eventi

### 7.2 Technical Support

- **Report Issues:** GitHub Issues repository
- **PayPal Support:** https://developer.paypal.com/support
- **Laravel Logs:** `storage/logs/laravel.log`

### 7.3 User Support

**Per problemi guest comuni:**
- Magic link scaduto → Reinviare da My Events
- Pagamento non confermato → Verificare email PayPal
- Non ricevo email → Verificare spam/junk folder

---

## 8. Performance Optimization (Post-Deploy)

### 8.1 Database Optimization

```bash
# Ottimizza tabelle
php artisan db:optimize

# Analizza query lente
php artisan telescope:prune --hours=48
```

### 8.2 Caching Strategy

```bash
# Cache query frequenti
php artisan cache:table
php artisan migrate

# Cache eventi pubblici (esempio)
php artisan tinker
Cache::remember('public_events', 3600, fn() => Event::public()->get());
```

### 8.3 Queue Workers (se alto traffico)

```bash
# Setup supervisor per queue workers
sudo supervisorctl start laravel-worker:*

# Verifica queue
php artisan queue:work --verbose
```

---

## 9. Security Checklist

### 9.1 Pre-Production Security

- [ ] `.env` file protected (NOT in public directory)
- [ ] HTTPS enabled (SSL certificate valid)
- [ ] CSRF protection enabled
- [ ] Rate limiting su public routes
- [ ] reCAPTCHA funzionante
- [ ] PayPal webhook signature verification enabled
- [ ] SQL injection prevention (Eloquent ORM)
- [ ] XSS protection (Blade escaping)

### 9.2 Post-Deploy Security Audit

```bash
# Verifica permessi files
ls -la storage/
ls -la bootstrap/cache/

# Test rate limiting
ab -n 100 -c 10 https://yourdomain.com/events

# Test HTTPS redirect
curl -I http://yourdomain.com
```

---

## 10. Success Criteria

### Deploy considerato SUCCESSO se:

- [ ] Zero errori in logs dopo 1 ora
- [ ] Tutte le smoke tests passano
- [ ] Scheduler attivo e funzionante
- [ ] Email inviate correttamente
- [ ] PayPal webhook ricevuti (se test effettuato)
- [ ] Performance response time < 500ms
- [ ] Admin dashboard accessibile
- [ ] Guest registration funzionante
- [ ] Zero downtime critico

---

## 11. Next Steps Post-Deploy

1. **Monitorare per 24h:** Logs, performance, email delivery
2. **Comunicare agli Admin:** Nuove funzionalità disponibili
3. **Marketing:** Promuovere eventi pubblici
4. **Raccogliere Feedback:** Prima settimana di utilizzo
5. **Pianificare Iterazioni:** Miglioramenti basati su feedback

---

## Contatti Emergenza Deploy

- **Tech Lead:** [Nome] - [Email/Phone]
- **DevOps:** [Nome] - [Email/Phone]
- **Database Admin:** [Nome] - [Email/Phone]

---

**Ultima revisione:** 2025-12-01
**Versione:** 1.0 - Sistema Eventi Pubblici
**Status:** Production Ready ✅
