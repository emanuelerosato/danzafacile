# 🚀 Piano Operativo Implementazione Push Notifications - DanzaFacile

**Progetto**: Sistema completo di notifiche push per promemoria lezioni
**Team**: Full-stack (Backend Laravel + Flutter App)
**Target**: Produzione - Sistema completo e professionale
**Data Inizio**: 2025-11-15
**Durata Stimata**: 12-15 giorni lavorativi (2.5-3 settimane)
**Versione**: 1.0.0

---

## 📋 EXECUTIVE SUMMARY

### Obiettivo
Implementare un sistema completo di notifiche push che permetta agli studenti di ricevere promemoria automatici prima dell'inizio delle lezioni, con:
- Notifiche locali schedulate (offline-first)
- Push notifications da backend (real-time updates)
- Preferenze utente personalizzabili
- Ambiente test dedicato per non corrompere dati produzione

### Strategia Dual-Mode
1. **Local Scheduling**: Notifiche schedulate nell'app (funzionano offline)
2. **Remote Push**: Notifiche dal backend Laravel via Firebase (cancellazioni last-minute)

### Ambiente di Lavoro
- **Produzione**: Scuola reale già online (NO MODIFICHE fino a testing completo)
- **Test**: Nuova scuola test con dati farlocchi per sviluppo sicuro
- **Rollback**: Piano completo in caso di problemi

---

## 🎯 OBIETTIVI TECNICI

### Must-Have (Critical)
- ✅ Backend Laravel: API complete per lezioni e preferenze notifiche
- ✅ Database: Tabelle `lessons`, `notification_preferences`, `fcm_tokens`
- ✅ Flutter App: Domain layer con entities Lesson + NotificationPreferences
- ✅ Firebase Admin SDK configurato su server Laravel
- ✅ Cron job ogni 15 minuti per inviare push remoti
- ✅ Local notifications scheduling funzionante
- ✅ Settings screen per preferenze utente
- ✅ Deep linking: tap notifica → dettaglio lezione

### Nice-to-Have (Optional)
- ⭐ Calendar view per visualizzare prossime lezioni
- ⭐ Background sync Android/iOS
- ⭐ Analytics dettagliato su aperture notifiche
- ⭐ Notification logs per debugging

---

## 📊 ANALISI DEPENDENCIES

### Backend Laravel Dependencies

```
courses (✅ esistente)
   ↓
lessons (❌ DA CREARE)
   ↓
notification_preferences (❌ DA CREARE)
   ↓
fcm_tokens (❌ DA CREARE)
   ↓
Firebase Admin SDK (❌ DA CONFIGURARE)
   ↓
Cron Job (❌ DA IMPLEMENTARE)
```

### Flutter App Dependencies

```
NotificationService (✅ esistente ma non inizializzato)
   ↓
Lesson Entity (❌ DA CREARE)
   ↓
LessonRepository (❌ DA CREARE)
   ↓
LessonReminderService (❌ DA CREARE)
   ↓
NotificationPreferencesService (❌ DA CREARE)
   ↓
UI Screens (❌ DA CREARE)
```

### Critical Path
**Backend DEVE essere completato PRIMA di Flutter app** perché:
- Flutter app chiama API `/lessons/upcoming`
- Senza backend, nessun dato da schedulare
- Testing end-to-end richiede entrambi attivi

---

## 🏗️ ARCHITETTURA SISTEMA

### Flusso Completo

```
┌─────────────────────────────────────────────────────────────────┐
│                    BACKEND LARAVEL (Server)                      │
│                                                                   │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ DATABASE                                                   │  │
│  │ • courses (esistente)                                      │  │
│  │ • lessons (nuova) ← orari specifici per ogni lezione      │  │
│  │ • notification_preferences (nuova) ← user settings        │  │
│  │ • fcm_tokens (nuova) ← device tokens                      │  │
│  └──────────────────────────────────────────────────────────┘  │
│                            ↓ ↑                                   │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ API ENDPOINTS                                              │  │
│  │ GET  /api/student/lessons/upcoming?days=7                 │  │
│  │ GET  /api/student/lessons/{id}                            │  │
│  │ GET  /api/notifications/preferences                        │  │
│  │ PUT  /api/notifications/preferences                        │  │
│  │ POST /api/notifications/fcm-token                          │  │
│  └──────────────────────────────────────────────────────────┘  │
│                            ↓ ↑                                   │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ CRON JOB (ogni 15 minuti)                                 │  │
│  │ • Controlla lezioni upcoming                              │  │
│  │ • Per ogni studente: check preferenze reminder            │  │
│  │ • Invia push via Firebase se match timing                 │  │
│  └──────────────────────────────────────────────────────────┘  │
│                            ↓                                     │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ FIREBASE ADMIN SDK                                         │  │
│  │ • Invia push notifications                                │  │
│  │ • Gestisce invalid tokens cleanup                         │  │
│  └──────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
                            ↓ ↑ HTTP/FCM
┌─────────────────────────────────────────────────────────────────┐
│                    FLUTTER APP (Mobile)                          │
│                                                                   │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ NOTIFICATION LAYER                                         │  │
│  │ • NotificationService (FCM receiver)                      │  │
│  │ • LessonReminderService (local scheduling)               │  │
│  │ • NotificationPreferencesService (settings)               │  │
│  └──────────────────────────────────────────────────────────┘  │
│                            ↓ ↑                                   │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ DOMAIN LAYER                                               │  │
│  │ • Lesson Entity                                            │  │
│  │ • NotificationPreferences Entity                           │  │
│  │ • LessonRepository (interface)                            │  │
│  └──────────────────────────────────────────────────────────┘  │
│                            ↓ ↑                                   │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ DATA LAYER                                                 │  │
│  │ • LessonModel (JSON serialization)                        │  │
│  │ • LessonRepositoryImpl (Dio API calls)                    │  │
│  └──────────────────────────────────────────────────────────┘  │
│                            ↓ ↑                                   │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ PRESENTATION LAYER                                         │  │
│  │ • Notification Settings Screen                             │  │
│  │ • Lessons Calendar Screen (optional)                       │  │
│  │ • Providers (Riverpod state)                              │  │
│  └──────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
```

