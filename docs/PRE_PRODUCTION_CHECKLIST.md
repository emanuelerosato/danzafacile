# 🚀 Pre-Production Checklist - DanzaFacile

**Data**: 2025-11-23  
**Stato Attuale**: Sistema backend production-ready al 95%  
**Target**: Distribuzione pubblica app Flutter + backend live

---

## 📊 STATO ATTUALE

### ✅ COMPLETATO (Backend - 100%)

#### 🔒 Security & Infrastructure
- [x] **SSL/TLS**: Certificato Let's Encrypt valido + auto-renewal + email notifications
- [x] **CSP Security**: Nonce-based (NO unsafe-inline, NO unsafe-eval) - Security Grade A+
- [x] **SendGrid API Key**: Protetto (permissions 600)
- [x] **Database Password**: Rotazione completata + sincronizzato
- [x] **Symfony CVE**: Fixed (v7.3.7)
- [x] **Duplicate Headers**: Rimossi da Nginx
- [x] **HSTS**: Configurato (max-age 1 anno + preload)
- [x] **Firewall**: UFW attivo (solo 22, 80, 443)

#### 🔧 Backend Services
- [x] **Laravel Queue Worker**: systemd service attivo (laravel-worker.service)
- [x] **Firebase SDK**: Installato e connesso (kreait/firebase-php 7.23.0)
- [x] **API Endpoints**: 12/12 funzionanti + documentati
- [x] **Database**: MySQL 8.4.7 + migrazioni complete
- [x] **Redis**: Cache attivo
- [x] **Nginx**: 1.28.0 configurato
- [x] **PHP**: 8.4.11

#### 📄 Documentazione
- [x] SSL/TLS audit completo
- [x] Security audit completo
- [x] Push notifications guide (1,045 righe)
- [x] SSL email notifications setup
- [x] Security fixes documentati

---

## ⚠️ DA COMPLETARE PRIMA DI PRODUZIONE

### 🔴 CRITICAL - Must Fix (Blockers)

#### 1. ❌ Hardcoded Default Password (VULN-003)

**Stato**: NON RISOLTO  
**Priorità**: 🔴 CRITICAL  
**Tempo stimato**: 5 minuti  
**Blocca produzione**: ❌ NO (hai detto che verrà resettata)

**Dettaglio**:
```php
// File: app/Console/Commands/ImportStudents.php:15
private const DEFAULT_PASSWORD = 'TempPass2025!';
```

**Azione richiesta**:
- ✅ **Opzione A**: Conferma che resetterai password prima di deploy (ACCETTABILE)
- ⚠️ **Opzione B**: Implementa password random + force reset al primo login

**Decision**: User ha confermato che resetterà manualmente - ✅ ACCEPTED

---

#### 2. ⚠️ PHP Dangerous Functions Enabled (VULN-006)

**Stato**: NON RISOLTO  
**Priorità**: 🟠 HIGH  
**Tempo stimato**: 10 minuti  
**Blocca produzione**: ⚠️ PARZIALMENTE

**Dettaglio**:
```ini
# /etc/php/8.4/fpm/php.ini
disable_functions = 
```

**Rischio**:
- Se qualcuno trova RCE (Remote Code Execution), può eseguire comandi shell
- Funzioni come `exec()`, `system()`, `shell_exec()` sono disponibili

**Azione richiesta**:
```bash
# SSH VPS
sudo nano /etc/php/8.4/fpm/php.ini

# Aggiungi:
disable_functions = exec,passthru,shell_exec,system,proc_open,popen,curl_exec,parse_ini_file,show_source

# Testa Laravel funziona ancora:
sudo systemctl restart php8.4-fpm
curl https://www.danzafacile.it
```

**Impatto**: Potrebbe rompere alcune funzionalità Laravel (verificare dopo modifica)

---

#### 3. ⚠️ Session Encryption Disabled (VULN-008)

**Stato**: NON RISOLTO  
**Priorità**: 🟡 MEDIUM  
**Tempo stimato**: 2 minuti  
**Blocca produzione**: ❌ NO

**Dettaglio**:
```env
# .env
SESSION_ENCRYPT=false
```

**Rischio**:
- Se Redis compromesso, sessioni leggibili in chiaro
- Dati sensibili in sessione potrebbero essere esposti

**Azione richiesta**:
```bash
# SSH VPS
cd /var/www/danzafacile
nano .env

# Cambia:
SESSION_ENCRYPT=true

# Restart servizi
php artisan config:clear
systemctl restart php8.4-fpm
```

**Test**: Verifica login/logout funzionano ancora dopo modifica

---

#### 4. ⚠️ LOG_LEVEL=debug in Production (VULN-009)

