# 📡 API Endpoints Reference - Push Notifications System

Quick reference per tutti gli endpoints del sistema push notifications.

---

## 🔐 Authentication

Tutti gli endpoints richiedono autenticazione Sanctum:

```http
Authorization: Bearer {token}
```

**Ottieni token:**
```bash
POST /api/mobile/v1/auth/login
Content-Type: application/json

{
  "email": "studente@test.it",
  "password": "password"
}

Response:
{
  "success": true,
  "token": "1|abcdef123456...",
  "user": {...}
}
```

---

## 📚 Student Lessons Endpoints

### 1. GET /api/mobile/v1/student/lessons/upcoming

Ottieni lezioni upcoming per lo studente autenticato.

**Query Parameters:**
- `days` (optional) - Numero di giorni da oggi (default: 7)

**Example:**
```bash
GET /api/mobile/v1/student/lessons/upcoming?days=7
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "1",
      "course_id": "5",
      "course_name": "Danza Classica Test",
      "instructor_id": "10",
      "instructor_name": "Istruttore Test 1",
      "room_id": "3",
      "room_name": "Sala A",
      "lesson_date": "2025-11-17",
      "start_time": "2025-11-15T19:00:00.000000Z",
      "end_time": "2025-11-15T20:30:00.000000Z",
      "status": "scheduled",
      "notes": null,
      "start_datetime": "2025-11-17T19:00:00+00:00",
      "end_datetime": "2025-11-17T20:30:00+00:00",
      "is_upcoming": true,
      "is_today": false
    }
  ],
  "meta": {
    "count": 4
  }
}
```

**Security:**
- Solo lezioni dei corsi in cui lo studente è iscritto
- Filtrate automaticamente per `status: scheduled`

---

### 2. GET /api/mobile/v1/student/lessons

Ottieni tutte le lezioni dello studente (passate e future).

**Query Parameters:**
- `course_id` (optional) - Filtra per ID corso specifico

**Example:**
```bash
GET /api/mobile/v1/student/lessons?course_id=5
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": [...],
  "meta": {
    "count": 18
  }
}
```

**Note:**
- Ordinamento: `lesson_date DESC`, `start_time DESC`
- Include anche lezioni passate

---

### 3. GET /api/mobile/v1/student/lessons/{id}

Ottieni dettagli singola lezione.

**Path Parameters:**
- `id` (required) - ID della lezione

**Example:**
```bash
GET /api/mobile/v1/student/lessons/1
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": "1",
    "course_id": "5",
    "course_name": "Danza Classica Test",
    "instructor_id": "10",
    "instructor_name": "Istruttore Test 1",
    "room_id": "3",
    "room_name": "Sala A",
    "lesson_date": "2025-11-17",
    "start_time": "2025-11-15T19:00:00.000000Z",
    "end_time": "2025-11-15T20:30:00.000000Z",
    "status": "scheduled",
    "notes": null,
    "start_datetime": "2025-11-17T19:00:00+00:00",
    "end_datetime": "2025-11-17T20:30:00+00:00",
    "is_upcoming": true,
    "is_today": false
  }
}
```

**Errors:**
- `404` - Lezione non trovata o studente non iscritto al corso

---

### 4. GET /api/mobile/v1/student/lessons/by-date/{date}

Ottieni lezioni per data specifica.

**Path Parameters:**
- `date` (required) - Data formato `YYYY-MM-DD`

**Example:**
```bash
GET /api/mobile/v1/student/lessons/by-date/2025-11-20
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "2",
      "course_name": "Danza Classica Test",
      "lesson_date": "2025-11-20",
      "start_time": "2025-11-15T19:00:00.000000Z",
      ...
    },
    {
      "id": "11",
      "course_name": "Hip Hop Test",
      "lesson_date": "2025-11-20",
      "start_time": "2025-11-15T19:00:00.000000Z",
      ...
    }
  ],
  "meta": {
    "date": "2025-11-20",
    "count": 2
  }
}
```

**Errors:**
- `400` - Formato data non valido

---

## 🔔 Notification Preferences Endpoints

### 5. GET /api/mobile/v1/notifications/preferences

Ottieni preferenze notifiche utente.

**Example:**
```bash
GET /api/mobile/v1/notifications/preferences
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 7,
    "user_id": 133,
    "enabled": true,
    "lesson_reminders": true,
    "reminder_minutes_before": 60,
    "event_reminders": true,
    "payment_reminders": true,
    "system_notifications": true,
    "created_at": "2025-11-15T21:38:46.000000Z",
    "updated_at": "2025-11-15T21:38:46.000000Z"
  }
}
```

**Note:**
- Se non esistono preferenze, vengono create automaticamente con defaults
- Defaults: tutto `true`, `reminder_minutes_before: 60`

---

### 6. PUT /api/mobile/v1/notifications/preferences

Aggiorna preferenze notifiche utente.

**Request Body:**
```json
{
  "enabled": true,
  "lesson_reminders": true,
  "reminder_minutes_before": 120,
  "event_reminders": false,
  "payment_reminders": true,
  "system_notifications": true
}
```

**Validation Rules:**
- `enabled`: boolean (optional)
- `lesson_reminders`: boolean (optional)
- `reminder_minutes_before`: integer, one of `[15, 30, 60, 120, 1440]` (optional)
- `event_reminders`: boolean (optional)
- `payment_reminders`: boolean (optional)
- `system_notifications`: boolean (optional)

