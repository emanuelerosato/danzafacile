# Scuola di Danza - Flutter App
## Documentazione Iniziale Progetto Mobile

**Repository App:** https://github.com/emanuelerosato/danzafacile-app
**Repository Backend:** https://github.com/emanuelerosato/danzafacile

**Data Creazione:** 2025-10-02
**Versione:** 1.0.0 (Development)
**Piattaforme:** Android, iOS

---

## 📱 OVERVIEW PROGETTO

**Scuola di Danza Mobile App** è l'applicazione mobile Flutter per studenti della scuola di danza. Permette agli studenti di:

- 📚 Sfogliare e iscriversi ai corsi
- 💳 Gestire pagamenti (PayPal integrato)
- 📄 Caricare e visualizzare documenti
- 📅 Registrarsi agli eventi
- ✅ Fare check-in alle lezioni (QR code)
- 🎫 Creare ticket di supporto
- 🖼️ Visualizzare gallerie foto/video
- 👤 Gestire il proprio profilo

---

## 🏗️ ARCHITETTURA

### **Clean Architecture + Feature-First**

```
danzafacile_app/
├── lib/
│   ├── core/
│   │   ├── constants/
│   │   │   ├── api_constants.dart       # API URLs, endpoints
│   │   │   ├── app_constants.dart       # App constants
│   │   │   └── storage_constants.dart   # Storage keys
│   │   │
│   │   ├── theme/
│   │   │   ├── app_colors.dart          # Palette colori
│   │   │   ├── app_theme.dart           # Theme configuration
│   │   │   └── app_text_styles.dart     # Typography
│   │   │
│   │   ├── widgets/
│   │   │   ├── app_button.dart
│   │   │   ├── app_card.dart
│   │   │   ├── app_text_field.dart
│   │   │   ├── loading_indicator.dart
│   │   │   ├── error_widget.dart
│   │   │   └── empty_state.dart
│   │   │
│   │   ├── utils/
│   │   │   ├── validators.dart          # Form validators
│   │   │   ├── formatters.dart          # Date, currency formatters
│   │   │   └── extensions.dart          # Dart extensions
│   │   │
│   │   └── network/
│   │       ├── api_client.dart          # Dio client
│   │       ├── auth_interceptor.dart    # Token interceptor
│   │       ├── error_handler.dart       # Error handling
│   │       └── api_response.dart        # Response model
│   │
│   ├── features/
│   │   ├── auth/
│   │   │   ├── data/
│   │   │   │   ├── models/
│   │   │   │   │   ├── user_model.dart
│   │   │   │   │   └── login_response.dart
│   │   │   │   ├── datasources/
│   │   │   │   │   └── auth_remote_datasource.dart
│   │   │   │   └── repositories/
│   │   │   │       └── auth_repository_impl.dart
│   │   │   │
│   │   │   ├── domain/
│   │   │   │   ├── entities/
│   │   │   │   │   └── user.dart
│   │   │   │   ├── repositories/
│   │   │   │   │   └── auth_repository.dart
│   │   │   │   └── usecases/
│   │   │   │       ├── login_usecase.dart
│   │   │   │       ├── register_usecase.dart
│   │   │   │       └── logout_usecase.dart
│   │   │   │
│   │   │   └── presentation/
│   │   │       ├── providers/
│   │   │       │   └── auth_provider.dart
│   │   │       ├── screens/
│   │   │       │   ├── splash_screen.dart
│   │   │       │   ├── login_screen.dart
│   │   │       │   ├── register_screen.dart
│   │   │       │   └── forgot_password_screen.dart
│   │   │       └── widgets/
│   │   │           ├── login_form.dart
│   │   │           └── register_form.dart
│   │   │
│   │   ├── courses/
│   │   │   ├── data/
│   │   │   ├── domain/
│   │   │   └── presentation/
│   │   │
│   │   ├── payments/
│   │   │   ├── data/
│   │   │   ├── domain/
│   │   │   └── presentation/
│   │   │
│   │   ├── documents/
│   │   ├── events/
│   │   ├── attendance/
│   │   ├── tickets/
│   │   ├── galleries/
│   │   ├── profile/
│   │   └── dashboard/
│   │
│   ├── routes/
│   │   ├── app_router.dart              # go_router configuration
│   │   └── route_constants.dart         # Route names
│   │
│   └── main.dart                         # Entry point
│
├── test/
│   ├── unit/
│   ├── widget/
│   └── integration/
│
├── assets/
│   ├── images/
│   ├── icons/
│   └── fonts/
│
├── android/
├── ios/
├── pubspec.yaml
└── README.md
```

---