---

## 📅 TIMELINE DETTAGLIATA - 3 SETTIMANE

### **SETTIMANA 1: Backend Foundation + Test Environment**

#### **Giorno 1-2: Setup Ambiente Test** (2 giorni)
**Responsabile**: Backend Developer
**Priority**: CRITICAL

**Tasks:**
1. ✅ Creare nuova scuola test nel database
   - Nome: "Scuola Test - DanzaFacile Dev"
   - Admin test: `admin.test@danzafacile.it` / password: `TestDev2025!`
   - Flag in database: `is_test_school = true`

2. ✅ Popolare con dati farlocchi
   - 3 corsi test (Bachata Base, Salsa Intermedia, Hip Hop Avanzato)
   - 10 studenti test (`studente1@test.it` ... `studente10@test.it`)
   - 2 istruttori test
   - 2 sale test

3. ✅ Creare script seeder dedicato
   - `php artisan db:seed --class=TestSchoolSeeder`
   - Dati isolati, non mischiano con produzione

4. ✅ Configurare env test separato (`.env.testing`)

**Deliverables:**
- ✅ Database con scuola test popolata
- ✅ Credenziali test documentate
- ✅ Script seeder riutilizzabile

---

#### **Giorno 3-4: Backend Database & Models** (2 giorni)
**Responsabile**: Backend Developer
**Priority**: CRITICAL

**Tasks:**

1. ✅ **Migration 1: `create_lessons_table.php`**
   ```bash
   php artisan make:migration create_lessons_table
   ```
   - Campi: `course_id`, `instructor_id`, `room_id`, `lesson_date`, `start_time`, `end_time`, `status`, `notes`
   - Indexes: `lesson_date`, `status`, composite `(lesson_date, start_time)`
   - Foreign keys: courses, users, rooms

2. ✅ **Migration 2: `create_notification_preferences_table.php`**
   ```bash
   php artisan make:migration create_notification_preferences_table
   ```
   - Campi: `user_id` (unique), `enabled`, `lesson_reminders`, `reminder_minutes_before`, etc.
   - Default values: tutto true, 60 minuti

3. ✅ **Migration 3: `create_fcm_tokens_table.php`**
   ```bash
   php artisan make:migration create_fcm_tokens_table
   ```
   - Campi: `user_id`, `token`, `device_type`, `device_id`, `last_used_at`
   - Unique constraint: `(user_id, device_id)`

4. ✅ **Migration 4: `create_notification_logs_table.php`** (optional)
   ```bash
   php artisan make:migration create_notification_logs_table
   ```
   - Per debugging e analytics

5. ✅ **Models Eloquent:**
   ```bash
   php artisan make:model Lesson
   php artisan make:model NotificationPreference
   php artisan make:model FcmToken
   php artisan make:model NotificationLog
   ```
   - Implementare relationships
   - Implementare scopes (`upcoming`, `byDate`)
   - Implementare accessors (`start_datetime`, `is_upcoming`)
   - Implementare helper methods (`getNotificationTitle()`)

6. ✅ **Seeders:**
   ```bash
   php artisan make:seeder LessonSeeder
   php artisan make:seeder NotificationPreferenceSeeder
   ```
   - LessonSeeder: 30 giorni di lezioni future (2 lezioni/settimana per corso)
   - NotificationPreferenceSeeder: preferenze default per studenti test

**Testing:**
```bash
# Eseguire migrations
php artisan migrate

# Eseguire seeders
php artisan db:seed --class=LessonSeeder
php artisan db:seed --class=NotificationPreferenceSeeder

# Verificare database
mysql -u danzafacile -p danzafacile -e "SELECT COUNT(*) FROM lessons;"
mysql -u danzafacile -p danzafacile -e "SELECT * FROM lessons LIMIT 5;"
```

**Deliverables:**
- ✅ 4 migrations eseguite con successo
- ✅ 4 models con relationships
- ✅ Database popolato con ~240 lezioni test (8 corsi × 30 giorni × 2 lezioni/settimana)
- ✅ Preferenze notifiche per 10 studenti test

---

#### **Giorno 5: Backend API Endpoints** (1 giorno)
**Responsabile**: Backend Developer
**Priority**: CRITICAL

**Tasks:**

1. ✅ **Controller: `StudentLessonController.php`**
   ```bash
   php artisan make:controller Api/StudentLessonController
   ```
   - `upcoming()` - GET /api/student/lessons/upcoming?days=7
   - `index()` - GET /api/student/lessons?course_id=5
   - `show()` - GET /api/student/lessons/{id}
   - `byDate()` - GET /api/student/lessons/by-date/{date}

