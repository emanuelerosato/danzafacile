# 📱 Flutter Week 2 - Day 1 FINAL REPORT

**Data:** 2025-11-16
**Tempo Effettivo:** ~5 ore
**Progress:** 60% Complete (6/10 tasks)

---

## ✅ COMPLETATO (6 Tasks)

### 1. Lesson Model Alignment ✅
**File**: `lib/features/lessons/data/models/lesson_model.dart`

**Changes**:
- Fixed datetime parsing: backend sends `"2025-11-15T19:00:00.000000Z"` → extract `"19:00"`
- Added helper functions `_timeFromJson()` and `_dateFromJson()`
- Removed fields not in backend: `max_capacity`, `enrolled_count`, `created_at`
- Ignored backend computed fields: `start_datetime`, `end_datetime`, `is_upcoming`, `is_today`

**Status**: ✅ Build runner generated code successfully

---

### 2. API Service Enhancement ✅
**Files**:
- `lib/core/api/api_service.dart`
- `lib/core/api/api_response.dart` (NEW)

**Changes**:
- Created `ApiResponse<T>` and `ApiListResponse<T>` wrapper models
- Added 8 new endpoints to ApiService:
  ```dart
  // LESSONS
  GET  /mobile/v1/student/lessons/upcoming
  GET  /mobile/v1/student/lessons/{id}
  GET  /mobile/v1/student/lessons/by-date/{date}
  GET  /mobile/v1/student/lessons

  // NOTIFICATION PREFERENCES
  GET  /mobile/v1/notifications/preferences
  PUT  /mobile/v1/notifications/preferences

  // FCM TOKEN
  POST /mobile/v1/notifications/fcm-token
  DELETE /mobile/v1/notifications/fcm-token
  ```

**Status**: ✅ Retrofit code generated

---

### 3. API Constants Update ✅
**File**: `lib/core/constants/api_constants.dart`

**Changes**:
- Updated all lesson endpoints to `/mobile/v1/student/lessons/*`
- Updated notification endpoints to `/mobile/v1/notifications/*`
- Added helper methods:
  ```dart
  static String lessonById(String id) => '/mobile/v1/student/lessons/$id';
  static String lessonsByDate(String date) => '/mobile/v1/student/lessons/by-date/$date';
  ```

---

### 4. Lesson Notification Scheduler Service ✅
**File**: `lib/core/services/lesson_notification_scheduler.dart` (NEW - 250 lines)

**Complete Implementation**:
```dart
class LessonNotificationScheduler {
  // Initialization with timezone (Europe/Rome)
  Future<void> initialize()

  // Schedule single lesson
  Future<void> scheduleForLesson(Lesson lesson, int minutesBefore)

  // Batch schedule
  Future<void> scheduleForLessons(List<Lesson> lessons, int minutesBefore)

  // Cancel specific
  Future<void> cancelForLesson(String lessonId, int minutesBefore)

  // Cancel all
  Future<void> cancelAll()

  // Reschedule when preferences change
  Future<void> rescheduleAll(
    List<Lesson> lessons,
    int newMinutesBefore,
    int? oldMinutesBefore,
  )

  // Get pending notifications
  Future<List<PendingNotificationRequest>> getPendingNotifications()

  // Check if scheduled
  Future<bool> isScheduled(String lessonId, int minutesBefore)
}
```

**Features**:
- Timezone-aware scheduling (tz.local = Europe/Rome)
- Smart notification ID generation (lesson_id * 10000 + minutes)
- Auto-skip past lessons
- Auto-skip non-scheduled lessons
- Deep linking payload: `"lesson:{id}:{courseId}"`
- Android + iOS notification details
- Rose/purple color scheme matching app theme

**Status**: ✅ Ready to use

---

### 5. Lessons Repository Update ✅
**File**: `lib/features/lessons/data/repositories/lessons_repository_impl.dart`

**Changes**: Updated all 5 methods to parse new backend response format:

```dart
// OLD (WRONG):
final lessonsData = response.data['data'] as List;

// NEW (CORRECT):
if (response.data['success'] != true) {
  throw Exception(response.data['message'] ?? 'API returned success=false');
}

final lessonsData = response.data['data'] as List?;
if (lessonsData == null) {
  return const Right([]);
}

final lessons = lessonsData
    .map((json) => LessonModel.fromJson(json as Map<String, dynamic>).toEntity())
    .toList();
```