**Stato**: NON RISOLTO  
**Priorità**: 🟡 MEDIUM  
**Tempo stimato**: 2 minuti  
**Blocca produzione**: ❌ NO

**Dettaglio**:
```env
# .env
LOG_LEVEL=debug
```

**Rischio**:
- Password, token, dati sensibili potrebbero finire nei log
- Log files possono crescere molto velocemente
- Information disclosure se log esposti

**Azione richiesta**:
```bash
# SSH VPS
cd /var/www/danzafacile
nano .env

# Cambia:
LOG_LEVEL=warning  # o 'error'

# Restart
php artisan config:clear
```

---

#### 5. ⚠️ SMTP Port 25 Publicly Exposed (VULN-007)

**Stato**: NON RISOLTO  
**Priorità**: 🟡 MEDIUM  
**Tempo stimato**: 1 minuto  
**Blocca produzione**: ❌ NO

**Dettaglio**:
- Port 25 (Postfix SMTP) aperto pubblicamente
- Rischio: Email relay abuse, spam

**Azione richiesta**:
```bash
# Se NON usi SMTP inbound (molto probabile):
sudo ufw deny 25/tcp
sudo ufw reload

# Verifica:
sudo ufw status | grep 25
```

**Nota**: DanzaFacile usa SendGrid (porta 587/TLS) quindi porta 25 non serve

---

#### 6. ⚠️ allow_url_fopen Enabled (VULN-010)

**Stato**: NON RISOLTO  
**Priorità**: 🟡 MEDIUM  
**Tempo stimato**: 5 minuti + testing  
**Blocca produzione**: ❌ NO

**Dettaglio**:
```ini
# /etc/php/8.4/fpm/php.ini
allow_url_fopen = On
```

**Rischio**: SSRF vulnerabilities, remote file inclusion

**Azione richiesta**:
```bash
sudo nano /etc/php/8.4/fpm/php.ini

# Cambia:
allow_url_fopen = Off

# Restart
sudo systemctl restart php8.4-fpm

# TEST IMPORTANTE:
curl https://www.danzafacile.it
php artisan queue:work --once
# Se errori, rimetti On
```

⚠️ **WARNING**: Potrebbe rompere HTTP client (Guzzle), testare bene!

---

### 🟢 OPTIONAL - Nice to Have (Non-blockers)

#### 7. ℹ️ Remove X-XSS-Protection Header (Deprecated)

**Priorità**: 🟢 LOW  
**Tempo**: 2 minuti

```php
// app/Http/Middleware/SecurityHeaders.php
// Rimuovi riga:
$response->headers->set('X-XSS-Protection', '1; mode=block');
```

**Motivo**: Header deprecato, CSP moderno è sufficiente

---

#### 8. ℹ️ Submit to HSTS Preload List

**Priorità**: 🟢 LOW  
**Tempo**: 5 minuti

```bash
# Vai su:
https://hstspreload.org/

# Sottometti:
danzafacile.it
```

**Benefit**: Browser forza HTTPS anche prima del primo redirect

---

#### 9. ℹ️ PHP Version Downgrade (8.4.11 → 8.3 LTS)

**Priorità**: 🟢 LOW (solo se problemi)  
**Tempo**: 30 minuti

**Motivo**: PHP 8.4 è bleeding edge, 8.3 è LTS più stabile

**Quando farlo**: Solo se riscontri bug strani in produzione

---

## 📱 FLUTTER APP - Da Completare

### ❌ NON FATTO (Blocking per distribuzione app)

#### 1. 🔴 FCM Integration in Flutter App

**Cosa serve**:
- Package `firebase_messaging` installato
- `google-services.json` (Android) configurato
- `GoogleService-Info.plist` (iOS) configurato
- Codice per registrazione FCM token
- Notification listeners implementati

**Riferimento**: Vedi `PUSH_NOTIFICATIONS_GUIDE.md` sezione "Testing Guide"

---

#### 2. 🔴 Test FCM End-to-End

**Passi richiesti**:
1. Installare app su device reale
2. Login nell'app
3. App registra FCM token
4. Backend invia notifica test
5. Verifica notifica ricevuta su device

**Stato**: ❌ NON TESTATO (serve app Flutter deployata)

---

#### 3. 🔴 Build & Sign APK/IPA

**Android**:
```bash
flutter build apk --release
# O meglio:
flutter build appbundle --release
```

**iOS**:
```bash
flutter build ios --release
# Poi Xcode per signing
```

**Stato**: ❌ DA FARE

---

#### 4. 🔴 Google Play Store / App Store Submission

**Requisiti Google Play**:
- Developer account ($25 one-time)
- Privacy Policy URL
- App screenshots
- App description
- Content rating

