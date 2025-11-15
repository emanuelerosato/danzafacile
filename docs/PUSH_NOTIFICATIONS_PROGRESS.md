# 📊 Push Notifications - Progress Report

**Data**: 2025-11-15
**Branch**: `feature/push-notifications-system`
**Status**: Backend Foundation ✅ Complete

---

## ✅ COMPLETATO (Giorno 1)

### 1. Database Schema (4 migrations)

✅ **Migration: `create_lessons_table`**
- Tabella per singole lezioni con date/orari
- Foreign keys: courses, users (instructor), rooms
- Indexes per performance queries
- File: `database/migrations/2025_11_15_212058_create_lessons_table.php`

✅ **Migration: `create_notification_preferences_table`**
- Preferenze notifiche utente (tempo reminder, enable/disable)
- Defaults: enabled=true, reminder=60min
- Unique constraint su user_id
- File: `database/migrations/2025_11_15_212059_create_notification_preferences_table.php`

✅ **Migration: `create_fcm_tokens_table`**
- Token Firebase per dispositivi utente
- Supporto multi-device (android, ios, web)
- Unique constraint (user_id, device_id)
- File: `database/migrations/2025_11_15_212059_create_fcm_tokens_table.php`

✅ **Migration: `create_notification_logs_table`**
- Log notifiche inviate (debugging e analytics)
- Status tracking (pending, sent, failed)
- Error logging
- File: `database/migrations/2025_11_15_212059_create_notification_logs_table.php`

### 2. Eloquent Models (4 models)

✅ **Model: `Lesson`**
- Relationships: course, instructor, room
- Scopes: upcoming(), scheduled(), byDate(), forCourse()
- Accessors: start_datetime, end_datetime, is_upcoming, is_today
- Helper methods: getNotificationTitle(), getNotificationBody(), getNotificationPayload()
- File: `app/Models/Lesson.php`

✅ **Model: `NotificationPreference`**
- Relationship: user
- Accessor: shouldSendLessonReminders (enabled && lesson_reminders)
- Static: defaults(), availableReminderTimes()
- File: `app/Models/NotificationPreference.php`

✅ **Model: `FcmToken`**
- Relationship: user
- Scope: active() (last 30 days)
- Helper: markAsUsed()
- File: `app/Models/FcmToken.php`

✅ **Model: `NotificationLog`**
- Relationships: user, lesson
- Helpers: markAsSent(), markAsFailed()
- JSON cast per data payload
- File: `app/Models/NotificationLog.php`

### 3. API Controllers (3 controllers)

✅ **Controller: `StudentLessonController`**
- `upcoming()`: GET upcoming lessons (default 7 days)
- `index()`: GET all lessons (optional course filter)
- `show()`: GET lesson by ID
- `byDate()`: GET lessons by specific date
- Transformation method per JSON response
- Security: solo lezioni dei corsi in cui studente è iscritto
- File: `app/Http/Controllers/Api/StudentLessonController.php`

✅ **Controller: `NotificationPreferenceController`**
- `show()`: GET user preferences (auto-create con defaults)
- `update()`: PUT update preferences (inline validation)
- Validation: reminder_minutes_before in [15,30,60,120,1440]
- File: `app/Http/Controllers/Api/NotificationPreferenceController.php`

✅ **Controller: `FcmTokenController`**
- `store()`: POST/PUT token (updateOrCreate)
- `destroy()`: DELETE token (logout)
- Auto-update last_used_at timestamp
- File: `app/Http/Controllers/Api/FcmTokenController.php`

### 4. API Routes (8 endpoints)

✅ **Student Lessons Routes** (auth required, student role)
- `GET /api/mobile/v1/student/lessons/upcoming?days=7`
- `GET /api/mobile/v1/student/lessons?course_id=5`
- `GET /api/mobile/v1/student/lessons/{id}`
- `GET /api/mobile/v1/student/lessons/by-date/2025-11-20`