**Methods Updated**:
1. `getUpcomingLessons()` - Uses `/mobile/v1/student/lessons/upcoming`
2. `getLessonsByCourse()` - Uses `/mobile/v1/student/lessons?course_id=`
3. `getLessonsByDate()` - Uses `/mobile/v1/student/lessons/by-date/{date}` ✅ Fixed endpoint
4. `getLessonById()` - Uses `/mobile/v1/student/lessons/{id}` ✅ Fixed endpoint
5. `getMyLessons()` - Uses `/mobile/v1/student/lessons`

**Improvements**:
- Success field validation
- Null-safe data handling
- Backend error message extraction: `e.response?.data?['message']`
- Empty array return instead of error when no data

**Status**: ✅ Ready for API testing

---

### 6. FCM Token Service ✅
**File**: `lib/core/services/fcm_token_service.dart` (NEW)

**Complete Implementation**:
```dart
class FcmTokenService {
  // Register token on backend
  Future<bool> registerToken(String token)

  // Delete token on logout
  Future<bool> deleteToken(String token)

  // Get device info for debugging
  Future<Map<String, String>> getDeviceInfo()
}
```

**Features**:
- Auto device ID detection:
  - Android: `androidInfo.id` (Android ID)
  - iOS: `identifierForVendor`
- Device type auto-detection (`android` / `ios`)
- Non-blocking errors (app works without push)
- Detailed logging

**Usage**:
```dart
// On app initialization
final token = notificationService.fcmToken;
if (token != null) {
  await fcmTokenService.registerToken(token);
}

// On logout
await fcmTokenService.deleteToken(token);
```

**Dependency Added**: `device_info_plus: ^12.2.0`

**Status**: ✅ Ready to use

---

## 🚧 RIMANE DA FARE (4 Tasks - ~4 ore)

### 7. Notification Preferences Provider Integration
**Status**: ⏳ PENDING
**Estimated**: 1.5 hours
**Priority**: HIGH

**File**: `lib/features/notifications/presentation/providers/notification_preferences_provider.dart`

**Current State**: Provider exists but uses old local services (`NotificationPreferencesService`, `LessonReminderService`)

**Required Changes**:

1. **Replace old services with new ones**:
   ```dart
   // Remove:
   - NotificationPreferencesService (local only)
   - LessonReminderService (old implementation)

   // Add:
   + LessonNotificationScheduler (our new service)
   + API calls via NotificationPreferencesRepository
   + LessonsRepository for fetching upcoming lessons
   ```

2. **Update `loadPreferences()` method**:
   ```dart
   Future<void> loadPreferences() async {
     state = const AsyncValue.loading();
     try {
       // 1. Fetch from backend API
       final result = await _repository.getPreferences();
       final preferences = result.fold(
         (failure) => throw Exception(failure.message),
         (prefs) => prefs,
       );

       state = AsyncValue.data(preferences);

       // 2. If lesson reminders enabled, schedule notifications
       if (preferences.shouldSendLessonReminders) {
         await _scheduleUpcomingLessons(preferences.reminderMinutesBefore);
       }
     } catch (e, stack) {
       state = AsyncValue.error(e, stack);
     }
   }
   ```

3. **Add `_scheduleUpcomingLessons()` helper**:
   ```dart
   Future<void> _scheduleUpcomingLessons(int minutesBefore) async {
     // Fetch next 7 days lessons
     final result = await _lessonsRepository.getUpcomingLessons(days: 7);

     result.fold(
       (failure) => AppLogger.error('Failed to fetch lessons: ${failure.message}'),
       (lessons) async {
         // Schedule notifications
         await _scheduler.scheduleForLessons(lessons, minutesBefore);
         AppLogger.info('✅ Scheduled ${lessons.length} lesson reminders');
       },
     );
   }
   ```

