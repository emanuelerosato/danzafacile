# 📱 Flutter Week 2 - Progress Report

**Data Inizio:** 2025-11-16
**Status:** 🚧 IN PROGRESS (40% Complete)

---

## ✅ Completato (Day 1 - 40%)

### 1. Lesson Model Alignment
- ✅ Updated `LessonModel` to parse backend datetime correctly
- ✅ Fixed `start_time` and `end_time` extraction (datetime → "HH:mm")
- ✅ Removed fields not returned by backend (`max_capacity`, `enrolled_count`)
- ✅ Build runner executed successfully

**File**: `lib/features/lessons/data/models/lesson_model.dart`

### 2. API Service Enhancement
- ✅ Created `ApiResponse` and `ApiListResponse` wrapper models
- ✅ Added 8 new API endpoints to `ApiService`:
  - 4 Lesson endpoints (upcoming, by-id, by-date, all)
  - 2 Notification Preferences endpoints (get, update)
  - 2 FCM Token endpoints (register, delete)
- ✅ Build runner generated retrofit code

**Files**:
- `lib/core/api/api_service.dart`
- `lib/core/api/api_response.dart`

### 3. API Constants Update
- ✅ Updated lesson endpoints to `/mobile/v1/student/lessons/*`
- ✅ Updated notification endpoints to `/mobile/v1/notifications/*`
- ✅ Added helper methods for dynamic paths (by-id, by-date)

**File**: `lib/core/constants/api_constants.dart`

### 4. Lesson Notification Scheduler Service
- ✅ Created complete service for local notification scheduling
- ✅ Timezone support (Europe/Rome)
- ✅ Methods implemented:
  - `scheduleForLesson()` - Schedule single lesson
  - `scheduleForLessons()` - Batch schedule
  - `cancelForLesson()` - Cancel specific
  - `cancelAll()` - Cancel all
  - `rescheduleAll()` - Update when preferences change
  - `getPendingNotifications()` - List scheduled
  - `isScheduled()` - Check status

**File**: `lib/core/services/lesson_notification_scheduler.dart`

**Features**:
- Offline-first scheduling (7 days ahead)
- Smart notification ID generation
- Payload for deep linking
- Android + iOS notification details
- Skip past lessons automatically
- Skip non-scheduled lessons

---

## 🚧 In Progress / Pending (60%)

### 5. Lessons Repository Implementation
**Status**: ⏳ PENDING
**Estimated**: 30 minutes

**Task**: Update `LessonsRepositoryImpl` to parse new backend response format.

**Current Issue**:
```dart
// Current (WRONG):
final lessonsData = response.data['data'] as List;

// Should be (CORRECT):
final apiResponse = ApiListResponse<LessonModel>.fromJson(
  response.data,
  (json) => LessonModel.fromJson(json as Map<String, dynamic>),
);
final lessons = apiResponse.data?.map((m) => m.toEntity()).toList() ?? [];
```

**Files to Update**:
- `lib/features/lessons/data/repositories/lessons_repository_impl.dart`

---

### 6. FCM Token Registration
**Status**: ⏳ PENDING
**Estimated**: 45 minutes

**Task**: Auto-register FCM token on app start and login.

**Implementation**:
```dart
// 1. Create helper service
class FcmTokenService {
  Future<void> registerToken(String token) async {
    final deviceInfo = await DeviceInfoPlugin().androidInfo; // or iosInfo
    await apiService.registerFcmToken({
      'token': token,
      'device_type': Platform.isAndroid ? 'android' : 'ios',
      'device_id': deviceInfo.id,
    });
  }
}

// 2. Call on app initialization (main.dart or auth provider)
notificationService.initialize();
final token = notificationService.fcmToken;
if (token != null) {
  await fcmTokenService.registerToken(token);
}
```

**Files to Create/Update**:
- `lib/core/services/fcm_token_service.dart` (NEW)
- `lib/main.dart` (update initialization)
- `lib/features/auth/presentation/providers/auth_provider.dart` (call on login)

**Dependencies Needed**:
```yaml
device_info_plus: ^10.1.2  # Add to pubspec.yaml
```

---

### 7. Notification Preferences Provider Logic
**Status**: ⏳ PENDING
**Estimated**: 1 hour

**Task**: Integrate scheduler with preferences provider.

**Implementation**:
```dart
class NotificationPreferencesProvider extends StateNotifier {
  final LessonNotificationScheduler _scheduler;
  final LessonsRepository _lessonsRepo;

  // On load preferences
  Future<void> loadPreferences() async {
    final prefs = await _api.getNotificationPreferences();
    state = prefs;

    // Schedule notifications if enabled
    if (prefs.shouldSendLessonReminders) {
      final lessons = await _lessonsRepo.getUpcomingLessons(days: 7);
      await _scheduler.scheduleForLessons(
        lessons,
        prefs.reminderMinutesBefore,
      );
    }
  }

  // On update preferences
  Future<void> updatePreferences(Map<String, dynamic> updates) async {
    final oldPrefs = state;
    final newPrefs = await _api.updateNotificationPreferences(updates);

    // Reschedule if timing changed
    if (oldPrefs.reminderMinutesBefore != newPrefs.reminderMinutesBefore) {
      final lessons = await _lessonsRepo.getUpcomingLessons(days: 7);
      await _scheduler.rescheduleAll(
        lessons,
        newPrefs.reminderMinutesBefore,
        oldPrefs.reminderMinutesBefore,
      );
    }

    state = newPrefs;
  }
}
```

**Files to Update**:
- `lib/features/notifications/presentation/providers/notification_preferences_provider.dart`

---

### 8. Notification Settings UI
**Status**: ⏳ PENDING
**Estimated**: 1 hour

**Task**: Update settings screen with 5 reminder time options.

