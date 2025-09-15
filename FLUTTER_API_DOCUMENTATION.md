# 📱 FLUTTER API DOCUMENTATION
## Sistema di Gestione Scuola di Danza - API Integration Guide

---

## 🚀 **OVERVIEW**

Il backend Laravel fornisce una **API REST completa** per l'integrazione Flutter con:
- ✅ **Autenticazione multi-ruolo** (Super Admin, Admin, Student)
- ✅ **Multi-tenant security** automatica
- ✅ **Token-based authentication** (Laravel Sanctum)
- ✅ **Rate limiting** e throttling
- ✅ **CRUD completo** per tutti i moduli
- ✅ **File upload** per documenti e media
- ✅ **Real-time notifications**
- ✅ **Analytics e reports**

---

## 🔐 **AUTHENTICATION**

### Base URL
```
Production: https://yourdomain.com/api/mobile/v1/
Development: http://localhost:8089/api/mobile/v1/
```

### 1. **LOGIN**
```http
POST /auth/login
Content-Type: application/json

{
  "email": "student@example.com",
  "password": "password",
  "device_name": "iPhone Pro Max" // optional
}
```

**Response Success:**
```json
{
  "success": true,
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "user", // user|admin|super_admin
    "school_id": 2,
    "active": true,
    "email_verified_at": "2024-01-15T10:30:00Z"
  },
  "token": "1|abc123xyz789...",
  "token_type": "Bearer"
}
```

### 2. **REGISTER** (Solo Studenti)
```http
POST /auth/register
Content-Type: application/json

{
  "name": "Mario Rossi",
  "email": "mario@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "first_name": "Mario",
  "last_name": "Rossi",
  "phone": "+39 123 456 7890",
  "school_id": 2
}
```

### 3. **GET USER PROFILE**
```http
GET /auth/me
Authorization: Bearer {token}
```

### 4. **UPDATE PROFILE**
```http
PUT /auth/profile
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Mario Rossi Updated",
  "first_name": "Mario",
  "last_name": "Rossi",
  "phone": "+39 987 654 3210"
}
```

### 5. **CHANGE PASSWORD**
```http
PUT /auth/password
Authorization: Bearer {token}

{
  "current_password": "oldpassword",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

### 6. **LOGOUT**
```http
POST /auth/logout
Authorization: Bearer {token}
```

---

## 👨‍🎓 **STUDENT API ENDPOINTS**

### **Dashboard Quick Stats**
```http
GET /dashboard-quick
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "Mario Rossi",
      "email": "mario@example.com",
      "role": "user"
    },
    "school": {
      "id": 2,
      "name": "Centro Danza Roma"
    },
    "quick_stats": {
      "active_enrollments": 3,
      "pending_payments": 1,
      "total_courses": 5
    }
  }
}
```

### **Courses (Browse Available)**
```http
GET /student/courses?page=1&per_page=10&category=dance
Authorization: Bearer {token}
```

### **Course Details**
```http
GET /student/courses/{course_id}
Authorization: Bearer {token}
```

### **My Enrolled Courses**
```http
GET /student/courses/enrolled/me
Authorization: Bearer {token}
```

### **Course Recommendations**
```http
GET /student/courses/recommendations
Authorization: Bearer {token}
```

### **Enroll in Course**
```http
POST /student/enrollments
Authorization: Bearer {token}

{
  "course_id": 5,
  "enrollment_date": "2024-02-01"
}
```

### **Cancel Enrollment**
```http
POST /student/enrollments/{enrollment_id}/cancel
Authorization: Bearer {token}

{
  "reason": "Schedule conflict"
}
```

### **Enrollment History**
```http
GET /student/enrollments/history
Authorization: Bearer {token}
```

---

## 👨‍🏫 **ADMIN API ENDPOINTS**

### **Dashboard Analytics**
```http
GET /admin/dashboard
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "stats": {
      "students_total": 150,
      "students_active": 142,
      "courses_active": 25,
      "revenue_this_month": 12500.00
    },
    "recent_enrollments": [...],
    "pending_payments": [...],
    "upcoming_events": [...]
  }
}
```

### **Advanced Analytics**
```http
GET /admin/analytics?period=month
Authorization: Bearer {token}
```

### **Students Management**
```http
GET /admin/students?page=1&search=mario&active=1
POST /admin/students
GET /admin/students/{id}
PUT /admin/students/{id}
DELETE /admin/students/{id}
```

**Create Student:**
```json
{
  "name": "Maria Bianchi",
  "email": "maria@example.com",
  "first_name": "Maria",
  "last_name": "Bianchi",
  "phone": "+39 111 222 3333",
  "date_of_birth": "1995-05-15",
  "address": "Via Roma 123, Milano",
  "emergency_contact": "Madre: +39 444 555 6666"
}
```

### **Courses Management**
```http
GET /admin/courses
POST /admin/courses
GET /admin/courses/{id}
PUT /admin/courses/{id}
DELETE /admin/courses/{id}
```

**Create Course:**
```json
{
  "name": "Danza Classica Principianti",
  "description": "Corso base di danza classica",
  "instructor_id": 3,
  "start_date": "2024-03-01",
  "end_date": "2024-06-30",
  "schedule": "Lunedì e Mercoledì 18:00-19:30",
  "price": 120.00,
  "max_students": 15,
  "location": "Sala A",
  "active": true
}
```

### **Course Statistics**
```http
GET /admin/courses/statistics
Authorization: Bearer {token}
```

### **Toggle Course Status**
```http
POST /admin/courses/{id}/toggle-status
Authorization: Bearer {token}
```

### **Duplicate Course**
```http
POST /admin/courses/{id}/duplicate
Authorization: Bearer {token}