2. ✅ **Controller: `NotificationPreferenceController.php`**
   ```bash
   php artisan make:controller Api/NotificationPreferenceController
   ```
   - `show()` - GET /api/notifications/preferences
   - `update()` - PUT /api/notifications/preferences

3. ✅ **Controller: `FcmTokenController.php`**
   ```bash
   php artisan make:controller Api/FcmTokenController
   ```
   - `store()` - POST /api/notifications/fcm-token
   - `destroy()` - DELETE /api/notifications/fcm-token

4. ✅ **Request Validation:**
   ```bash
   php artisan make:request UpdateNotificationPreferencesRequest
   php artisan make:request StoreFcmTokenRequest
   ```

5. ✅ **Routes in `routes/api.php`:**
   ```php
   // Lessons (authenticated students)
   Route::middleware('auth:sanctum')->group(function () {
       Route::prefix('student/lessons')->group(function () {
           Route::get('/upcoming', [StudentLessonController::class, 'upcoming']);
           Route::get('/', [StudentLessonController::class, 'index']);
           Route::get('/{id}', [StudentLessonController::class, 'show']);
           Route::get('/by-date/{date}', [StudentLessonController::class, 'byDate']);
       });

       Route::prefix('notifications')->group(function () {
           Route::get('/preferences', [NotificationPreferenceController::class, 'show']);
           Route::put('/preferences', [NotificationPreferenceController::class, 'update']);
           Route::post('/fcm-token', [FcmTokenController::class, 'store']);
           Route::delete('/fcm-token', [FcmTokenController::class, 'destroy']);
       });
   });
   ```

**Testing:**
```bash
# Test API con studente test autenticato
# Login come studente1@test.it
curl -X POST https://www.danzafacile.it/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"studente1@test.it","password":"TestDev2025!"}'

# Salva token ricevuto in $TOKEN

# Test GET upcoming lessons
curl -X GET https://www.danzafacile.it/api/student/lessons/upcoming?days=7 \
  -H "Authorization: Bearer $TOKEN"

# Test GET preferences
curl -X GET https://www.danzafacile.it/api/notifications/preferences \
  -H "Authorization: Bearer $TOKEN"

# Test UPDATE preferences
curl -X PUT https://www.danzafacile.it/api/notifications/preferences \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"reminder_minutes_before": 120}'

# Test POST fcm token
curl -X POST https://www.danzafacile.it/api/notifications/fcm-token \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"token":"test_fcm_token","device_type":"android","device_id":"test_001"}'
```

**Deliverables:**
- ✅ 3 controllers implementati
- ✅ 2 request validations
- ✅ 7 routes API attive
- ✅ Tutte le API rispondono correttamente (test con curl)

---

### **SETTIMANA 2: Firebase + Cron Job + Flutter Domain Layer**

#### **Giorno 6-7: Firebase Admin SDK Setup** (2 giorni)
**Responsabile**: Backend Developer + DevOps
**Priority**: CRITICAL

**Tasks:**

1. ✅ **Installare Firebase Admin SDK**
   ```bash
   ssh root@157.230.114.252
   cd /var/www/danzafacile
   composer require kreait/laravel-firebase
   php artisan vendor:publish --provider="Kreait\Laravel\Firebase\ServiceProvider" --tag=config
   ```

2. ✅ **Scaricare credenziali Firebase**
   - Vai su Firebase Console: https://console.firebase.google.com
   - Seleziona progetto DanzaFacile
   - Settings → Service Accounts → Generate New Private Key
   - Scarica JSON, rinomina in `firebase-credentials.json`
   - Upload su server:
     ```bash
     scp firebase-credentials.json root@157.230.114.252:/var/www/danzafacile/storage/app/firebase/
     chmod 600 /var/www/danzafacile/storage/app/firebase/firebase-credentials.json
     ```

3. ✅ **Configurare `.env`**
   ```bash
   # Aggiungi al file .env su server
   FIREBASE_CREDENTIALS=storage/app/firebase/firebase-credentials.json
   FIREBASE_DATABASE_URL=https://danzafacile-xxxx.firebaseio.com
   ```

4. ✅ **Configurare `config/firebase.php`**
   ```php
   return [
       'credentials' => [
           'file' => env('FIREBASE_CREDENTIALS'),
       ],
       'database' => [
           'url' => env('FIREBASE_DATABASE_URL'),
       ],
   ];
   ```

5. ✅ **Creare Service: `FirebasePushService.php`**
   ```bash
   php artisan make:service FirebasePushService
   # (creare manualmente in app/Services/)
   ```
   - Implementare `sendToUser()`, `sendMulticast()`
   - Cleanup invalid tokens automatico
   - Logging su `notification_logs` table

6. ✅ **Test manuale Firebase**
   ```bash
   php artisan tinker

   $service = app(\App\Services\FirebasePushService::class);
   $service->sendToUser(
       1, // user_id di uno studente test
       'Test Push',
       'Questa è una notifica di test',
       ['type' => 'test']
   );
   ```

**Deliverables:**
- ✅ Firebase Admin SDK installato e configurato
- ✅ `FirebasePushService` funzionante
- ✅ Test push ricevuto su dispositivo reale

---

#### **Giorno 8: Backend Cron Job** (1 giorno)
**Responsabile**: Backend Developer
**Priority**: CRITICAL