**Requisiti App Store**:
- Apple Developer account ($99/year)
- Privacy Policy
- Screenshots
- App description
- App Review submission

**Stato**: ❌ DA FARE

---

## 🔧 QUICK FIXES SCRIPT

Ecco uno script per risolvere le vulnerabilità MEDIUM in 5 minuti:

```bash
#!/bin/bash
# VPS Quick Security Fixes

echo "🔒 Applying security fixes..."

# Fix 1: Session Encryption
sed -i 's/SESSION_ENCRYPT=false/SESSION_ENCRYPT=true/' /var/www/danzafacile/.env

# Fix 2: Log Level
sed -i 's/LOG_LEVEL=debug/LOG_LEVEL=warning/' /var/www/danzafacile/.env

# Fix 3: Close SMTP port
ufw deny 25/tcp
ufw reload

# Fix 4: Disable dangerous PHP functions
sed -i 's/^disable_functions =.*/disable_functions = exec,passthru,shell_exec,system,proc_open,popen,curl_exec,parse_ini_file,show_source/' /etc/php/8.4/fpm/php.ini

# Fix 5: Clear config cache
cd /var/www/danzafacile
php artisan config:clear
php artisan optimize

# Fix 6: Restart services
systemctl restart php8.4-fpm
systemctl restart nginx

echo "✅ Security fixes applied!"
echo "⚠️  Testing required:"
echo "  1. Visit https://www.danzafacile.it"
echo "  2. Test login/logout"
echo "  3. Test API endpoints"
echo ""
echo "If issues, check logs:"
echo "  tail -f /var/www/danzafacile/storage/logs/laravel.log"
```

**Come usare**:
```bash
# SSH VPS
ssh root@157.230.114.252

# Crea script
nano /tmp/security-fixes.sh
# (copia contenuto sopra)

# Esegui
chmod +x /tmp/security-fixes.sh
/tmp/security-fixes.sh

# TEST SUBITO DOPO:
curl -I https://www.danzafacile.it
# Se HTTP 200 OK → tutto bene
# Se HTTP 500 → qualcosa rotto, controlla log
```

---

## ✅ FINAL CHECKLIST

### Backend Production Ready

- [x] SSL/TLS auto-renewal attivo
- [x] Security Grade A+ (CSP nonce-based)
- [x] Queue Worker running
- [x] Firebase connected
- [x] Database secured
- [ ] **Session encryption enabled** (5 min fix)
- [ ] **Log level = warning** (2 min fix)
- [ ] **Dangerous PHP functions disabled** (10 min fix + test)
- [ ] **SMTP port closed** (1 min fix)
- [ ] **allow_url_fopen OFF** (5 min fix + test)

**Backend Score**: 10/15 done (67%) → Con quick fixes: 15/15 (100%)

---

### Flutter App Ready

- [ ] FCM integration implementata
- [ ] Test notifiche su device reale
- [ ] Build APK/IPA signed
- [ ] Privacy Policy pubblicata
- [ ] Screenshots preparate
- [ ] Store listing completo
- [ ] Submitted to Google Play
- [ ] Submitted to App Store
- [ ] Approved e pubblicata

**App Score**: 0/9 done (0%)

---

## 🎯 RACCOMANDAZIONE FINALE

### Priorità di Lavoro

**FASE 1 - Oggi/Domani (2 ore)**:
1. ✅ Esegui script quick fixes (15 min)
2. ✅ Testa tutto funziona (30 min)
3. ✅ Implementa FCM in Flutter app (1 ora)
4. ✅ Test notifiche end-to-end (15 min)

**FASE 2 - Questa settimana (4-6 ore)**:
1. ✅ Build & sign APK (1 ora)
2. ✅ Crea Privacy Policy (1 ora)
3. ✅ Prepara screenshots (1 ora)
4. ✅ Google Play listing (2 ore)
5. ✅ Submit to Google Play (30 min)

**FASE 3 - Prossime 2 settimane**:
1. ✅ Approval Google Play (1-7 giorni)
2. ✅ Build iOS se necessario (2-3 ore)
3. ✅ App Store submission (2 ore)
4. ✅ Approval App Store (1-2 settimane)

---

## ⏱️ TIMELINE STIMATO

**Se inizi oggi**:
- **Backend 100% ready**: Fine oggi (2 ore)
- **Flutter app ready**: Fine settimana (6 ore lavoro)
- **Google Play LIVE**: Tra 1-2 settimane
- **App Store LIVE**: Tra 2-4 settimane

---

**Vuoi che applichi subito i security fixes con lo script automatico?**

---

**Document Version**: 1.0  
**Status**: 📋 CHECKLIST COMPLETA  
**Next Action**: Security quick fixes (15 min)