✅ **Notification Settings Routes** (auth required, any role)
- `GET /api/mobile/v1/notifications/preferences`
- `PUT /api/mobile/v1/notifications/preferences`

✅ **FCM Token Routes** (auth required, any role)
- `POST /api/mobile/v1/notifications/fcm-token`
- `DELETE /api/mobile/v1/notifications/fcm-token`

File: `routes/api.php` (linee 22-24, 366-372, 544-553)

### 5. Documentation

✅ **Implementation Plan**
- 3-week detailed roadmap
- Task breakdown per giorno
- Testing strategy
- Risk management
- File: `docs/PUSH_NOTIFICATIONS_IMPLEMENTATION_PLAN.md`

✅ **Backend Specs**
- Complete API documentation
- Database schema details
- Seeder examples
- Testing procedures
- File: `/Users/emanuele/Sites/scuoladidanza-app/PUSH_NOTIFICATIONS_BACKEND_SPECS.md` (Flutter repo)

### 6. Git & GitHub

✅ **Branch Created**: `feature/push-notifications-system`
✅ **Commits**: 3 commits pushati
  1. `feat: Add database migrations for push notifications system`
  2. `feat: Add Eloquent models for push notifications`
  3. `feat: Add API controllers and routes for push notifications`

✅ **GitHub URL**: https://github.com/emanuelerosato/danzafacile/tree/feature/push-notifications-system

---

## ⏳ PROSSIMI STEP (Giorno 2-3)

### 1. Deploy Migrations su Server Produzione

**SSH al server:**
```bash
ssh root@157.230.114.252
cd /var/www/danzafacile
```

**Pull latest code:**
```bash
git fetch origin
git checkout feature/push-notifications-system
git pull origin feature/push-notifications-system
```

**Run migrations:**
```bash
php artisan migrate

# Verifica tabelle create
mysql -u danzafacile -p danzafacile -e "SHOW TABLES LIKE 'lessons';"
mysql -u danzafacile -p danzafacile -e "SHOW TABLES LIKE 'notification_preferences';"
mysql -u danzafacile -p danzafacile -e "SHOW TABLES LIKE 'fcm_tokens';"
mysql -u danzafacile -p danzafacile -e "SHOW TABLES LIKE 'notification_logs';"
```

### 2. Creare Seeders per Dati Test

**LessonSeeder:**
- Popolare tabella lessons con 30 giorni di lezioni
- 2 lezioni/settimana per corso (Lunedì e Giovedì 19:00-20:30)
- Status: scheduled
- File da creare: `database/seeders/LessonSeeder.php`

**NotificationPreferenceSeeder:**
- Creare preferenze per tutti gli studenti esistenti
- Defaults con variazioni casuali per testing
- File da creare: `database/seeders/NotificationPreferenceSeeder.php`

### 3. Testare API Endpoints

**Test con curl:**
```bash
# Login come studente test
TOKEN=$(curl -X POST https://www.danzafacile.it/api/mobile/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"studente@test.it","password":"password"}' \
  | jq -r '.token')

# Test GET upcoming lessons
curl -X GET "https://www.danzafacile.it/api/mobile/v1/student/lessons/upcoming?days=7" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

# Test GET preferences
curl -X GET https://www.danzafacile.it/api/mobile/v1/notifications/preferences \
  -H "Authorization: Bearer $TOKEN"

# Test UPDATE preferences
curl -X PUT https://www.danzafacile.it/api/mobile/v1/notifications/preferences \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"reminder_minutes_before": 120}'

# Test POST FCM token
curl -X POST https://www.danzafacile.it/api/mobile/v1/notifications/fcm-token \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"token":"test_fcm_token","device_type":"android","device_id":"test_001"}'
```

### 4. Firebase Admin SDK Setup

**Installare package:**
```bash
ssh root@157.230.114.252
cd /var/www/danzafacile
composer require kreait/laravel-firebase
php artisan vendor:publish --provider="Kreait\Laravel\Firebase\ServiceProvider" --tag=config
```