**Tasks:**

1. ✅ **Creare Command:**
   ```bash
   php artisan make:command SendLessonReminders
   ```
   - Signature: `notifications:send-lesson-reminders`
   - Logic:
     - Trova tutti user con `lesson_reminders = true`
     - Per ogni user: trova lezioni upcoming che matchano `reminder_minutes_before`
     - Invia push via `FirebasePushService`

2. ✅ **Registrare in Scheduler**
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

3. ✅ **Configurare Crontab su server** (se non già fatto)
   ```bash
   crontab -e
   # Aggiungi se non esiste:
   * * * * * cd /var/www/danzafacile && php artisan schedule:run >> /dev/null 2>&1
   ```

4. ✅ **Test manuale command**
   ```bash
   php artisan notifications:send-lesson-reminders
   # Controlla output: quante notifiche inviate

   # Verifica logs
   tail -f storage/logs/laravel.log

   # Verifica notification_logs table
   mysql -u danzafacile -p danzafacile -e "SELECT * FROM notification_logs ORDER BY created_at DESC LIMIT 10;"
   ```

**Deliverables:**
- ✅ Command funzionante
- ✅ Cron job schedulato
- ✅ Test manuale con push ricevuti

---

#### **Giorno 9-10: Flutter Domain & Data Layer** (2 giorni)
**Responsabile**: Mobile Developer
**Priority**: CRITICAL

**Tasks:**

1. ✅ **Installare dipendenze mancanti**
   ```yaml
   # pubspec.yaml
   dependencies:
     timezone: ^0.9.0  # Per timezone handling
   ```
   ```bash
   flutter pub get
   ```

2. ✅ **Lesson Entity**
   ```bash
   # Creare file lib/features/lessons/domain/entities/lesson.dart
   ```
   - Campi: id, courseId, courseName, lessonDate, startTime, endTime, status, etc.
   - Extends Equatable per comparisons
   - Props list per equality

3. ✅ **NotificationPreferences Entity**
   ```bash
   # Creare lib/features/notifications/domain/entities/notification_preferences.dart
   ```
   - Campi: enabled, lessonReminders, reminderMinutesBefore, etc.

4. ✅ **LessonModel con JSON serialization**
   ```bash
   # Creare lib/features/lessons/data/models/lesson_model.dart
   ```
   - `@JsonSerializable()`
   - `fromJson()`, `toJson()`, `toEntity()`
   - Converter per LessonStatus enum

   ```bash
   # Generare codice
   flutter pub run build_runner build --delete-conflicting-outputs
   ```

5. ✅ **LessonRepository (Domain)**
   ```bash
   # Creare lib/features/lessons/domain/repositories/lesson_repository.dart
   ```
   - Abstract class con methods:
     - `Future<Either<Failure, List<Lesson>>> getUpcomingLessons({int days})`
     - `Future<Either<Failure, Lesson>> getLessonById(int id)`

6. ✅ **LessonRepositoryImpl (Data)**
   ```bash
   # Creare lib/features/lessons/data/repositories/lesson_repository_impl.dart
   ```
   - Implementa abstract repository
   - Usa Dio per API calls
   - Error handling (NetworkException, ServerException)
   - Mapping `LessonModel → Lesson entity`

7. ✅ **NotificationPreferencesModel & Repository**
   - Stesso pattern di Lesson
   - Model + Repository interface + Implementation

8. ✅ **Unit Tests**
   ```bash
   # Test per entities
   flutter test test/features/lessons/domain/entities/lesson_test.dart

   # Test per models serialization
   flutter test test/features/lessons/data/models/lesson_model_test.dart
   ```

**Deliverables:**
- ✅ 2 entities (Lesson, NotificationPreferences)
- ✅ 2 models con JSON serialization
- ✅ 2 repositories (interface + implementation)
- ✅ Unit tests passano (coverage > 80%)

---

### **SETTIMANA 3: Flutter Services + UI + Testing + Deployment**

#### **Giorno 11: Flutter Notification Services** (1 giorno)
**Responsabile**: Mobile Developer
**Priority**: CRITICAL

**Tasks:**

1. ✅ **Inizializzare NotificationService in main.dart**
   ```dart
   Future<void> _initializeFirebase() async {
     try {
       await Firebase.initializeApp();
       await AnalyticsService.initialize();
       await CrashlyticsService.initialize();

       // ✅ AGGIUNGERE
       await NotificationService().initialize();

     } catch (e) {
       debugPrint('⚠️ Firebase initialization failed: $e');
     }
   }
   ```

2. ✅ **LessonReminderService**
   ```bash
   # Creare lib/core/services/lesson_reminder_service.dart
   ```
   - `scheduleUpcomingLessonsReminders({int days = 7})`
   - `scheduleLessonReminder(Lesson lesson, int minutesBefore)`
   - `cancelAllReminders()`
   - `cancelLessonReminder(int lessonId)`
   - Usare `flutter_local_notifications` con `zonedSchedule()`
   - Gestire timezone con package `timezone`
   - Notification payload: `{"type":"lesson_reminder","lesson_id":"123"}`

3. ✅ **NotificationPreferencesService**
   ```bash
   # Creare lib/core/services/notification_preferences_service.dart
   ```
   - `getPreferences()` - fetch da API + cache locale
   - `savePreferences()` - save su API + SharedPreferences
   - `updateReminderTime(int minutesBefore)` - reschedule all

