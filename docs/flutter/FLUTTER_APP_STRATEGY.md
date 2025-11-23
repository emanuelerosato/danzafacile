# Flutter App - Student Frontend Strategy
**Data:** 2025-10-02
**Progetto:** Scuola di Danza - App Mobile Studenti
**Fase:** Brainstorming & Planning

---

## 📊 STATO ATTUALE - COSA ABBIAMO

### ✅ **Backend API Ready (100%)**

**Authentication:**
- ✅ Login/Register/Logout
- ✅ Password reset/change
- ✅ Profile management
- ✅ Laravel Sanctum tokens

**Student Features:**
- ✅ Profile API (view, update, preferences)
- ✅ Courses API (browse, enrolled, recommendations)
- ✅ Enrollments API (enroll, cancel, history)
- ✅ Payments API (list, PayPal, receipts, status)
- ✅ Events API (list, register, my events)
- ✅ Attendance API (check-in, QR code, stats)
- ✅ Tickets API (create, list, reply, close)
- ✅ Documents API (upload, download, view)
- ✅ Galleries API (view public galleries, media)

**Total Student Endpoints:** ~50 endpoint pronti

---

## 🎯 COSA CI MANCA - ANALISI GAP

### **1. App Flutter NON ESISTE** ❌
- Nessun progetto Flutter creato
- Nessun repository separato
- Nessuna struttura iniziale

### **2. Design System** ⚠️
- ❌ Nessun design UI/UX
- ❌ Nessuna palette colori definita
- ❌ Nessun tema Material Design
- ℹ️ Possiamo replicare il design web (rose-pink-purple gradient)

### **3. Assets & Branding** ⚠️
- ❌ Logo app non definito
- ❌ Icons mancanti
- ❌ Splash screen non progettato
- ❌ App name & bundle identifier da definire

### **4. Testing Strategy** ❌
- Nessun piano di testing
- Nessun setup CI/CD per Flutter
- Nessun ambiente di test configurato

---

## 🗺️ ROADMAP SVILUPPO - PROPOSTA

### **FASE 0: Setup Iniziale (2-3 giorni)** 🔴
**Obiettivo:** Progetto Flutter pronto per sviluppo

**Task:**
1. Creare nuovo progetto Flutter
2. Configurare struttura cartelle (Clean Architecture)
3. Setup package manager (pubspec.yaml)
4. Configurare networking (Dio/http)
5. Implementare authentication flow
6. Setup state management (Provider/Riverpod/Bloc)
7. Creare design system base (colori, typography, widgets)
8. Configurare routing (go_router)

**Deliverable:**
- Progetto Flutter inizializzato
- Login/Register funzionante
- Token management implementato
- Navigazione base

---

### **FASE 1: MVP - Core Features (1-2 settimane)** 🟡
**Obiettivo:** App minima funzionante per testing

**Features:**
1. **Authentication** ✅
   - Login screen
   - Register screen
   - Forgot password
   - Splash screen

2. **Dashboard** ✅
   - Quick stats (corsi attivi, pagamenti pending, presenze)
   - Quick actions (check-in, paga, contatta)
   - Notifiche base

3. **Corsi** ✅
   - Lista corsi disponibili
   - Dettaglio corso
   - Iscrizione corso
   - I miei corsi

4. **Profilo** ✅
   - Visualizza profilo
   - Modifica dati
   - Cambio password
   - Logout

**Screens:** ~10 schermate
**API Used:** Auth, Profile, Courses, Dashboard

---

### **FASE 2: Payments & Documents (1 settimana)** 🟡
**Obiettivo:** Gestione pagamenti e documenti

**Features:**
1. **Pagamenti** ✅
   - Lista pagamenti
   - Dettaglio pagamento
   - Paga con PayPal
   - Storico pagamenti
   - Download ricevute

2. **Documenti** ✅
   - Lista documenti personali
   - Upload documento (foto, PDF)
   - Download documento
   - Stati: pending, approved, rejected

**Screens:** +5 schermate
**API Used:** Payments, Documents

---

### **FASE 3: Events & Attendance (1 settimana)** 🟢
**Obiettivo:** Gestione eventi e presenze

**Features:**
1. **Eventi** ✅
   - Lista eventi disponibili
   - Dettaglio evento
   - Registrazione evento
   - I miei eventi
   - Calendario

2. **Presenze** ✅
   - Check-in manuale
   - Check-in QR code
   - Storico presenze
   - Statistiche presenze

**Screens:** +6 schermate
**API Used:** Events, Attendance

---

### **FASE 4: Support & Galleries (5 giorni)** 🟢
**Obiettivo:** Supporto clienti e gallerie