**UI Requirements**:
- Master toggle: Enable/Disable all notifications
- Lesson reminders toggle
- Reminder timing picker: [15, 30, 60, 120, 1440] minutes
- Event reminders toggle
- Payment reminders toggle
- System notifications toggle
- Save button with loading state

**Files to Update**:
- `lib/features/notifications/presentation/screens/notification_settings_screen.dart`

**Design Pattern**: Follow existing app design (rose/purple gradient, cards, etc.)

---

### 9. Notification Tap Handling (Deep Linking)
**Status**: ⏳ PENDING
**Estimated**: 45 minutes

**Task**: Navigate to lesson detail when notification tapped.

**Implementation**:
```dart
// 1. Register callback in main.dart
void setupNotificationHandling(GoRouter router) {
  notificationService.setNotificationTapCallback((payload) {
    final parsed = LessonNotificationScheduler.parsePayload(payload);
    if (parsed != null) {
      router.go('/lessons/${parsed['lessonId']}');
    }
  });
}

// 2. Create lesson detail route in GoRouter
GoRoute(
  path: '/lessons/:id',
  builder: (context, state) {
    final lessonId = state.pathParameters['id']!;
    return LessonDetailScreen(lessonId: lessonId);
  },
),
```

**Files to Update**:
- `lib/main.dart` - Setup callback
- `lib/core/routing/app_router.dart` - Add lesson detail route
- `lib/features/lessons/presentation/screens/lesson_detail_screen.dart` - Create screen

---

### 10. Testing End-to-End
**Status**: ⏳ PENDING
**Estimated**: 2 hours

**Test Scenarios**:

1. **Login Flow**
   - [ ] Login successful
   - [ ] FCM token registered to backend
   - [ ] Notification preferences loaded
   - [ ] Upcoming lessons fetched (7 days)
   - [ ] Local notifications scheduled

2. **Preference Changes**
   - [ ] Change reminder time (60min → 120min)
   - [ ] Old notifications cancelled
   - [ ] New notifications scheduled
   - [ ] Verify in pending notifications list

3. **Notification Received**
   - [ ] Wait for scheduled notification (or use debug trigger)
   - [ ] Notification appears in notification tray
   - [ ] Tap notification
   - [ ] App opens to lesson detail screen
   - [ ] Correct lesson loaded

4. **Firebase Push (Remote)**
   - [ ] Backend sends test push (via tinker)
   - [ ] App receives push (foreground + background)
   - [ ] Local notification shown
   - [ ] Tap handling works

5. **Edge Cases**
   - [ ] No internet → local notifications still work
   - [ ] Past lessons → no notifications scheduled
   - [ ] Cancelled lessons → no notifications
   - [ ] Multiple devices → each gets token

---

## 📊 Overall Progress

| Task | Status | Estimated Time | Actual Time |
|------|--------|---------------|-------------|
| 1. Model Alignment | ✅ | 30 min | 45 min |
| 2. API Service | ✅ | 1 hour | 1.5 hours |
| 3. API Constants | ✅ | 15 min | 10 min |
| 4. Scheduler Service | ✅ | 2 hours | 2 hours |
| 5. Repository Update | ⏳ | 30 min | - |
| 6. FCM Token Service | ⏳ | 45 min | - |
| 7. Preferences Provider | ⏳ | 1 hour | - |
| 8. Settings UI | ⏳ | 1 hour | - |
| 9. Deep Linking | ⏳ | 45 min | - |
| 10. Testing | ⏳ | 2 hours | - |
| **TOTAL** | **40%** | **~10 hours** | **~4 hours** |

**Remaining**: ~6 hours estimated

---

## 🎯 Next Steps (Priority Order)

1. **Update LessonsRepository** (30 min) - CRITICAL
   - Fix response parsing
   - Test with real API

2. **Create FCM Token Service** (45 min) - CRITICAL
   - Auto-register on app start
   - Re-register on login

3. **Update Preferences Provider** (1 hour) - HIGH
   - Load and schedule on init
   - Reschedule on preference change

4. **Update Settings UI** (1 hour) - MEDIUM
   - 5 reminder time options
   - Toggle switches
   - Save/loading states

5. **Implement Deep Linking** (45 min) - MEDIUM
   - GoRouter lesson detail route
   - Notification tap callback
   - Payload parsing

6. **Test on Real Device** (2 hours) - HIGH
   - Full flow testing
   - Edge cases
   - Firebase push testing

---

## 🚀 Deployment Readiness

### Prerequisites for Testing
- [ ] Firebase files configured (`google-services.json`, `GoogleService-Info.plist`)
- [ ] Backend API operational (https://www.danzafacile.it/api/mobile/v1/*)
- [ ] Test credentials available (`studente1@test.pushnotif.local`)
- [ ] Real Android/iOS device for testing
- [ ] Firebase Console access for push monitoring

### Build Commands
```bash
# Android
flutter build apk --release --dart-define-from-file=.env.prod

# iOS
flutter build ipa --release --dart-define-from-file=.env.prod
```

---

## 📝 Notes

### Known Issues
- ⚠️ Analyzer version warning (3.4.0 vs SDK 3.9.0) - non-blocking
- ⚠️ 119 dependencies have newer versions - consider upgrade dopo Week 2

### Firebase Files Required
```
android/app/google-services.json
ios/Runner/GoogleService-Info.plist
```

**Status**: ❓ Da verificare se presenti

### Environment Configuration
- Development: `.env.dev`
- Production: `.env.prod`

**Verify**: API base URL points to production (https://www.danzafacile.it/api)

---

**Last Updated**: 2025-11-16 (Day 1, 40% complete)
**Next Session**: Continue with Task 5 (Repository Update)
**Estimated Completion**: +6 hours (~1.5 giorni)