{
  "name": "Danza Classica Intermedio"
}
```

---

## 🎉 **MISSING ENDPOINTS** (Da implementare)

### **Events API**
```http
GET /events                    # Browse events
GET /events/{id}               # Event details
POST /events/{id}/register     # Register for event
GET /my-events                 # My registered events
```

### **Attendance API**
```http
GET /attendance/my-sessions    # My attendance history
POST /attendance/check-in      # Check-in to session
GET /attendance/qr-code        # Get QR for check-in
```

### **Staff API** (Admin only)
```http
GET /admin/staff              # Staff list
POST /admin/staff             # Add staff member
PUT /admin/staff/{id}         # Update staff
GET /admin/staff/{id}/schedule # Staff schedule
```

### **Analytics API** (Enhanced)
```http
GET /admin/analytics/revenue   # Revenue analytics
GET /admin/analytics/students  # Student analytics
GET /admin/analytics/courses   # Course performance
GET /admin/reports/export      # Export reports
```

---

## 📁 **SHARED ENDPOINTS** (Tutti gli utenti autenticati)

### **Documents Management**
```http
GET /documents                 # My documents
POST /documents               # Upload document
GET /documents/{id}           # Document details
PUT /documents/{id}           # Update document
DELETE /documents/{id}        # Delete document
```

**Upload Document:**
```http
POST /documents
Authorization: Bearer {token}
Content-Type: multipart/form-data

title: "Certificato Medico"
category: "medical"           # general|medical|contract|identification|other
file: [binary file data]
description: "Certificato medico 2024"
```

### **Media/Gallery Management**
```http
GET /media                    # Browse media
POST /media                   # Upload media
GET /media/{id}               # Media details
GET /galleries/{gallery_id}/media # Gallery media
```

### **Notifications**
```http
GET /notifications
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "notifications": [
      {
        "id": "payment_1",
        "type": "payment",
        "title": "Pending Payments",
        "message": "You have 2 pending payment(s)",
        "priority": "high",
        "created_at": "2024-01-15T10:30:00Z"
      }
    ],
    "unread_count": 1
  }
}
```

---

## 🔒 **SECURITY & MULTI-TENANT**

### **Headers Required**
```http
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### **Multi-Tenant Security**
- **Automatico**: Ogni richiesta viene filtrata per `school_id` dell'utente
- **Students**: Vedono solo dati della loro scuola
- **Admins**: Vedono solo dati della scuola che gestiscono
- **Super Admins**: Accesso a tutto il sistema

### **Rate Limiting**
- **Mobile API**: 120 requests/minute
- **Web API**: 60 requests/minute
- **Authentication**: Limitato per sicurezza

### **Error Responses**
```json
{
  "success": false,
  "message": "Validation failed",
  "data": null,
  "errors": {
    "email": ["The email field is required."],
    "password": ["Password must be at least 6 characters."]
  },
  "meta": {
    "timestamp": "2024-01-15T10:30:00Z",
    "version": "1.0"
  }
}
```

**HTTP Status Codes:**
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized (Token invalid/expired)
- `403` - Forbidden (Insufficient permissions)
- `404` - Not Found
- `422` - Validation Error
- `429` - Rate Limit Exceeded
- `500` - Server Error

---

## 🧪 **TESTING ENDPOINTS**

### **Test Authentication**
```bash
# Login
curl -X POST http://localhost:8089/api/mobile/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "contatti+admin@centrodanzaroma.it",
    "password": "password"
  }'

# Use token for authenticated requests
curl -X GET http://localhost:8089/api/mobile/v1/dashboard-quick \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## 📱 **FLUTTER INTEGRATION EXAMPLES**

### **HTTP Client Setup**
```dart
import 'package:dio/dio.dart';