4. ✅ **Sync FCM Token con Backend**
   ```dart
   // In NotificationService.initialize()
   _fcmToken = await _firebaseMessaging.getToken();
   await _syncTokenWithBackend(_fcmToken!);

   Future<void> _syncTokenWithBackend(String token) async {
     await dio.post('/notifications/fcm-token', data: {
       'token': token,
       'device_type': Platform.isAndroid ? 'android' : 'ios',
       'device_id': await _getDeviceId(),
     });
   }
   ```

5. ✅ **Deep Linking Handler**
   ```bash
   # Creare lib/core/routing/notification_deep_link_handler.dart
   ```
   - Parse notification payload
   - Navigate to lesson detail screen
   - Handle quando app è closed/background/foreground

**Deliverables:**
- ✅ NotificationService inizializzato
- ✅ LessonReminderService funzionante
- ✅ FCM token sincronizzato con backend
- ✅ Deep linking testato

---

#### **Giorno 12-13: Flutter UI Screens** (2 giorni)
**Responsabile**: Mobile Developer
**Priority**: HIGH

**Tasks:**

1. ✅ **Notification Settings Screen**
   ```bash
   # Creare lib/features/settings/presentation/screens/notification_settings_screen.dart
   ```
   - Toggle lesson reminders on/off
   - Radio buttons per tempo reminder (15min, 30min, 1h, 2h, 1 giorno)
   - Salva e reschedula notifiche
   - Mostra permessi OS status

2. ✅ **Notification Permission Onboarding** (opzionale)
   ```bash
   # Creare lib/features/onboarding/presentation/screens/notification_permission_screen.dart
   ```
   - Mostra dopo primo login
   - Spiega benefici notifiche
   - Richiede permessi OS

3. ✅ **Lessons Calendar View** (opzionale - nice to have)
   ```bash
   # Installare package
   flutter pub add table_calendar

   # Creare lib/features/lessons/presentation/screens/lessons_calendar_screen.dart
   ```
   - Calendar con giorni con lezioni
   - Lista lezioni per giorno selezionato
   - Badge "reminder attivo"

4. ✅ **Provider/State Management**
   ```bash
   # Creare providers con Riverpod
   # lib/features/lessons/presentation/providers/lesson_providers.dart
   # lib/features/notifications/presentation/providers/notification_providers.dart
   ```

5. ✅ **Integrazione con Navigation**
   - Aggiungere routes per nuove screens
   - Collegare da settings menu esistente

**Deliverables:**
- ✅ Notification settings screen funzionante
- ✅ UI testata su Android e iOS
- ✅ Widget tests per nuove screens

---

#### **Giorno 14: Testing End-to-End** (1 giorno)
**Responsabile**: Full Team
**Priority**: CRITICAL

**Scenario Testing:**

1. ✅ **Scenario 1: Primo utilizzo**
   - Studente test login
   - Accetta permessi notifiche
   - App schedula reminder per prossime lezioni
   - Verifica: notifiche schedulate correttamente

2. ✅ **Scenario 2: Cambio preferenze**
   - Cambia reminder da 60min a 120min
   - Verifica: tutte le notifiche rescheduled
   - Check database: preferences salvate

3. ✅ **Scenario 3: Notifica locale ricevuta**
   - Attendi notifica scheduled (o simula cambiando ora device)
   - Tap notifica
   - Verifica: app naviga a dettaglio lezione corretta

4. ✅ **Scenario 4: Push remoto dal backend**
   - Triggera cron job manualmente
   - Verifica: push ricevuto
   - Verifica: notification_logs table popolata

5. ✅ **Scenario 5: Lezione cancellata**
   - Admin cancella lezione da dashboard
   - Backend invia push "Lezione cancellata"
   - App rimuove notifica locale schedulata

**Testing Checklist:**
- [ ] Android 12+ (notification channels)
- [ ] Android 10-11 (backward compatibility)
- [ ] iOS 16+ (notification permission)
- [ ] iOS 14-15 (backward compatibility)
- [ ] App aperta → ricevi notifica
- [ ] App background → ricevi notifica
- [ ] App chiusa → ricevi notifica
- [ ] Tap notifica → navigazione corretta
- [ ] Permessi negati → mostra messaggio
- [ ] Timezone diverso → notifiche corrette
- [ ] Network offline → notifiche locali funzionano
- [ ] Backend down → fallback graceful

**Deliverables:**
- ✅ Tutti gli scenari testati
- ✅ Bug trovati documentati e fixati
- ✅ Test report compilato

---

#### **Giorno 15: Deployment Produzione** (1 giorno)
**Responsabile**: Full Team + DevOps
**Priority**: CRITICAL

**Pre-Deployment Checklist:**

**Backend:**
- [ ] Migrations eseguite su produzione
- [ ] Seeders NON eseguiti (no dati farlocchi in prod)
- [ ] Firebase credentials produzione configurate
- [ ] Cron job attivo e verificato
- [ ] API endpoint testati su produzione
- [ ] Logs monitoring attivi (Telescope)

**Flutter App:**
- [ ] Build produzione generata
  ```bash
  flutter build apk --release
  flutter build ios --release
  ```
- [ ] Firebase config produzione (`google-services.json`, `GoogleService-Info.plist`)
- [ ] App versioning incrementato (1.0.8+9)
- [ ] Changelog aggiornato
- [ ] Release notes preparate