**Features:**
1. **Tickets Support** ✅
   - Lista ticket personali
   - Crea ticket
   - Dettaglio ticket
   - Rispondi a ticket
   - Chiudi ticket

2. **Gallerie** ✅
   - Lista gallerie pubbliche
   - Visualizza foto/video
   - Lightbox/carousel
   - Share su social

**Screens:** +5 schermate
**API Used:** Tickets, Galleries

---

### **FASE 5: Polish & Release (1 settimana)** 🟢
**Obiettivo:** Preparazione rilascio

**Task:**
1. Testing completo (unit, widget, integration)
2. Performance optimization
3. Error handling & offline support
4. Push notifications setup
5. Analytics integration (Firebase)
6. App icons & splash screens
7. Store screenshots
8. Privacy policy & terms
9. Beta testing (TestFlight/Internal Testing)

**Deliverable:**
- App pronta per stores
- Documentazione utente
- Marketing materials

---

## 📱 SCHERMATE APP - LISTA COMPLETA

### **Authentication (4 schermate)**
1. Splash Screen
2. Login
3. Register
4. Forgot Password

### **Dashboard (1 schermata)**
5. Home Dashboard

### **Profilo (3 schermate)**
6. View Profile
7. Edit Profile
8. Change Password

### **Corsi (4 schermate)**
9. Browse Courses
10. Course Detail
11. My Courses
12. Enroll Course Confirmation

### **Pagamenti (4 schermate)**
13. Payments List
14. Payment Detail
15. PayPal Payment
16. Payment Success/Failure

### **Documenti (3 schermate)**
17. Documents List
18. Upload Document
19. Document Detail

### **Eventi (3 schermate)**
20. Events List
21. Event Detail
22. My Events

### **Presenze (3 schermate)**
23. Attendance History
24. Check-in (manual + QR)
25. Attendance Stats

### **Support (3 schermate)**
26. Tickets List
27. Create Ticket
28. Ticket Detail

### **Gallerie (2 schermate)**
29. Galleries List
30. Gallery Detail (con media viewer)

### **Settings (1 schermata)**
31. Settings (notifiche, lingua, privacy)

**TOTALE: ~31 schermate**

---

## 🏗️ ARCHITETTURA FLUTTER PROPOSTA

### **Clean Architecture con Feature-First**

```
lib/
├── core/
│   ├── constants/          # API URLs, app constants
│   ├── theme/             # Design system, colors, typography
│   ├── utils/             # Helper functions, validators
│   ├── widgets/           # Reusable widgets (buttons, cards, etc)
│   └── network/           # API client, interceptors
│
├── features/
│   ├── auth/
│   │   ├── data/          # Models, repositories
│   │   ├── domain/        # Entities, use cases
│   │   └── presentation/  # Screens, widgets, state
│   │
│   ├── courses/
│   │   ├── data/
│   │   ├── domain/
│   │   └── presentation/
│   │
│   ├── payments/
│   ├── documents/
│   ├── events/
│   ├── attendance/
│   ├── tickets/
│   ├── galleries/
│   └── profile/
│
├── routes/                # App routing (go_router)
└── main.dart
```

**Vantaggi:**
- ✅ Separazione chiara delle responsabilità
- ✅ Facile testing
- ✅ Scalabile per future features
- ✅ Riutilizzo codice

---

## 📦 FLUTTER PACKAGES NECESSARI

### **Core Dependencies:**
```yaml
dependencies:
  flutter:
    sdk: flutter

  # Networking
  dio: ^5.4.0                    # HTTP client
  retrofit: ^4.1.0               # Type-safe API client
  json_annotation: ^4.8.1        # JSON serialization

  # State Management
  flutter_riverpod: ^2.4.9       # State management (RACCOMANDATO)
  # O flutter_bloc: ^8.1.3       # Alternativa

  # Storage
  shared_preferences: ^2.2.2     # Token storage
  flutter_secure_storage: ^9.0.0 # Secure token storage

  # Navigation
  go_router: ^13.0.0             # Declarative routing

  # UI/UX
  cached_network_image: ^3.3.0   # Image caching
  flutter_svg: ^2.0.9            # SVG support
  shimmer: ^3.0.0                # Loading skeletons
  lottie: ^3.0.0                 # Animations

  # Forms & Validation
  flutter_form_builder: ^9.1.1
  form_builder_validators: ^9.1.0

  # Media
  image_picker: ^1.0.5           # Camera/gallery picker
  file_picker: ^6.1.1            # File picker
  photo_view: ^0.14.0            # Image viewer
  video_player: ^2.8.1           # Video playback

  # QR Code
  qr_flutter: ^4.1.0             # QR generation
  qr_code_scanner: ^1.0.1        # QR scanning

  # Payments
  flutter_paypal_payment: ^1.0.1 # PayPal integration
  # O webview_flutter: ^4.4.2    # WebView per PayPal

  # Utils
  intl: ^0.19.0                  # Internationalization
  timeago: ^3.6.0                # Relative time
  url_launcher: ^6.2.2           # Open URLs
  share_plus: ^7.2.1             # Share content

  # Error Tracking
  sentry_flutter: ^7.14.0        # Crash reporting (optional)

dev_dependencies:
  # Code Generation
  build_runner: ^2.4.7
  json_serializable: ^6.7.1
  retrofit_generator: ^8.0.6

  # Testing
  flutter_test:
    sdk: flutter
  mocktail: ^1.0.2               # Mocking

  # Linting
  flutter_lints: ^3.0.1
```