**Download credenziali:**
1. Firebase Console → Project Settings → Service Accounts
2. Generate New Private Key → download JSON
3. Upload su server: `scp firebase-credentials.json root@157.230.114.252:/var/www/danzafacile/storage/app/firebase/`
4. Permessi: `chmod 600 /var/www/danzafacile/storage/app/firebase/firebase-credentials.json`

**Configurare .env:**
```bash
FIREBASE_CREDENTIALS=storage/app/firebase/firebase-credentials.json
FIREBASE_DATABASE_URL=https://danzafacile-xxxx.firebaseio.com
```

### 5. FirebasePushService

**Creare service:**
```bash
mkdir -p app/Services
# Creare app/Services/FirebasePushService.php
```

**Implementare:**
- `sendToUser(int $userId, string $title, string $body, array $data)`
- `sendMulticast(array $tokens, ...)`
- Cleanup invalid tokens automatico
- Logging su notification_logs

### 6. Cron Job Command

**Creare command:**
```bash
php artisan make:command SendLessonReminders
```

**Implementare logic:**
- Trova users con lesson_reminders=true
- Per ogni user: cerca lezioni che matchano reminder_minutes_before
- Invia push via FirebasePushService
- File: `app/Console/Commands/SendLessonReminders.php`

**Registrare in scheduler:**
```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('notifications:send-lesson-reminders')
        ->everyFifteenMinutes()
        ->withoutOverlapping()
        ->runInBackground();
}
```

**Verificare crontab:**
```bash
crontab -l | grep schedule:run
# Se non esiste: * * * * * cd /var/www/danzafacile && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🎯 OBIETTIVI SETTIMANA 1

- [x] ✅ Database schema completo (4 migrations)
- [x] ✅ Eloquent models completi (4 models)
- [x] ✅ API controllers completi (3 controllers)
- [x] ✅ API routes registrate (8 endpoints)
- [x] ✅ Tutto committato e pushato su GitHub
- [x] ✅ Migrations deployed su server produzione
- [x] ✅ Seeders creati e eseguiti (TestSchoolSeeder)
- [x] ✅ API testate con curl (tutti 8 endpoint funzionanti)
- [ ] ⏳ Firebase Admin SDK installato
- [ ] ⏳ FirebasePushService implementato
- [ ] ⏳ Cron job command creato e schedulato

---

## 📊 METRICHE PROGRESSO

**Tempo stimato totale**: 12-15 giorni (2.5-3 settimane)
**Tempo trascorso**: 1.5 giorni
**Completato**: ~40%

**Backend**:
- Database: ✅ 100% (4/4 migrations)
- Models: ✅ 100% (4/4 models)
- Controllers: ✅ 100% (3/3 controllers)
- Routes: ✅ 100% (8/8 endpoints)
- Seeders: ✅ 100% (1/1 TestSchoolSeeder)
- API Testing: ✅ 100% (8/8 endpoints testati e funzionanti)
- Services: ⏳ 0% (0/1 FirebasePushService)
- Commands: ⏳ 0% (0/1 SendLessonReminders)

**Flutter App**: ⏳ 0% (inizia Settimana 2)

**Testing**: ⏳ 0%

**Deployment**: ⏳ 0%

---

## 🚀 COME CONTINUARE

### Opzione 1: Deploy su Server (Consigliato)

Deployare subito migrations e testare API su server reale:

```bash
# 1. SSH al server
ssh root@157.230.114.252

# 2. Pull branch
cd /var/www/danzafacile
git fetch origin
git checkout feature/push-notifications-system
git pull

# 3. Run migrations
php artisan migrate

# 4. Verificare tabelle
mysql -u danzafacile -p danzafacile -e "SHOW TABLES;"

# 5. Clear caches
php artisan config:clear
php artisan route:clear

# 6. Test API endpoint
curl -X GET https://www.danzafacile.it/api/mobile/v1/notifications/preferences \
  -H "Authorization: Bearer TOKEN_STUDENTE"