**Deployment Steps:**

1. ✅ **Backend Deployment**
   ```bash
   ssh root@157.230.114.252
   cd /var/www/danzafacile

   # Pull latest code
   git pull origin main

   # Run migrations
   php artisan migrate --force

   # Clear caches
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear

   # Restart queue worker
   systemctl restart laravel-queue

   # Verify cron job
   crontab -l | grep schedule:run

   # Test API
   curl -X GET https://www.danzafacile.it/api/student/lessons/upcoming \
     -H "Authorization: Bearer $TOKEN"
   ```

2. ✅ **Flutter App Deployment**
   ```bash
   # Android: Upload su Play Store Internal Testing
   flutter build appbundle --release
   # Upload su Play Console

   # iOS: Upload su TestFlight
   flutter build ipa --release
   # Upload su App Store Connect
   ```

3. ✅ **Monitoring Post-Deployment**
   - Verificare Firebase Console (push sent)
   - Verificare Laravel logs (`tail -f /var/www/danzafacile/storage/logs/laravel.log`)
   - Verificare notification_logs table
   - Verificare analytics (quanti utenti attivano notifiche)

**Rollback Plan:**

In caso di problemi critici:

```bash
# Backend Rollback
ssh root@157.230.114.252
cd /var/www/danzafacile

# Rollback to previous git commit
git log --oneline | head -5  # Trova commit precedente
git reset --hard <commit_hash>

# Rollback migrations
php artisan migrate:rollback --step=4

# Restart services
systemctl restart nginx php8.4-fpm laravel-queue

# Flutter App Rollback
# Riattiva versione precedente su Play Store/TestFlight
```

**Deliverables:**
- ✅ Backend deployed in produzione
- ✅ Flutter app in beta testing (TestFlight/Play Internal)
- ✅ Monitoring attivo
- ✅ Rollback plan testato

---

## 🧪 STRATEGIA TESTING

### Test Environment (Scuola Test)

**Database:**
```sql
-- Flag per identificare scuola test
ALTER TABLE schools ADD COLUMN is_test_school BOOLEAN DEFAULT FALSE;
UPDATE schools SET is_test_school = TRUE WHERE id = <test_school_id>;

-- Query per isolare dati test
SELECT * FROM users WHERE school_id IN (SELECT id FROM schools WHERE is_test_school = TRUE);
```

**Seeder per Scuola Test:**
```bash
php artisan make:seeder TestSchoolSeeder
```

**Contenuto seeder:**
```php
class TestSchoolSeeder extends Seeder
{
    public function run()
    {
        // Creare scuola test
        $school = School::create([
            'name' => 'Scuola Test - DanzaFacile Dev',
            'is_test_school' => true,
            'email' => 'test@danzafacile.it',
        ]);

        // Admin test
        $admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin.test@danzafacile.it',
            'password' => Hash::make('TestDev2025!'),
            'role' => 'admin',
            'school_id' => $school->id,
        ]);

        // 10 studenti test
        for ($i = 1; $i <= 10; $i++) {
            User::create([
                'name' => "Studente Test {$i}",
                'email' => "studente{$i}@test.it",
                'password' => Hash::make('TestDev2025!'),
                'role' => 'student',
                'school_id' => $school->id,
            ]);
        }

        // 3 corsi test
        $courses = [];
        $courses[] = Course::create([
            'name' => 'Bachata Base',
            'school_id' => $school->id,
            'schedule' => 'Lunedì e Giovedì 19:00-20:30',
        ]);
        // ... altri corsi

        // Iscrivere studenti ai corsi
        // ... enrollments

        $this->command->info("✅ Scuola test creata con ID: {$school->id}");
    }
}
```

### Unit Testing

**Backend (PHPUnit):**
```bash
# Test models
php artisan test --filter LessonTest
php artisan test --filter NotificationPreferenceTest

# Test API endpoints
php artisan test --filter StudentLessonControllerTest
php artisan test --filter NotificationPreferenceControllerTest

# Test services
php artisan test --filter FirebasePushServiceTest

# Full test suite
php artisan test --coverage
```

**Flutter (flutter test):**
```bash
# Test entities
flutter test test/features/lessons/domain/entities/

# Test models
flutter test test/features/lessons/data/models/

# Test repositories
flutter test test/features/lessons/data/repositories/

# Test services
flutter test test/core/services/

# Widget tests
flutter test test/features/settings/presentation/

# Full suite
flutter test --coverage
```

### Integration Testing

**Flutter Integration Tests:**
```bash
# Creare test/integration_test/notification_flow_test.dart

flutter test integration_test/notification_flow_test.dart
```

**Scenario coperto:**
1. Login studente
2. Fetch upcoming lessons
3. Enable notification preferences
4. Schedule local notifications
5. Simulate notification received
6. Tap notification → verify navigation

---

## 📊 METRICHE DI SUCCESSO

### Technical KPIs

**Backend:**
- [ ] API response time: < 500ms (P95)
- [ ] Cron job execution: < 30 secondi
- [ ] Push delivery rate: > 95% (via Firebase Console)
- [ ] Database query performance: < 100ms per query
- [ ] Zero errori 500 su API endpoints