class ApiClient {
  static const String baseUrl = 'http://localhost:8089/api/mobile/v1';
  late Dio _dio;
  String? _token;

  ApiClient() {
    _dio = Dio(BaseOptions(
      baseUrl: baseUrl,
      connectTimeout: const Duration(seconds: 10),
      receiveTimeout: const Duration(seconds: 10),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
    ));

    _dio.interceptors.add(InterceptorsWrapper(
      onRequest: (options, handler) {
        if (_token != null) {
          options.headers['Authorization'] = 'Bearer $_token';
        }
        handler.next(options);
      },
      onError: (error, handler) {
        if (error.response?.statusCode == 401) {
          // Token expired - redirect to login
          _handleUnauthorized();
        }
        handler.next(error);
      },
    ));
  }

  void setToken(String token) {
    _token = token;
  }

  Future<Map<String, dynamic>> login(String email, String password) async {
    try {
      final response = await _dio.post('/auth/login', data: {
        'email': email,
        'password': password,
        'device_name': 'Flutter App',
      });

      if (response.data['success']) {
        setToken(response.data['token']);
      }

      return response.data;
    } catch (e) {
      throw ApiException('Login failed: $e');
    }
  }

  Future<Map<String, dynamic>> getDashboard() async {
    try {
      final response = await _dio.get('/dashboard-quick');
      return response.data;
    } catch (e) {
      throw ApiException('Failed to fetch dashboard: $e');
    }
  }

  void _handleUnauthorized() {
    _token = null;
    // Navigate to login screen
  }
}

class ApiException implements Exception {
  final String message;
  ApiException(this.message);
}
```

### **Model Classes Example**
```dart
class User {
  final int id;
  final String name;
  final String email;
  final String role;
  final School? school;

  User({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    this.school,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'],
      name: json['name'],
      email: json['email'],
      role: json['role'],
      school: json['school'] != null ? School.fromJson(json['school']) : null,
    );
  }
}

class School {
  final int id;
  final String name;

  School({required this.id, required this.name});

  factory School.fromJson(Map<String, dynamic> json) {
    return School(
      id: json['id'],
      name: json['name'],
    );
  }
}

class Course {
  final int id;
  final String name;
  final String description;
  final double price;
  final String schedule;
  final bool active;

  Course({
    required this.id,
    required this.name,
    required this.description,
    required this.price,
    required this.schedule,
    required this.active,
  });

  factory Course.fromJson(Map<String, dynamic> json) {
    return Course(
      id: json['id'],
      name: json['name'],
      description: json['description'] ?? '',
      price: json['price']?.toDouble() ?? 0.0,
      schedule: json['schedule'] ?? '',
      active: json['active'] ?? true,
    );
  }
}
```

---

## 📋 **CHECKLIST PER FLUTTER DEVELOPER**

### **✅ API COMPLETAMENTE FUNZIONANTI:**
- [x] Authentication (login, register, logout, profile)
- [x] User management (profile, password change)
- [x] Students CRUD (Admin only)
- [x] Courses CRUD (Admin only)
- [x] Course enrollment (Students)
- [x] Payments management
- [x] Documents upload/management
- [x] Media/Gallery management
- [x] Multi-tenant security
- [x] Role-based permissions
- [x] Real-time notifications
- [x] Dashboard quick stats

### **⏳ API DA COMPLETARE:**
- [ ] Events & Event Registration
- [ ] Attendance tracking with QR codes
- [ ] Staff management (Admin only)
- [ ] Advanced Analytics API
- [ ] Push notifications

### **🚀 PRONTO PER SVILUPPO FLUTTER:**

Il backend è **80% completo** per l'integrazione Flutter. Puoi iniziare a sviluppare:

1. **Authentication flow** - ✅ Completamente funzionante
2. **Student app** - ✅ API complete per browse courses, enrollment, profile
3. **Admin app** - ✅ API complete per manage students, courses, dashboard
4. **Document upload** - ✅ Funzionante con file handling
5. **Real-time features** - ✅ Notifications API pronta

**Le API mancanti** (Events, Attendance, Staff, Advanced Analytics) verranno implementate nelle prossime fasi, ma non bloccano lo sviluppo della parte core dell'app Flutter.

**Base URL di sviluppo:** `http://localhost:8089/api/mobile/v1/`
**Test credentials:** Email: `contatti+admin@centrodanzaroma.it` | Password: `password`

---

## 📞 **SUPPORT**

Per domande sulle API o problemi di integrazione:
- Documentazione sempre aggiornata in questo file
- Test tutti gli endpoints con Postman/Thunder Client
- Verifica token authentication e multi-tenant security
- Controlla rate limiting se ricevi errori 429

**Happy coding! 🎉**