4. **Update `updateReminderTime()` method**:
   ```dart
   Future<void> updateReminderTime(int newMinutes) async {
     final currentPrefs = state.value;
     if (currentPrefs == null) return;

     try {
       // 1. Update backend
       final result = await _repository.updatePreferences({
         'reminder_minutes_before': newMinutes,
       });

       final updatedPrefs = result.fold(
         (failure) => throw Exception(failure.message),
         (prefs) => prefs,
       );

       state = AsyncValue.data(updatedPrefs);

       // 2. Reschedule notifications
       if (updatedPrefs.shouldSendLessonReminders) {
         final lessonsResult = await _lessonsRepository.getUpcomingLessons(days: 7);

         lessonsResult.fold(
           (failure) => AppLogger.error('Failed to fetch lessons'),
           (lessons) async {
             await _scheduler.rescheduleAll(
               lessons,
               newMinutes,
               currentPrefs.reminderMinutesBefore,
             );
           },
         );
       }
     } catch (e, stack) {
       state = AsyncValue.error(e, stack);
     }
   }
   ```

5. **Update `toggleLessonReminders()` method**:
   ```dart
   Future<void> toggleLessonReminders(bool enabled) async {
     try {
       // 1. Update backend
       final result = await _repository.updatePreferences({
         'lesson_reminders': enabled,
       });

       final updatedPrefs = result.fold(
         (failure) => throw Exception(failure.message),
         (prefs) => prefs,
       );

       state = AsyncValue.data(updatedPrefs);

       // 2. Schedule or cancel
       if (enabled) {
         await _scheduleUpcomingLessons(updatedPrefs.reminderMinutesBefore);
       } else {
         await _scheduler.cancelAll();
       }
     } catch (e, stack) {
       state = AsyncValue.error(e, stack);
     }
   }
   ```

**Dependencies to Inject**:
```dart
NotificationPreferencesNotifier({
  required NotificationPreferencesRepository repository,
  required LessonsRepository lessonsRepository,
  required LessonNotificationScheduler scheduler,
  required String userId,
})
```

---

### 8. Notification Settings UI Update
**Status**: ⏳ PENDING
**Estimated**: 1 hour
**Priority**: MEDIUM

**File**: `lib/features/notifications/presentation/screens/notification_settings_screen.dart`

**Required UI Elements**:

1. **Master Toggle**
   ```dart
   SwitchListTile(
     title: Text('Abilita Notifiche'),
     subtitle: Text('Attiva/disattiva tutte le notifiche'),
     value: prefs.enabled,
     onChanged: (value) => ref.read(notifProvider.notifier).toggleAll(value),
   )
   ```

2. **Lesson Reminders Section**
   ```dart
   SwitchListTile(
     title: Text('Promemoria Lezioni'),
     value: prefs.lessonReminders,
     onChanged: (value) => ref.read(notifProvider.notifier).toggleLessonReminders(value),
   )

   // Reminder Time Picker (only visible if lessonReminders == true)
   if (prefs.lessonReminders) ...[
     ListTile(
       title: Text('Anticipo Promemoria'),
       subtitle: Text(prefs.formattedReminderTime),
       trailing: Icon(Icons.chevron_right),
       onTap: () => _showReminderTimePicker(context, ref),
     ),
   ]
   ```

3. **Reminder Time Picker Dialog**
   ```dart
   void _showReminderTimePicker(BuildContext context, WidgetRef ref) {
     final availableTimes = NotificationPreferences.availableReminderTimes;
     // [15, 30, 60, 120, 1440]

     showDialog(
       context: context,
       builder: (context) => AlertDialog(
         title: Text('Anticipo Promemoria'),
         content: Column(
           mainAxisSize: MainAxisSize.min,
           children: availableTimes.map((minutes) {
             return RadioListTile<int>(
               title: Text(NotificationPreferences.getReminderTimeLabel(minutes)),
               // "15 minuti prima", "1 ora prima", etc.
               value: minutes,
               groupValue: prefs.reminderMinutesBefore,
               onChanged: (value) {
                 if (value != null) {
                   ref.read(notifProvider.notifier).updateReminderTime(value);
                   Navigator.pop(context);
                 }
               },
             );
           }).toList(),
         ),
       ),
     );
   }
   ```

4. **Other Toggles**
   ```dart
   SwitchListTile(
     title: Text('Promemoria Eventi'),
     value: prefs.eventReminders,
     onChanged: (value) => ref.read(notifProvider.notifier).toggleEventReminders(value),
   )

   SwitchListTile(
     title: Text('Promemoria Pagamenti'),
     value: prefs.paymentReminders,
     onChanged: (value) => ref.read(notifProvider.notifier).togglePaymentReminders(value),
   )

   SwitchListTile(
     title: Text('Notifiche Sistema'),
     value: prefs.systemNotifications,
     onChanged: (value) => ref.read(notifProvider.notifier).toggleSystemNotifications(value),
   )
   ```

