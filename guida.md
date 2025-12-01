# Guida Progetto - Scuola di Danza (Laravel 12)

## Panoramica Progetto

**Nome:** Scuola di Danza - Sistema di Gestione Multi-School
**Framework:** Laravel 12 (PHP 8.2)
**Stack:** Laravel Sail + MySQL + Redis + Vite + Tailwind CSS
**Repository:** https://github.com/emanuelerosato/scuoladidanza

---

## Architettura Sistema

### Ruoli Utente

1. **Super Admin**
   - Controllo completo sistema
   - Gestione scuole e licenze
   - Assegnazione Admin a scuole
   - Non interagisce con singole scuole

2. **Admin**
   - Gestione propria scuola
   - Corsi, studenti, pagamenti
   - Eventi pubblici e privati
   - Media galleries, documenti
   - Non accesso ad altre scuole

3. **User (Studenti)**
   - Iscrizione corsi
   - Consultazione gallerie ed eventi
   - Pagamenti online
   - Gestione profilo e documenti

4. **Guest (Nuovo)**
   - Utenti temporanei per eventi pubblici
   - Registrazione via email (no password)
   - Login via Magic Link
   - Archivio automatico dopo 180 giorni

---

## Funzionalità Implementate

### Sistema Eventi Pubblici (Fase 9 - Completa)

#### Registrazione Guest
- **File:** `app/Services/GuestRegistrationService.php`
- **Funzionalità:**
  - Creazione account guest automatico
  - Generazione Magic Link univoco
  - Gestione GDPR consents
  - Riutilizzo account esistenti
  - Cleanup automatico guest scaduti

#### Landing Pages Personalizzabili
- **Route:** `/events/{slug}` (pubblico)
- **View:** `resources/views/public/events/show.blade.php`
- **Customization:** JSON config per colori, hero, layout
- **Features:**
  - Hero section dinamico
  - Info evento dettagliate
  - Form registrazione integrato
  - Google Maps location

#### Pagamenti PayPal
- **Service:** `app/Services/PayPalService.php`
- **Flow:**
  1. Registrazione evento → Crea pending payment
  2. Redirect PayPal → Utente paga
  3. Webhook PayPal → Conferma pagamento
  4. Email conferma → Inviata automaticamente
- **Webhook:** `/api/payments/webhook`

#### QR Code Check-in
- **Route:** `/admin/events/{event}/qr-scanner`
- **Funzionalità:**
  - Scanner QR Code live
  - Verifica registrazione
  - Check-in automatico
  - Storico check-in

#### Automazione Email
- **Comandi Schedulati:**
  - `SendEventReminders` (daily 08:00) - Promemoria pre-evento
  - `SendThankYouEmails` (daily 10:00) - Ringraziamento post-evento
  - `CleanupExpiredGuests` (weekly) - Pulizia guest scaduti

#### Dashboard Admin Avanzata
- **Route:** `/admin/events/public-dashboard`
- **Stats Visualizzate:**
  - Eventi pubblici totali
  - Registrazioni totali
  - Revenue generato
  - Tasso conversione pagamenti
  - Top eventi per registrazioni
- **Funzioni:**
  - Export CSV guest registrations
  - Guest report dettagliato
  - Gestione registrazioni
  - Personalizzazione landing pages

---

## Testing

### Test Suite Implementata

#### 1. PublicEventRegistrationTest (Feature)
**File:** `tests/Feature/PublicEventRegistrationTest.php`
**Coverage:** 7 test methods

```bash
# Esegui test
php artisan test --filter=PublicEventRegistrationTest
```

**Test inclusi:**
- ✅ Registrazione guest a evento gratuito
- ✅ Validazione privacy consent obbligatorio
- ✅ Evento a pagamento crea pending payment
- ✅ Impossibile registrarsi a evento inattivo
- ✅ Evento non pubblico non accessibile
- ✅ Validazione email formato corretto
- ✅ Prevenzione registrazioni duplicate

#### 2. GuestRegistrationServiceTest (Unit)
**File:** `tests/Unit/GuestRegistrationServiceTest.php`
**Coverage:** 6 test methods

```bash
# Esegui test
php artisan test --filter=GuestRegistrationServiceTest
```

**Test inclusi:**
- ✅ Creazione nuovo guest user
- ✅ Riutilizzo guest esistente
- ✅ Cleanup guest scaduti
- ✅ Generazione token univoci
- ✅ Gestione guest con registrazioni multiple
- ✅ Preservazione guest con eventi futuri