**Flutter App:**
- [ ] Local scheduling time: < 500ms per 50 notifiche
- [ ] App startup delay: < 200ms con NotificationService
- [ ] Memory overhead: < 10MB per notification service
- [ ] Notification delivery: 100% per local, > 95% per remote
- [ ] Permission grant rate: > 60%

### Business KPIs

- [ ] Attendance rate: +10% dopo implementazione
- [ ] User engagement: +20% aperture app da notifiche
- [ ] User satisfaction: > 4.0/5 rating per feature (in-app survey)
- [ ] Support tickets: -30% richieste "Ho dimenticato la lezione"

---

## 🚨 GESTIONE RISCHI

| Rischio | Probabilità | Impatto | Mitigazione | Piano B |
|---------|-------------|---------|-------------|---------|
| **Firebase quota exceeded** | Bassa | Alto | Monitor usage, throttling | Upgrade piano Firebase |
| **iOS 64 notifiche limit** | Alta | Medio | Schedule solo 7 giorni | Push remoti per remainder |
| **Cron job fallisce** | Bassa | Alto | Monitoring + alerting | Queue worker fallback |
| **Utenti negano permessi** | Media | Alto | Onboarding efficace | Email/SMS reminder (future) |
| **Timezone issues** | Media | Alto | Package timezone, test multi-TZ | Server-side scheduling |
| **Backend migration errori** | Bassa | Critico | Backup database pre-migration | Rollback migrations |
| **Conflitto scuola test/prod** | Bassa | Critico | Flag is_test_school | Separare database completamente |
| **App crash su notifica** | Media | Alto | Try-catch su handler | Graceful degradation |

---

## 📞 RESPONSABILITÀ E CONTATTI

### Team Roles

| Ruolo | Responsabile | Fasi Coinvolte | Contatto |
|-------|--------------|----------------|----------|
| **Backend Developer** | TBD | Settimana 1-2 (Backend + Firebase) | - |
| **Mobile Developer** | TBD | Settimana 2-3 (Flutter app) | - |
| **DevOps** | TBD | Firebase setup, server config, deployment | - |
| **QA Engineer** | TBD | Testing Giorno 14 | - |
| **Product Owner** | TBD | Approval requirements, beta feedback | - |

### Communication

- **Daily Standup**: 15 minuti ogni mattina (9:00)
- **Progress Updates**: Fine giornata su Slack/Email
- **Blocker Resolution**: Immediate notification
- **Code Reviews**: Prima di merge su main
- **Deployment Approval**: Richiede OK da PO + Tech Lead

---

## 📝 DELIVERABLES FINALI

### Documentazione

- [x] Questo piano operativo (`PUSH_NOTIFICATIONS_IMPLEMENTATION_PLAN.md`)
- [ ] API documentation (Postman collection)
- [ ] Database schema diagram
- [ ] Architecture diagram
- [ ] User guide per notifiche (italiano)
- [ ] Developer guide per manutenzione

### Codice

**Backend Laravel:**
- [ ] 4 migrations (lessons, preferences, tokens, logs)
- [ ] 4 models (Lesson, NotificationPreference, FcmToken, NotificationLog)
- [ ] 3 controllers (StudentLesson, NotificationPreference, FcmToken)
- [ ] 1 service (FirebasePushService)
- [ ] 1 command (SendLessonReminders)
- [ ] 2 seeders (Lesson, NotificationPreference)
- [ ] PHPUnit tests (coverage > 80%)

**Flutter App:**
- [ ] 2 entities (Lesson, NotificationPreferences)
- [ ] 2 models (LessonModel, NotificationPreferencesModel)
- [ ] 2 repositories (LessonRepository, NotificationPreferencesRepository)
- [ ] 2 services (LessonReminderService, NotificationPreferencesService)
- [ ] 2 screens (NotificationSettings, LessonsCalendar)
- [ ] Deep linking handler
- [ ] Unit tests (coverage > 80%)
- [ ] Widget tests
- [ ] Integration tests

### Deployment

- [ ] Backend deployed su server produzione
- [ ] Database migrations eseguite
- [ ] Cron job attivo e monitorato
- [ ] Flutter app su TestFlight (iOS)
- [ ] Flutter app su Play Internal Testing (Android)
- [ ] Monitoring dashboards configurati
- [ ] Rollback plan documentato e testato

---

## ✅ CHECKLIST PRE-DEPLOYMENT

### Backend

- [ ] Tutte le migrations eseguite senza errori
- [ ] Tutti i models hanno relationships corrette
- [ ] Tutte le API ritornano 200 su test
- [ ] Firebase Admin SDK invia notifiche correttamente
- [ ] Cron job eseguito manualmente con successo
- [ ] Database backup eseguito
- [ ] `.env` produzione verificato
- [ ] Logs monitoring attivo (Telescope/Laravel Log)
- [ ] Code review completato
- [ ] PHPUnit tests passano (100%)

### Flutter

- [ ] Tutte le dependencies installate
- [ ] Build runner generato codice (`.g.dart`)
- [ ] NotificationService inizializzato in main
- [ ] API integration testata con backend reale
- [ ] Local notifications funzionanti
- [ ] Remote push notifications ricevute
- [ ] Deep linking testato
- [ ] UI responsive su Android/iOS
- [ ] Unit tests passano (100%)
- [ ] Widget tests passano (100%)
- [ ] Integration tests passano (100%)
- [ ] Build release generato senza errori
- [ ] Firebase config produzione configurato
- [ ] App versioning incrementato
- [ ] Code review completato