## 🔧 TECH STACK

### **Core**
- **Flutter:** 3.19+ (stable channel)
- **Dart:** 3.3+
- **Minimum SDK:**
  - Android: API 21 (Android 5.0)
  - iOS: iOS 12.0+

### **State Management**
- **flutter_riverpod** ^2.4.9 - State management reattivo e type-safe

### **Networking**
- **dio** ^5.4.0 - HTTP client
- **retrofit** ^4.1.0 - Type-safe REST client
- **json_annotation** ^4.8.1 - JSON serialization

### **Navigation**
- **go_router** ^13.0.0 - Declarative routing

### **Storage**
- **shared_preferences** ^2.2.2 - Local storage
- **flutter_secure_storage** ^9.0.0 - Secure token storage

### **UI/UX**
- **cached_network_image** ^3.3.0 - Image caching
- **flutter_svg** ^2.0.9 - SVG support
- **shimmer** ^3.0.0 - Loading skeletons
- **lottie** ^3.0.0 - Animations
- **photo_view** ^0.14.0 - Image viewer

### **Forms**
- **flutter_form_builder** ^9.1.1
- **form_builder_validators** ^9.1.0

### **Media**
- **image_picker** ^1.0.5 - Camera/gallery picker
- **file_picker** ^6.1.1 - File picker
- **video_player** ^2.8.1 - Video playback

### **QR Code**
- **qr_flutter** ^4.1.0 - QR generation
- **qr_code_scanner** ^1.0.1 - QR scanning

### **Payments**
- **flutter_paypal_payment** ^1.0.1 - PayPal integration

### **Utils**
- **intl** ^0.19.0 - Internationalization
- **timeago** ^3.6.0 - Relative time
- **url_launcher** ^6.2.2 - Open URLs
- **share_plus** ^7.2.1 - Share content

---

## 🎨 DESIGN SYSTEM

### **Palette Colori**

```dart
class AppColors {
  // Primary Gradient (da design web)
  static const rose = Color(0xFFF43F5E);        // rose-500
  static const purple = Color(0xFF9333EA);      // purple-600

  // Background Gradient
  static const roseLight = Color(0xFFFFF1F2);   // rose-50
  static const pinkLight = Color(0xFFFCE7F3);   // pink-50
  static const purpleLight = Color(0xFFFAF5FF); // purple-50

  // Status Colors
  static const success = Color(0xFF10B981);     // green-500
  static const warning = Color(0xFFF59E0B);     // yellow-500
  static const error = Color(0xFFEF4444);       // red-500
  static const info = Color(0xFF3B82F6);        // blue-500

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
  displayLarge: TextStyle(
    fontSize: 32,
    fontWeight: FontWeight.bold,
    color: AppColors.gray900,
  ),
  headlineMedium: TextStyle(
    fontSize: 24,
    fontWeight: FontWeight.w600,
    color: AppColors.gray900,
  ),
  titleLarge: TextStyle(
    fontSize: 20,
    fontWeight: FontWeight.w600,
    color: AppColors.gray900,
  ),
  bodyLarge: TextStyle(
    fontSize: 16,
    color: AppColors.gray900,
  ),
  bodyMedium: TextStyle(
    fontSize: 14,
    color: AppColors.gray600,
  ),
  labelSmall: TextStyle(
    fontSize: 12,
    color: AppColors.gray600,
  ),
)
```

---

## 🚀 GETTING STARTED

### **Prerequisites**

1. **Flutter SDK 3.19+**
   ```bash
   flutter --version
   ```

2. **Android Studio** (per Android)
   - Android SDK
   - Android Emulator

3. **Xcode** (per iOS, solo macOS)
   - Command Line Tools
   - iOS Simulator

### **Setup Progetto**

#### **1. Clone Repository**
```bash
git clone https://github.com/emanuelerosato/danzafacile-app.git
cd danzafacile-app
```

#### **2. Install Dependencies**
```bash
flutter pub get
```

#### **3. Generate Code (models, routes)**
```bash
flutter pub run build_runner build --delete-conflicting-outputs
```

#### **4. Configurare Backend URL**

Creare file `lib/core/constants/api_constants.dart`:

```dart
class ApiConstants {
  // Development
  static const String baseUrlDev = 'http://localhost:8089/api/mobile/v1';

  // Production
  static const String baseUrlProd = 'https://api.danzafacile.com/api/mobile/v1';

  // Current environment
  static const String baseUrl = baseUrlDev; // Change in production

  // Endpoints
  static const String login = '/auth/login';
  static const String register = '/auth/register';
  static const String logout = '/auth/logout';
  static const String profile = '/auth/me';

  static const String courses = '/student/courses';
  static const String enrollments = '/student/enrollments';
  static const String payments = '/student/payments';
  static const String events = '/events';
  static const String attendance = '/attendance';
  static const String tickets = '/tickets';
  static const String documents = '/documents';
  static const String galleries = '/galleries';
}
```