---

## 🔀 STRATEGIA GITHUB - OPZIONI

### **OPZIONE 1: Mono-Repository** 🟡
**Struttura:**
```
danzafacile/
├── backend/              # Laravel backend (esistente)
├── flutter_app/          # Flutter app studenti
├── admin_web/            # Admin dashboard (esistente)
└── docs/
```

**Vantaggi:**
- ✅ Tutto in un posto
- ✅ Facile sincronizzare API changes
- ✅ Unico issue tracker

**Svantaggi:**
- ❌ Repository molto grande
- ❌ CI/CD più complesso
- ❌ Checkout lento

---

### **OPZIONE 2: Repository Separato** 🟢 **RACCOMANDATO**
**Struttura:**
```
Repo 1: danzafacile (backend Laravel)
Repo 2: danzafacile-app (Flutter app)
```

**Vantaggi:**
- ✅ Repository leggero
- ✅ CI/CD dedicato per Flutter
- ✅ Team separati possono lavorare indipendentemente
- ✅ Rilasci indipendenti
- ✅ Checkout veloce

**Svantaggi:**
- ❌ Due repository da gestire
- ❌ Issue tracker separato

**Branch Strategy:**
```
main              # Production (store releases)
├── develop       # Development branch
├── feature/*     # Feature branches
├── hotfix/*      # Hotfix per production
└── release/*     # Release candidates
```

---

### **OPZIONE 3: Mono-Repo con Git Submodules** ⚠️
**Struttura:**
```
danzafacile/
├── backend/              # Git submodule
└── flutter_app/          # Git submodule
```

**Vantaggi:**
- ✅ Flessibile
- ✅ Repository separati ma linkati

**Svantaggi:**
- ❌ Complessità Git submodules
- ❌ Curva di apprendimento

---

## 🎨 DESIGN SYSTEM - PROPOSTA

### **Palette Colori (da Web)**
```dart
class AppColors {
  // Primary gradient (come web)
  static const rose = Color(0xFFF43F5E);      // rose-500
  static const purple = Color(0xFF9333EA);    // purple-600

  // Background gradient
  static const roseLight = Color(0xFFFFF1F2); // rose-50
  static const pinkLight = Color(0xFFFCE7F3); // pink-50
  static const purpleLight = Color(0xFFFAF5FF); // purple-50

  // Status colors
  static const success = Color(0xFF10B981);   // green-500
  static const warning = Color(0xFFF59E0B);   // yellow-500
  static const error = Color(0xFFEF4444);     // red-500
  static const info = Color(0xFF3B82F6);      // blue-500

  // Neutral
  static const gray900 = Color(0xFF111827);
  static const gray600 = Color(0xFF4B5563);
  static const gray300 = Color(0xFFD1D5DB);
  static const white = Color(0xFFFFFFFF);
}
```

### **Typography**
```dart
TextTheme(
  displayLarge: TextStyle(fontSize: 32, fontWeight: FontWeight.bold),
  headlineMedium: TextStyle(fontSize: 24, fontWeight: FontWeight.w600),
  titleLarge: TextStyle(fontSize: 20, fontWeight: FontWeight.w600),
  bodyLarge: TextStyle(fontSize: 16),
  bodyMedium: TextStyle(fontSize: 14),
  labelSmall: TextStyle(fontSize: 12),
)
```

---

## 🚀 DEPLOYMENT STRATEGY

### **Android:**
1. Google Play Console account (€25 one-time)
2. Bundle ID: `com.danzafacile.app`
3. Release: Internal Testing → Beta → Production

### **iOS:**
1. Apple Developer account ($99/year)
2. Bundle ID: `com.danzafacile.app`
3. TestFlight → App Store