```

### Opzione 2: Continuare Sviluppo Locale

Continuare con seeders e Firebase setup prima di deploy:

```bash
# 1. Creare seeders
php artisan make:seeder LessonSeeder
php artisan make:seeder NotificationPreferenceSeeder

# 2. Implementare seeders (vedi PUSH_NOTIFICATIONS_BACKEND_SPECS.md)

# 3. Creare FirebasePushService

# 4. Creare SendLessonReminders command

# 5. Poi deploy tutto insieme
```

---

## 📞 SUPPORTO

**Documentazione Completa**:
- Implementation Plan: `docs/PUSH_NOTIFICATIONS_IMPLEMENTATION_PLAN.md`
- Backend Specs: `/Users/emanuele/Sites/scuoladidanza-app/PUSH_NOTIFICATIONS_BACKEND_SPECS.md`
- Progress Report: `docs/PUSH_NOTIFICATIONS_PROGRESS.md` (questo file)

**GitHub Branch**: https://github.com/emanuelerosato/danzafacile/tree/feature/push-notifications-system

**Commits** (Giorno 1):
1. `78d2700` - Database migrations
2. `06f9c59` - Eloquent models
3. `c3967a5` - API controllers and routes

**Commits** (Giorno 2):
4. `81fefac` - Fix foreign key room_id per school_rooms
5. `26c54f0` - Add TestSchoolSeeder
6. `f1e00d0` - Fix schema TestSchoolSeeder compatibilità
7. `7b9c864` - Fix string interpolation in seeder
8. `27edf9f` - Add instructor_id in Course creation
9. `e15f192` - Fix accessors start_datetime/end_datetime

---

## ✅ RIEPILOGO GIORNO 2 (2025-11-15)

### Completato
1. **Deploy Migrations su Server Produzione** ✅
   - 4 tabelle create su server: `lessons`, `notification_preferences`, `fcm_tokens`, `notification_logs`
   - Fix foreign key `room_id` → `school_rooms` (tabella corretta)

2. **TestSchoolSeeder Completo** ✅
   - Scuola test isolata: `[TEST] Scuola Push Notifications` (ID: 4)
   - 3 Studenti: `studente1/2/3@test.pushnotif.local` (password: `password`)
   - 2 Instructors, 2 Sale, 2 Corsi attivi
   - **18 lezioni** schedulati (Lunedì/Giovedì 19:00-20:30, prossimi 30 giorni)
   - Tutte le notification_preferences create

3. **Fix Bug API** ✅
   - Risolto: "Double date specification" error in `start_datetime` accessor
   - Causa: `start_time` già cast a datetime, serve `->format('H:i:s')`

4. **API Testing Completo** ✅
   - ✅ `GET /api/mobile/v1/student/lessons/upcoming?days=7` (4 lezioni)
   - ✅ `GET /api/mobile/v1/student/lessons` (18 lezioni totali)
   - ✅ `GET /api/mobile/v1/student/lessons?course_id=1` (9 lezioni)
   - ✅ `GET /api/mobile/v1/student/lessons/{id}` (singola lezione)
   - ✅ `GET /api/mobile/v1/student/lessons/by-date/2025-11-20` (2 lezioni)
   - ✅ `GET /api/mobile/v1/notifications/preferences` (get preferences)
   - ✅ `PUT /api/mobile/v1/notifications/preferences` (update)
   - ✅ `POST /api/mobile/v1/notifications/fcm-token` (register token)
   - ✅ `DELETE /api/mobile/v1/notifications/fcm-token` (remove token)

### Dati Test Disponibili
- **Token API**: `1|elKwsA8i5G7Z6jU0G9rOHkDgV5xk752Ep6qqaT5q5a9740b3` (studente1)
- **User ID**: 133 (Studente Test 1)
- **Corsi iscritti**: 2 (Danza Classica Test, Hip Hop Test)
- **Lezioni upcoming**: 4 nei prossimi 7 giorni

---

**Ultima modifica**: 2025-11-16 00:05
**Status**: Backend API Complete & Tested ✅
**Prossimo Step**: Firebase Admin SDK setup