### DevOps

- [ ] Server backup completo eseguito
- [ ] Firebase project production setup
- [ ] APNs certificates configurati (iOS)
- [ ] FCM server key configurato (Android)
- [ ] Monitoring dashboards pronti
- [ ] Alerting configurato (Slack/Email)
- [ ] Rollback procedure documentata
- [ ] Disaster recovery plan aggiornato

---

## 🎯 DEFINITION OF DONE

La feature "Push Notifications & Lesson Reminders" è considerata **DONE** quando:

### Funzionalità

- [ ] Studente riceve notifica locale 1 ora prima di ogni lezione (tempo configurabile)
- [ ] Studente può cambiare tempo reminder dalle impostazioni (15min-1 giorno)
- [ ] Studente può disabilitare notifiche completamente
- [ ] Tappare notifica apre dettaglio lezione corretta
- [ ] Backend invia push per lezioni upcoming ogni 15 minuti
- [ ] Backend invia push per cancellazioni/cambi lezione
- [ ] Notifiche funzionano su Android 10+ e iOS 14+
- [ ] Permessi OS gestiti correttamente

### Qualità

- [ ] Unit tests: > 80% coverage (backend + app)
- [ ] Widget tests: tutte le nuove screen
- [ ] Integration tests: scenari principali passano
- [ ] Manual testing: checklist completata
- [ ] Performance benchmarks raggiunti
- [ ] Zero crash su Crashlytics (24h monitoring)

### Deployment

- [ ] Backend deployed in produzione
- [ ] App in beta testing (10+ testers)
- [ ] Feedback beta positivo (> 4.0/5)
- [ ] Documentazione completa
- [ ] Analytics e monitoring attivi

---

## 📅 MILESTONE TRACKING

### Week 1 Milestone: Backend Foundation ✅
**Deadline**: Fine Settimana 1
**Criteria:**
- ✅ Database schema completo
- ✅ API endpoints funzionanti
- ✅ Test environment configurato
- ✅ Seeders con dati test

### Week 2 Milestone: Firebase + Flutter Domain ✅
**Deadline**: Fine Settimana 2
**Criteria:**
- ✅ Firebase Admin SDK funzionante
- ✅ Cron job attivo
- ✅ Flutter domain layer implementato
- ✅ API integration testata

### Week 3 Milestone: Production Ready ✅
**Deadline**: Fine Settimana 3
**Criteria:**
- ✅ UI completa e testata
- ✅ Testing end-to-end passato
- ✅ Beta deployment completato
- ✅ Monitoring attivo

---

## 🔄 ITERATION & FEEDBACK

### Beta Testing (Post-Deployment)

**Duration**: 7 giorni
**Participants**: 10-20 studenti reali
**Platforms**: TestFlight (iOS) + Play Internal Testing (Android)

**Feedback da raccogliere:**
1. Timing notifiche (troppo presto/tardi?)
2. Frequenza notifiche (troppe/poche?)
3. Contenuto messaggio (chiaro?)
4. UX settings screen (intuitivo?)
5. Bug/crash riscontrati

**Iterazione:**
- Fix bug critici: entro 24h
- Miglioramenti UX: pianificare per v1.1
- Feature requests: valutare priorità

---

## 📚 RISORSE & RIFERIMENTI

### Documentation

- [Firebase Cloud Messaging - Flutter](https://firebase.google.com/docs/cloud-messaging/flutter/client)
- [flutter_local_notifications Package](https://pub.dev/packages/flutter_local_notifications)
- [Timezone Package](https://pub.dev/packages/timezone)
- [Laravel Firebase Notifications](https://github.com/kreait/laravel-firebase)
- [Laravel Task Scheduling](https://laravel.com/docs/10.x/scheduling)

### Code Examples

- [FCM Flutter Codelab](https://firebase.google.com/codelabs/firebase-fcm-flutter)
- [Local Notifications Example](https://github.com/MaikuB/flutter_local_notifications/tree/master/flutter_local_notifications/example)

### Tools

- **Backend Testing**: Postman, Telescope
- **Mobile Testing**: Firebase Console, Android Studio, Xcode
- **Monitoring**: Firebase Crashlytics, Google Analytics
- **CI/CD**: GitHub Actions (future)

---

## 🎉 CONCLUSIONE

Questa roadmap operativa fornisce una guida **step-by-step completa** per implementare le notifiche push in modo professionale e sicuro.

### Key Takeaways

1. **Backend-First Approach**: Implementare backend prima di Flutter per avere API pronte
2. **Test Environment**: Usare scuola test per non corrompere dati produzione
3. **Dual-Mode Strategy**: Local + Remote notifications per massima affidabilità
4. **Rollback Ready**: Piano di rollback documentato e testato
5. **Monitoring Essential**: Logs e analytics attivi dal giorno 1

### Next Steps

1. ✅ **Review questo documento** con tutto il team
2. ✅ **Assegnare responsabilità** a ciascun developer
3. ✅ **Setup ambiente test** (Giorno 1-2)
4. ✅ **Daily standup** per tracking progress
5. ✅ **Go Live!** 🚀

---

**Documento creato**: 2025-11-15
**Versione**: 1.0.0
**Status**: ✅ READY FOR IMPLEMENTATION
**Prossima Review**: Fine Settimana 1 (Milestone 1)

---

**Happy Coding! 🎉**