### **CI/CD:**
- **GitHub Actions** (raccomandato, gratis per repo pubblici)
- **Codemagic** (Flutter-specific, 500 build/month gratis)
- **Fastlane** (automazione build & deploy)

---

## 📊 TIMELINE STIMATO

| Fase | Durata | Deliverable |
|------|--------|-------------|
| **FASE 0** Setup | 2-3 giorni | Progetto inizializzato + login |
| **FASE 1** MVP | 1-2 settimane | Dashboard + Corsi + Profilo |
| **FASE 2** Payments | 1 settimana | Pagamenti + Documenti |
| **FASE 3** Events | 1 settimana | Eventi + Presenze |
| **FASE 4** Support | 5 giorni | Tickets + Gallerie |
| **FASE 5** Release | 1 settimana | Testing + Store submission |
| **TOTALE** | **5-7 settimane** | App completa in stores |

**Con 1 sviluppatore full-time:** 5-7 settimane
**Con sviluppo part-time:** 10-12 settimane

---

## ✅ DECISIONI DA PRENDERE

### **1. Repository Strategy** 🔴 CRITICO
- [ ] Mono-repo
- [x] **Repository separato (RACCOMANDATO)**
- [ ] Submodules

**Decisione:** Creare `danzafacile-app` repository separato

---

### **2. State Management** 🔴 CRITICO
- [x] **Riverpod (RACCOMANDATO)** - Moderno, type-safe, testabile
- [ ] Bloc - Pattern completo ma verboso
- [ ] Provider - Semplice ma limitato
- [ ] GetX - Rapido ma antipattern

**Decisione:** Usare **Riverpod 2.x**

---

### **3. App Name** 🟡 IMPORTANTE
- [ ] "Scuola di Danza"
- [ ] "DanzaApp"
- [ ] "MyDanza"
- [ ] Altro: _______________

**Bundle ID:** `com.danzafacile.app`

---

### **4. Target Platforms** 🟡 IMPORTANTE
- [x] Android
- [x] iOS
- [ ] Web (future)

---

### **5. Minimum SDK** 🟢 BASSA
- Android: minSdkVersion 21 (Android 5.0)
- iOS: iOS 12.0+

---

## 🎯 NEXT STEPS IMMEDIATI

### **Step 1: Creare Repository GitHub** 🔴
```bash
# Opzione A: Nuovo repo separato
gh repo create danzafacile-app --public
cd ../
flutter create danzafacile_app
cd danzafacile_app
git init
git remote add origin https://github.com/emanuelerosato/danzafacile-app.git

# Opzione B: Cartella nel repo esistente
cd /Users/emanuele/Sites/danzafacile
flutter create flutter_app
```

### **Step 2: Configurare Progetto Flutter**
```bash
flutter create --org com.danzafacile danzafacile_app
cd danzafacile_app

# Aggiungere dependencies base
flutter pub add dio flutter_riverpod go_router shared_preferences
flutter pub add --dev build_runner json_serializable

# Creare struttura cartelle
mkdir -p lib/core/{constants,theme,utils,widgets,network}
mkdir -p lib/features/{auth,courses,payments,profile}/{data,domain,presentation}
mkdir -p lib/routes
```

### **Step 3: Creare Design System**
- Definire `app_colors.dart`
- Definire `app_theme.dart`
- Creare widget base (AppButton, AppCard, AppTextField)

### **Step 4: Implementare Auth Flow**
- Login screen
- Token storage (secure_storage)
- API client con Dio + interceptors
- Auth state management (Riverpod)

---

## 📝 DOMANDE APERTE

1. **Quale nome preferisci per l'app?**
2. **Vuoi mono-repo o repo separato?** (consiglio separato)
3. **Hai account Google Play / Apple Developer?**
4. **Vuoi implementare push notifications subito?**
5. **Serve supporto multilingua (EN/IT)?**
6. **Budget per servizi esterni?** (Firebase, Sentry, etc)

---

## 💡 RACCOMANDAZIONI FINALI

### **DO:**
✅ Inizia con MVP (FASE 1) per feedback rapido
✅ Usa Riverpod per state management
✅ Repository separato per Flutter app
✅ Implementa error handling & offline support da subito
✅ Test automatici per logica business
✅ CI/CD con GitHub Actions

### **DON'T:**
❌ Non implementare tutte le feature subito
❌ Non sottovalutare testing
❌ Non dimenticare error states nelle UI
❌ Non hardcodare API URLs
❌ Non committare secrets (API keys)

---

**PROSSIMO PASSO:**
Decidere strategia repository e creare progetto Flutter iniziale con struttura base.