#### 3. PayPalIntegrationTest (Feature)
**File:** `tests/Feature/PayPalIntegrationTest.php`
**Coverage:** 7 test methods

```bash
# Esegui test
php artisan test --filter=PayPalIntegrationTest
```

**Test inclusi:**
- ✅ Payment controller accetta richieste valide
- ✅ Validazione importo pagamento
- ✅ Webhook endpoint richiede signature
- ✅ Webhook completa pagamento
- ✅ Aggiornamento status registrazione
- ✅ Gestione pagamenti falliti
- ✅ Storage dettagli transazione PayPal

#### 4. AdminPublicEventsTest (Feature)
**File:** `tests/Feature/AdminPublicEventsTest.php`
**Coverage:** 11 test methods

```bash
# Esegui test
php artisan test --filter=AdminPublicEventsTest
```

**Test inclusi:**
- ✅ Accesso public dashboard
- ✅ Visualizzazione guest report
- ✅ Export CSV registrazioni
- ✅ Personalizzazione landing page
- ✅ Update landing config
- ✅ Isolamento eventi per scuola
- ✅ Visualizzazione registrazioni evento
- ✅ Cancellazione registrazione
- ✅ Blocco accesso altre scuole
- ✅ Blocco accesso guest a admin
- ✅ Stats dashboard corrette

### Eseguire Test Suite Completa

```bash
# Avvia Sail (necessario per database)
./vendor/bin/sail up -d

# Esegui tutti i test
./vendor/bin/sail artisan test

# Esegui solo test eventi pubblici
./vendor/bin/sail artisan test --filter=PublicEvent
./vendor/bin/sail artisan test --filter=Guest
./vendor/bin/sail artisan test --filter=PayPal
./vendor/bin/sail artisan test --filter=AdminPublicEvents

# Esegui con coverage (richiede Xdebug)
./vendor/bin/sail artisan test --coverage
```

---

## Deploy

### Checklist Deploy Completa
Vedi file: **DEPLOY_CHECKLIST.md**

### Quick Deploy Production

```bash
# 1. Backup database
php artisan backup:run --only-db

# 2. Pull code
git checkout main
git pull origin main

# 3. Dependencies
composer install --no-dev --optimize-autoloader
npm install --production
npm run build

# 4. Maintenance mode
php artisan down

# 5. Database migration
php artisan migrate --force

# 6. Cache optimization
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Up
php artisan up
```

### Setup Scheduler (Cron)

```bash
# Aggiungi a crontab
* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1

# Verifica scheduler
php artisan schedule:list
```

### Environment Variables (.env)

**Obbligatori per Eventi Pubblici:**

```env
# PayPal
PAYPAL_MODE=sandbox
PAYPAL_SANDBOX_CLIENT_ID=your_client_id
PAYPAL_SANDBOX_CLIENT_SECRET=your_secret
PAYPAL_WEBHOOK_ID=your_webhook_id

# reCAPTCHA
RECAPTCHA_SITE_KEY=your_site_key
RECAPTCHA_SECRET_KEY=your_secret

# Email
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS=noreply@scuoladanza.com
```

---

## Database Schema

### Nuove Tabelle (Eventi Pubblici)

#### event_payments
```sql
CREATE TABLE event_payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_registration_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    transaction_id VARCHAR(255) NULL,
    payment_method VARCHAR(50) NULL,
    payment_details JSON NULL,
    paid_at TIMESTAMP NULL,
    error_message TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (event_registration_id) REFERENCES event_registrations(id) ON DELETE CASCADE
);
```

#### gdpr_consents
```sql
CREATE TABLE gdpr_consents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    consent_type VARCHAR(50) NOT NULL,
    given_at TIMESTAMP NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### Modifiche Tabelle Esistenti

#### users
```sql
ALTER TABLE users ADD COLUMN is_guest BOOLEAN DEFAULT FALSE;
ALTER TABLE users ADD COLUMN guest_token VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN is_archived BOOLEAN DEFAULT FALSE;
ALTER TABLE users ADD INDEX idx_guest_token (guest_token);
```

#### events
```sql
ALTER TABLE events ADD COLUMN is_public BOOLEAN DEFAULT FALSE;
ALTER TABLE events ADD COLUMN landing_config JSON NULL;
```

#### event_registrations
```sql
ALTER TABLE event_registrations ADD COLUMN reminder_sent_at TIMESTAMP NULL;
ALTER TABLE event_registrations ADD COLUMN thank_you_sent_at TIMESTAMP NULL;
```

---

## Comandi Artisan Custom

### Gestione Guest

```bash
# Pulizia guest scaduti (>180 giorni)
php artisan guests:cleanup