#### **5. Run App**

**Android:**
```bash
flutter run
```

**iOS:**
```bash
flutter run -d ios
```

**Web (development):**
```bash
flutter run -d chrome
```

---

## 🔐 AUTHENTICATION FLOW

### **Token Management**

L'app usa **Laravel Sanctum** per l'autenticazione. Il flow è:

1. **Login:** POST `/auth/login` → riceve token
2. **Store Token:** Salva in `flutter_secure_storage`
3. **Interceptor:** Aggiunge token ad ogni richiesta
4. **Refresh:** Se 401, refresh token o logout
5. **Logout:** POST `/auth/logout` → cancella token

### **Esempio Implementazione**

```dart
class AuthInterceptor extends Interceptor {
  final FlutterSecureStorage _secureStorage;

  AuthInterceptor(this._secureStorage);

  @override
  void onRequest(
    RequestOptions options,
    RequestInterceptorHandler handler,
  ) async {
    final token = await _secureStorage.read(key: 'auth_token');

    if (token != null) {
      options.headers['Authorization'] = 'Bearer $token';
    }

    return handler.next(options);
  }

  @override
  void onError(DioException err, ErrorInterceptorHandler handler) async {
    if (err.response?.statusCode == 401) {
      // Token expired - logout user
      await _secureStorage.delete(key: 'auth_token');
      // Navigate to login
    }

    return handler.next(err);
  }
}
```

---

## 📱 FEATURES & SCREENS

### **FASE 1: MVP (12 schermate)**

#### **Authentication (4)**
1. Splash Screen
2. Login Screen
3. Register Screen
4. Forgot Password Screen

#### **Dashboard (1)**
5. Home Dashboard
   - Quick stats (corsi attivi, pagamenti, presenze)
   - Quick actions (check-in, paga, contatta)
   - Notifiche

#### **Courses (4)**
6. Browse Courses (lista con filtri)
7. Course Detail (dettaglio + iscrizione)
8. My Courses (corsi iscritto)
9. Enroll Confirmation

#### **Profile (3)**
10. View Profile
11. Edit Profile
12. Change Password

### **FASE 2: Payments & Documents (7 schermate)**

#### **Payments (4)**
13. Payments List
14. Payment Detail
15. PayPal Payment
16. Payment Success/Failure

#### **Documents (3)**
17. Documents List
18. Upload Document
19. Document Detail

### **FASE 3: Events & Attendance (6 schermate)**

#### **Events (3)**
20. Events List
21. Event Detail
22. My Events

#### **Attendance (3)**
23. Attendance History
24. Check-in (manual + QR)
25. Attendance Stats

### **FASE 4: Support & Galleries (5 schermate)**

#### **Tickets (3)**
26. Tickets List
27. Create Ticket
28. Ticket Detail

#### **Galleries (2)**
29. Galleries List
30. Gallery Detail (con media viewer)

### **Settings (1)**
31. Settings Screen

**TOTALE: 31 schermate**

---

## 🔗 API INTEGRATION

### **Base API Client**

```dart
class ApiClient {
  late final Dio _dio;
  final FlutterSecureStorage _secureStorage;

  ApiClient(this._secureStorage) {
    _dio = Dio(
      BaseOptions(
        baseUrl: ApiConstants.baseUrl,
        connectTimeout: const Duration(seconds: 30),
        receiveTimeout: const Duration(seconds: 30),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      ),
    );

    // Add interceptors
    _dio.interceptors.add(AuthInterceptor(_secureStorage));
    _dio.interceptors.add(LogInterceptor(
      requestBody: true,
      responseBody: true,
    ));
  }

  Dio get dio => _dio;
}
```

### **API Response Model**

```dart
class ApiResponse<T> {
  final bool success;
  final T? data;
  final String? message;
  final Map<String, dynamic>? errors;

  ApiResponse({
    required this.success,
    this.data,
    this.message,
    this.errors,
  });

  factory ApiResponse.fromJson(
    Map<String, dynamic> json,
    T Function(dynamic)? fromJsonT,
  ) {
    return ApiResponse(
      success: json['success'] ?? false,
      data: json['data'] != null && fromJsonT != null
          ? fromJsonT(json['data'])
          : null,
      message: json['message'],
      errors: json['errors'],
    );
  }
}
```

---