5. **Loading/Error States**
   ```dart
   ref.watch(notificationPreferencesProvider).when(
     data: (prefs) => _buildSettingsContent(prefs),
     loading: () => Center(child: CircularProgressIndicator()),
     error: (error, stack) => Center(
       child: Text('Errore: $error'),
     ),
   )
   ```

**Design**: Follow app theme (rose/purple gradient, white cards, rounded corners)

---

### 9. Notification Tap Navigation (Deep Linking)
**Status**: ⏳ PENDING
**Estimated**: 1 hour
**Priority**: MEDIUM

**Files to Update**:

1. **`lib/main.dart`** - Register callback
   ```dart
   void main() async {
     WidgetsFlutterBinding.ensureInitialized();

     // Initialize services
     await notificationService.initialize();
     await lessonScheduler.initialize();

     // Setup notification tap handling
     final router = AppRouter.router; // Get GoRouter instance

     notificationService.setNotificationTapCallback((payload) {
       AppLogger.info('Notification tapped: $payload');

       final parsed = LessonNotificationScheduler.parsePayload(payload);
       if (parsed != null) {
         final lessonId = parsed['lessonId'];
         router.go('/lessons/$lessonId');
       }
     });

     runApp(MyApp());
   }
   ```

2. **`lib/core/routing/app_router.dart`** - Add lesson detail route
   ```dart
   GoRoute(
     path: '/lessons/:id',
     name: 'lesson-detail',
     builder: (context, state) {
       final lessonId = state.pathParameters['id']!;
       return LessonDetailScreen(lessonId: lessonId);
     },
   ),
   ```

3. **`lib/features/lessons/presentation/screens/lesson_detail_screen.dart`** - Create screen (or update existing)
   ```dart
   class LessonDetailScreen extends ConsumerWidget {
     final String lessonId;

     const LessonDetailScreen({required this.lessonId});

     @override
     Widget build(BuildContext context, WidgetRef ref) {
       // Fetch lesson by ID
       final lessonProvider = FutureProvider((ref) async {
         final repo = ref.read(lessonsRepositoryProvider);
         final result = await repo.getLessonById(lessonId);
         return result.fold(
           (failure) => throw Exception(failure.message),
           (lesson) => lesson,
         );
       });

       return ref.watch(lessonProvider).when(
         data: (lesson) => _buildLessonDetail(lesson),
         loading: () => Scaffold(
           appBar: AppBar(title: Text('Caricamento...')),
           body: Center(child: CircularProgressIndicator()),
         ),
         error: (error, stack) => Scaffold(
           appBar: AppBar(title: Text('Errore')),
           body: Center(child: Text('$error')),
         ),
       );
     }
   }
   ```

**Testing**:
- Tap local notification → app opens to lesson detail
- Tap remote push → app opens to lesson detail
- Deep link works from background/terminated app state

---

### 10. End-to-End Testing
**Status**: ⏳ PENDING
**Estimated**: 1.5 hours
**Priority**: CRITICAL

**Test Scenarios**:

1. **Login & Initial Setup** (15 min)
   - [ ] Login successful
   - [ ] FCM token registered to backend
   - [ ] Verify in Firebase Console that token appears
   - [ ] Check backend database: `fcm_tokens` table has new record
   - [ ] Notification preferences loaded from backend
   - [ ] Default preferences: `lesson_reminders: true`, `reminder_minutes_before: 60`

2. **Fetch Lessons & Schedule Notifications** (20 min)
   - [ ] App fetches upcoming lessons (7 days)
   - [ ] Verify API call: `GET /mobile/v1/student/lessons/upcoming?days=7`
   - [ ] Check response: 4 lessons returned (from TestSchool)
   - [ ] Local notifications scheduled
   - [ ] Verify with: `lessonScheduler.getPendingNotifications()`
   - [ ] Should have 4 pending notifications (one per lesson, 60 min before)

3. **Change Reminder Time** (15 min)
   - [ ] Open Notification Settings
   - [ ] Change reminder time: 60 min → 120 min (2 hours)
   - [ ] Verify API call: `PUT /mobile/v1/notifications/preferences`
   - [ ] Old notifications cancelled
   - [ ] New notifications scheduled (120 min before)
   - [ ] Verify pending notifications updated

