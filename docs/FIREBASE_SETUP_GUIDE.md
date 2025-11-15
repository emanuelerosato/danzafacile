# 🔥 Firebase Setup Guide - Push Notifications

Guida completa per configurare Firebase Cloud Messaging sul server produzione.

---

## 📋 Prerequisiti

- Accesso Firebase Console: https://console.firebase.google.com/
- Accesso SSH server: `root@157.230.114.252`
- Progetto Firebase esistente o da creare

---

## 🚀 Step 1: Firebase Console Setup

### 1.1 Crea/Apri Progetto Firebase

1. Vai su https://console.firebase.google.com/
2. Crea nuovo progetto oppure seleziona progetto esistente "DanzaFacile"
3. Abilita **Google Analytics** (opzionale ma consigliato)

### 1.2 Aggiungi App Android/iOS

**Android:**
1. Project Settings → Add app → Android
2. Package name: `com.danzafacile.app` (o il tuo package)
3. Download `google-services.json`
4. Salva per dopo (serve per Flutter app)

**iOS:**
1. Project Settings → Add app → iOS
2. Bundle ID: `com.danzafacile.app`
3. Download `GoogleService-Info.plist`
4. Salva per dopo

### 1.3 Abilita Cloud Messaging

1. Vai su **Build → Cloud Messaging**
2. Verifica che sia abilitato
3. Nota: il setup automatico dovrebbe essere già fatto

---

## 🔑 Step 2: Download Service Account Key

### 2.1 Genera Credenziali Admin SDK

1. Vai su **Project Settings** (⚙️ icona in alto a sinistra)
2. Tab **Service accounts**
3. Click **Generate new private key**
4. Conferma → Download JSON file
5. **IMPORTANTE:** Questo file contiene credenziali private! Non committarlo mai su Git

Il file scaricato avrà questo formato:
```json
{
  "type": "service_account",
  "project_id": "danzafacile-xxxxx",
  "private_key_id": "abc123...",
  "private_key": "-----BEGIN PRIVATE KEY-----\n...",
  "client_email": "firebase-adminsdk-xxxxx@danzafacile-xxxxx.iam.gserviceaccount.com",
  ...
}
```

### 2.2 Rinomina File

Rinomina il file scaricato in: `firebase-credentials.json`

---

## 📤 Step 3: Upload Credenziali su Server

### 3.1 Upload via SCP

Dal tuo computer locale:

```bash
scp firebase-credentials.json root@157.230.114.252:/var/www/danzafacile/storage/app/firebase/
```

### 3.2 Verifica Permessi

SSH sul server:

```bash
ssh root@157.230.114.252
cd /var/www/danzafacile/storage/app/firebase/
ls -la firebase-credentials.json
```

Output atteso:
```
-rw------- 1 deploy deploy 2345 Nov 16 00:00 firebase-credentials.json
```

Se i permessi sono sbagliati:

```bash
chmod 600 firebase-credentials.json
chown deploy:deploy firebase-credentials.json
```

---

## ⚙️ Step 4: Configurazione Laravel

### 4.1 Verifica .env

Il file `.env` dovrebbe già contenere:

```env
# Firebase Cloud Messaging
FIREBASE_CREDENTIALS=storage/app/firebase/firebase-credentials.json
FIREBASE_DATABASE_URL=https://danzafacile-xxxxx.firebaseio.com
```

**IMPORTANTE:** Aggiorna `FIREBASE_DATABASE_URL` con il tuo progetto ID!

Per trovare il Database URL:
1. Firebase Console → Project Settings → General
2. Copia "Project ID"
3. URL formato: `https://[PROJECT_ID]-default-rtdb.firebaseio.com`

### 4.2 Clear Laravel Caches

```bash
cd /var/www/danzafacile
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

---

## ✅ Step 5: Test Setup

### 5.1 Test Connessione Firebase

Crea script di test temporaneo:

```bash
cd /var/www/danzafacile
php artisan tinker
```

Nel tinker:

```php
$service = new App\Services\FirebasePushService();
$test = $service->testConnection();
echo $test ? "✅ Firebase OK\n" : "❌ Firebase FAILED\n";
exit
```

Output atteso: `✅ Firebase OK`

### 5.2 Test Invio Notifica (Opzionale)

**SOLO SE** hai già un FCM token registrato:

```bash
php artisan tinker
```

```php
$service = new App\Services\FirebasePushService();
$userId = 133; // User ID di test
$result = $service->sendToUser($userId, "Test 🔥", "Notifica di test da backend", ["test" => true]);
print_r($result);
exit
```

Output atteso:
```php
Array
(
    [success] => 1
    [sent_count] => 1
    [failed_tokens] => Array()
)
```

---

## 🕐 Step 6: Verifica Cron Job

### 6.1 Check Schedule

Verifica che il comando sia schedulato:

```bash
php artisan schedule:list
```

Output atteso:
```
0 0-23/1 * * * php artisan emails:process-scheduled ......... Next Due: 1 hour from now
*/15 * * * * php artisan notifications:send-lesson-reminders  Next Due: 12 minutes from now
```

### 6.2 Test Manuale Command

```bash
php artisan notifications:send-lesson-reminders
```

Output atteso:
```
🔔 Starting lesson reminder notifications...
Found 3 users with reminders enabled
...
✅ Lesson reminders completed!
   Sent: 0
   Skipped: 3
```

### 6.3 Verifica Crontab

```bash
crontab -l | grep schedule
```

Output atteso:
```
* * * * * cd /var/www/danzafacile && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🔍 Troubleshooting

### Problema: "Firebase initialization failed"

**Causa:** Credenziali non trovate o invalide

**Fix:**
1. Verifica path file: `ls -la storage/app/firebase/firebase-credentials.json`
2. Verifica formato JSON: `cat storage/app/firebase/firebase-credentials.json | jq .`
3. Verifica `.env`: `cat .env | grep FIREBASE`

### Problema: "Invalid token" errors

**Causa:** FCM token non valido o scaduto

**Fix:** I token invalidi vengono automaticamente rimossi dal sistema.

### Problema: No notifications sent

**Causa:** Nessun user con FCM token registrato

**Fix:**
1. Verifica: `php artisan tinker` → `App\Models\FcmToken::count()`
2. Registra token via API: `POST /api/mobile/v1/notifications/fcm-token`

---

## 📊 Monitoring

### Check Notification Logs

```bash
php artisan tinker
```

```php
// Ultime 10 notifiche
App\Models\NotificationLog::latest()->take(10)->get(['user_id', 'title', 'status', 'sent_at']);

// Count per status
App\Models\NotificationLog::groupBy('status')->selectRaw('status, count(*) as count')->get();
```

### Check Laravel Logs

```bash
tail -f storage/logs/laravel.log | grep -i firebase
```

---

## 🔒 Security Checklist

- [ ] `firebase-credentials.json` ha permessi `600`
- [ ] File non è committato su Git (check `.gitignore`)
- [ ] `.env` non è pubblico
- [ ] Solo server ha accesso alle credenziali
- [ ] Credenziali diverse per staging/production

---

## 📚 Risorse

- [Firebase Admin SDK - PHP](https://firebase-php.readthedocs.io/)
- [Laravel Firebase Package](https://github.com/kreait/laravel-firebase)
- [FCM Documentation](https://firebase.google.com/docs/cloud-messaging)

---

**Creato**: 2025-11-16
**Ultima modifica**: 2025-11-16
**Autore**: Claude Code + Emanuele