## 📊 STATE MANAGEMENT (Riverpod)

### **Esempio: Auth Provider**

```dart
@riverpod
class AuthNotifier extends _$AuthNotifier {
  @override
  Future<User?> build() async {
    // Load user from storage on app start
    final token = await ref.read(storageProvider).getToken();
    if (token != null) {
      return ref.read(authRepositoryProvider).getCurrentUser();
    }
    return null;
  }

  Future<void> login(String email, String password) async {
    state = const AsyncLoading();

    state = await AsyncValue.guard(() async {
      final response = await ref.read(authRepositoryProvider).login(
        email: email,
        password: password,
      );

      await ref.read(storageProvider).saveToken(response.token);
      return response.user;
    });
  }

  Future<void> logout() async {
    await ref.read(authRepositoryProvider).logout();
    await ref.read(storageProvider).clearToken();
    state = const AsyncValue.data(null);
  }
}
```

---

## 🧪 TESTING

### **Unit Tests**
```bash
flutter test test/unit/
```

### **Widget Tests**
```bash
flutter test test/widget/
```

### **Integration Tests**
```bash
flutter test integration_test/
```

### **Coverage**
```bash
flutter test --coverage
genhtml coverage/lcov.info -o coverage/html
open coverage/html/index.html
```

---

## 🚢 BUILD & DEPLOYMENT

### **Android APK (Debug)**
```bash
flutter build apk --debug
```

### **Android App Bundle (Release)**
```bash
flutter build appbundle --release
```

### **iOS IPA (Release)**
```bash
flutter build ipa --release
```

### **Versioning**
Edit `pubspec.yaml`:
```yaml
version: 1.0.0+1  # version+buildNumber
```

---

## 🔄 CI/CD (GitHub Actions)

### **Workflow File:** `.github/workflows/flutter.yml`

```yaml
name: Flutter CI

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main, develop ]

jobs:
  build-android:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - uses: subosito/flutter-action@v2
        with:
          flutter-version: '3.19.0'

      - run: flutter pub get
      - run: flutter analyze
      - run: flutter test
      - run: flutter build apk --release

  build-ios:
    runs-on: macos-latest
    steps:
      - uses: actions/checkout@v3
      - uses: subosito/flutter-action@v2
        with:
          flutter-version: '3.19.0'

      - run: flutter pub get
      - run: flutter analyze
      - run: flutter test
      - run: flutter build ios --release --no-codesign
```

---

## 📝 CONTRIBUTING

### **Branch Strategy**
- `main` - Production (store releases)
- `develop` - Development (staging)
- `feature/*` - Feature branches
- `hotfix/*` - Hotfix branches

### **Workflow**
1. Create feature branch from `develop`
2. Implement feature
3. Write tests
4. Create Pull Request to `develop`
5. Code review
6. Merge to `develop`
7. Release: merge `develop` → `main`

### **Commit Convention**
```
feat: Add payment history screen
fix: Fix login validation
refactor: Refactor auth provider
docs: Update README
test: Add course tests
style: Format code
```

---

## 🐛 TROUBLESHOOTING

### **Common Issues**

**1. "Gradle build failed"**
```bash
cd android
./gradlew clean
cd ..
flutter clean
flutter pub get
```

**2. "CocoaPods not installed" (iOS)**
```bash
sudo gem install cocoapods
cd ios
pod install
```

**3. "Certificate error" (iOS)**
- Open Xcode
- Select project
- Signing & Capabilities
- Select team

**4. "Network error" in emulator**
- Android: Use `10.0.2.2` instead of `localhost`
- iOS: Use computer's local IP

---

## 📚 RESOURCES

### **Documentation**
- [Flutter Docs](https://docs.flutter.dev/)
- [Riverpod Docs](https://riverpod.dev/)
- [Go Router Docs](https://pub.dev/packages/go_router)
- [Dio Docs](https://pub.dev/packages/dio)

### **Backend API**
- [API Documentation](/API_ENDPOINTS.md)
- [API Coverage Report](/API_COVERAGE_REPORT.md)
- Backend Repository: https://github.com/emanuelerosato/danzafacile

### **Design**
- [Flutter App Strategy](/FLUTTER_APP_STRATEGY.md)
- Material Design: https://m3.material.io/

---

## 📞 SUPPORT

**Issues:** https://github.com/emanuelerosato/danzafacile-app/issues
**Backend Issues:** https://github.com/emanuelerosato/danzafacile/issues

---

## 📄 LICENSE

Copyright © 2025 Scuola di Danza. All rights reserved.

---

**Last Updated:** 2025-10-02
**Version:** 1.0.0 (Initial Setup)