4. **Disable Lesson Reminders** (10 min)
   - [ ] Toggle lesson reminders OFF
   - [ ] All notifications cancelled
   - [ ] Verify: `getPendingNotifications()` returns empty list
   - [ ] Toggle back ON
   - [ ] Notifications rescheduled

5. **Local Notification Received** (15 min)
   - [ ] **Option A**: Wait for real notification time
   - [ ] **Option B**: Debug trigger:
     ```dart
     await lessonScheduler.scheduleForLesson(
       testLesson,
       0, // 0 minutes before = now
     );
     ```
   - [ ] Notification appears in system tray
   - [ ] Title: "Lezione tra poco! 🩰"
   - [ ] Body: "Danza Classica Test inizia tra 2 ore" (example)
   - [ ] Tap notification
   - [ ] App opens to lesson detail screen
   - [ ] Correct lesson loaded

6. **Firebase Remote Push** (20 min)
   - [ ] SSH to backend: `ssh root@157.230.114.252`
   - [ ] Send test push via tinker:
     ```php
     php artisan tinker

     $service = app(\App\Services\FirebasePushService::class);
     $service->sendToUser(
       133, // user_id from fcm_tokens table
       'Test Push',
       'Questa è una notifica di test da backend',
       ['type' => 'test']
     );
     ```
   - [ ] App receives push (foreground & background)
   - [ ] Local notification shown
   - [ ] Tap handling works

7. **Edge Cases** (15 min)
   - [ ] **No Internet**: Turn off WiFi/data
     - [ ] Local notifications still work ✅
     - [ ] Already scheduled notifications fire ✅
     - [ ] API calls fail gracefully
   - [ ] **Past Lessons**: Verify no notifications for past lessons
   - [ ] **Cancelled Lessons**: Status `cancelled` → no notifications
   - [ ] **Multiple Devices**:
     - [ ] Login on second device
     - [ ] Both devices get their own FCM token
     - [ ] Backend push sends to both devices

**Success Criteria**:
- ✅ All 7 scenarios pass
- ✅ No crashes or errors
- ✅ Notifications arrive on time
- ✅ Deep linking works correctly
- ✅ Backend logs show successful push sends

---

## 📊 Overall Week 2 Progress

| Day | Tasks | Hours | Progress |
|-----|-------|-------|----------|
| Day 1 | 1-6 | ~5h | 60% ✅ |
| Day 2 | 7-10 | ~4h | 40% ⏳ |
| **TOTAL** | **10** | **~9h** | **100%** |

**Current Status**: 60% complete
**Remaining**: ~4 hours (estimated 1 more work session)

---

## 🚀 Git Status

**Repository**: https://github.com/emanuelerosato/danzafacile-app

**Branch**: `claude/add-push-notifications-01DXxmCnNefTYQ5DbeCV8kHU`

**Commits Today**:
- `af4d9ec` - Initial setup (40%)
- `bf141ce` - Tasks 5-6 complete (60%)

**Ready to Push**: ✅ Yes

---

## 🎯 Next Session Plan

**Start Time**: When you're ready
**Duration**: ~4 hours
**Focus**: Complete remaining 4 tasks

**Order**:
1. Task 7: Preferences Provider (1.5h) - CRITICAL
2. Task 8: Settings UI (1h) - MEDIUM
3. Task 9: Deep Linking (1h) - MEDIUM
4. Task 10: Testing (1.5h) - CRITICAL

**After Testing**: If all passes → Merge to main, deploy APK for real device testing

---

## 📝 Notes

### Firebase Files Status
**Required**:
- `android/app/google-services.json` - ❓ Da verificare
- `ios/Runner/GoogleService-Info.plist` - ❓ Da verificare

**Action**: Verifica se presenti, altrimenti scarica da Firebase Console

### Backend API Status
- **URL**: https://www.danzafacile.it/api/mobile/v1/*
- **Status**: ✅ OPERATIONAL (Week 1 complete)
- **Test Credentials**: `studente1@test.pushnotif.local` / `password`

### Known Issues
- ⚠️ Analyzer version warning (non-blocking)
- ⚠️ 116 dependencies have newer versions (upgrade dopo Week 2)

---

**Last Updated**: 2025-11-16 Day 1 Complete (60%)
**Next Milestone**: Complete Week 2 (100%)
**Final Goal**: Production-ready Flutter app with full push notifications