**Example:**
```bash
PUT /api/mobile/v1/notifications/preferences
Authorization: Bearer {token}
Content-Type: application/json

{
  "reminder_minutes_before": 120
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 7,
    "user_id": 133,
    "enabled": true,
    "lesson_reminders": true,
    "reminder_minutes_before": 120,
    "event_reminders": true,
    "payment_reminders": true,
    "system_notifications": true,
    "created_at": "2025-11-15T21:38:46.000000Z",
    "updated_at": "2025-11-15T23:02:30.000000Z"
  },
  "message": "Preferenze aggiornate con successo"
}
```

**Available reminder times:**
- `15` - 15 minuti prima
- `30` - 30 minuti prima
- `60` - 1 ora prima (default)
- `120` - 2 ore prima
- `1440` - 1 giorno prima (24 ore)

**Errors:**
- `422` - Validation failed (es. `reminder_minutes_before` non valido)

---

## 📱 FCM Token Endpoints

### 7. POST /api/mobile/v1/notifications/fcm-token

Registra/aggiorna FCM token per dispositivo.

**Request Body:**
```json
{
  "token": "FCM_TOKEN_STRING_HERE",
  "device_type": "android",
  "device_id": "unique_device_id_123"
}
```

**Validation Rules:**
- `token`: string, required, max 255
- `device_type`: enum, required, one of `["android", "ios", "web"]`
- `device_id`: string, optional, max 255

**Example:**
```bash
POST /api/mobile/v1/notifications/fcm-token
Authorization: Bearer {token}
Content-Type: application/json

{
  "token": "dE3fG5hJ7kL9mN1pQ2rS4tU6vW8xY0zA",
  "device_type": "android",
  "device_id": "samsung_galaxy_s21_001"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "user_id": 133,
    "token": "dE3fG5hJ7kL9mN1pQ2rS4tU6vW8xY0zA",
    "device_type": "android",
    "device_id": "samsung_galaxy_s21_001",
    "last_used_at": "2025-11-15T23:02:36.000000Z",
    "created_at": "2025-11-15T23:02:36.000000Z",
    "updated_at": "2025-11-15T23:02:36.000000Z"
  },
  "message": "Token FCM registrato con successo"
}
```

**Note:**
- `updateOrCreate` su `(user_id, device_id)`: aggiorna token se esiste
- Multi-device support: utente può avere più token (es. phone + tablet)
- `last_used_at` aggiornato automaticamente

---

### 8. DELETE /api/mobile/v1/notifications/fcm-token

Rimuovi FCM token (logout dispositivo).

**Request Body:**
```json
{
  "token": "FCM_TOKEN_STRING_HERE"
}
```

**Example:**
```bash
DELETE /api/mobile/v1/notifications/fcm-token
Authorization: Bearer {token}
Content-Type: application/json

{
  "token": "dE3fG5hJ7kL9mN1pQ2rS4tU6vW8xY0zA"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Token FCM rimosso con successo"
}
```

**Note:**
- Rimuove token solo per l'utente autenticato
- Safe: se token non esiste, ritorna comunque success

---

## 🔧 Testing con cURL

### Setup Token
```bash
# Login
TOKEN=$(curl -s -X POST https://www.danzafacile.it/api/mobile/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"studente1@test.pushnotif.local","password":"password"}' \
  | jq -r '.token')

echo $TOKEN
```

### Test Endpoints
```bash
# 1. Upcoming lessons
curl -s -X GET "https://www.danzafacile.it/api/mobile/v1/student/lessons/upcoming?days=7" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" | jq '.'

# 2. Lessons by date
curl -s -X GET "https://www.danzafacile.it/api/mobile/v1/student/lessons/by-date/2025-11-20" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" | jq '.'

# 3. Get preferences
curl -s -X GET "https://www.danzafacile.it/api/mobile/v1/notifications/preferences" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" | jq '.'

# 4. Update preferences
curl -s -X PUT "https://www.danzafacile.it/api/mobile/v1/notifications/preferences" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"reminder_minutes_before":120}' | jq '.'

# 5. Register FCM token
curl -s -X POST "https://www.danzafacile.it/api/mobile/v1/notifications/fcm-token" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"token":"test_token_123","device_type":"android","device_id":"test_001"}' | jq '.'
```

---

## 📊 Response Format

### Success Response
```json
{
  "success": true,
  "data": {...},
  "meta": {...},
  "message": "Operation completed successfully"
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error description",
  "error_code": "ERROR_CODE",
  "errors": {...}
}
```

### Common HTTP Status Codes
- `200` - Success
- `400` - Bad Request (invalid input)
- `401` - Unauthorized (invalid/missing token)
- `404` - Not Found
- `422` - Validation Error
- `500` - Internal Server Error

---

## 🔒 Security Notes

1. **Authentication:** Tutti endpoints richiedono token Sanctum valido
2. **Authorization:** Studenti vedono solo lezioni dei propri corsi
3. **Data Isolation:** TestSchool (ID: 4) separata da produzione
4. **Token Cleanup:** FCM tokens invalidi rimossi automaticamente
5. **Rate Limiting:** 60 requests/minute per utente (Sanctum default)

---

## 📚 See Also

- [Firebase Setup Guide](FIREBASE_SETUP_GUIDE.md)
- [Push Notifications Progress](PUSH_NOTIFICATIONS_PROGRESS.md)
- [Implementation Plan](PUSH_NOTIFICATIONS_IMPLEMENTATION_PLAN.md)

---

**Created:** 2025-11-16
**Last Updated:** 2025-11-16
**Version:** 1.0.0