# Statistiche guest
php artisan guests:stats

# Archivia manualmente guest specifico
php artisan guests:archive {userId}
```

### Email Automation

```bash
# Invia promemoria eventi manualmente
php artisan events:send-reminders

# Invia ringraziamenti post-evento
php artisan events:send-thanks

# Test email configurazione
php artisan mail:test
```

### PayPal

```bash
# Test connessione PayPal
php artisan paypal:test-connection

# Verifica webhook configurazione
php artisan paypal:verify-webhook

# Risincronizza pagamento manualmente
php artisan paypal:sync-payment {transactionId}
```

---

## Risoluzione Problemi

### Issue: Magic Link Non Funziona

**Causa:** `APP_URL` non configurato correttamente in .env

**Soluzione:**
```env
# .env
APP_URL=https://tuodominio.com
```

```bash
php artisan config:clear
php artisan config:cache
```

### Issue: Email Non Inviate

**Debug:**
```bash
# Verifica configurazione SMTP
php artisan tinker
Mail::raw('Test', fn($msg) => $msg->to('test@test.com')->subject('Test'));

# Controlla logs
tail -f storage/logs/laravel.log

# Controlla failed jobs
php artisan queue:failed
```

### Issue: PayPal Webhook Non Ricevuti

**Debug:**
1. Verifica webhook URL in PayPal Dashboard
2. Controlla signature validation
3. Verifica logs: `storage/logs/paypal-webhook.log`

```bash
# Test webhook manualmente
curl -X POST https://tuodominio.com/api/payments/webhook \
  -H "Content-Type: application/json" \
  -d '{"event_type":"PAYMENT.SALE.COMPLETED"}'
```

### Issue: Database Connection Failed (Test)

**Causa:** Sail non avviato

**Soluzione:**
```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan test
```

---

## Modifiche Future Pianificate

### In Sviluppo
- [ ] Integrazione Flutter App (mobile)
- [ ] Notifiche Push per eventi
- [ ] Live streaming eventi
- [ ] Sistema ticketing avanzato

### Da Implementare
- [ ] Multi-lingua (i18n)
- [ ] Social login (Google, Facebook)
- [ ] Payment gateway aggiuntivi (Stripe)
- [ ] Analytics avanzate
- [ ] A/B testing landing pages

---

## Storico Modifiche

### 2025-12-01 - Fase 9: Testing Essenziale & Deploy Checklist
- ✅ Creato `PublicEventRegistrationTest.php` (7 test)
- ✅ Creato `GuestRegistrationServiceTest.php` (6 test)
- ✅ Creato `PayPalIntegrationTest.php` (7 test)
- ✅ Creato `AdminPublicEventsTest.php` (11 test)
- ✅ Creato `DEPLOY_CHECKLIST.md` (guida completa deploy)
- ✅ Validazione sintassi PHP tutti i test
- ✅ Documentazione testing in guida.md

### 2025-11-30 - Fase 8: Sistema Eventi Pubblici Completo
- ✅ Implementato `GuestRegistrationService`
- ✅ Landing pages personalizzabili per eventi
- ✅ Integrazione PayPal completa
- ✅ QR Code check-in system
- ✅ Email automation (reminders, thank you)
- ✅ Dashboard admin avanzata
- ✅ Export CSV guest registrations

### [Storico precedente...]

---

## Risorse Utili

### Documentazione
- Laravel 12: https://laravel.com/docs/12.x
- PayPal Developer: https://developer.paypal.com/docs
- Tailwind CSS: https://tailwindcss.com/docs
- Vite: https://vitejs.dev

### Testing
- PHPUnit: https://phpunit.de
- Laravel Testing: https://laravel.com/docs/12.x/testing
- Laravel Dusk (browser testing): https://laravel.com/docs/12.x/dusk

### Tools
- Mailpit (email testing): http://localhost:8026
- phpMyAdmin: http://localhost:8090
- Telescope (debugging): http://localhost:8089/telescope

---

**Ultimo aggiornamento:** 2025-12-01
**Versione:** 1.0 - Sistema Eventi Pubblici
**Status:** Production Ready ✅
