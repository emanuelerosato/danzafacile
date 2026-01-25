# API Endpoints - Flutter Integration
**Ultima Modifica:** 2025-10-02
**Base URL:** `http://localhost:8089/api/mobile/v1`

---

## 🎫 TICKETS API

### **Student & Admin**
```
GET     /tickets                  - Lista tickets (filtri: status, priority, category, direction, search)
POST    /tickets                  - Crea nuovo ticket
GET     /tickets/statistics       - Statistiche tickets
GET     /tickets/{id}             - Dettaglio ticket
POST    /tickets/{id}/reply       - Rispondi al ticket
POST    /tickets/{id}/close       - Chiudi ticket
POST    /tickets/{id}/reopen      - Riapri ticket chiuso
```

### **Admin Only**
```
PUT     /tickets/{id}             - Aggiorna ticket (status, priority, assigned_to)
```

**Filtri disponibili:**
- `status`: open, pending, closed
- `priority`: low, medium, high, critical, urgent
- `category`: technical, payment, course, account, general, billing, feature, other
- `direction` (admin only): sent, received
- `search`: cerca in title, description
- `per_page`: paginazione (default: 15)

---

## 📄 DOCUMENTS API

### **Student & Admin**
```
GET     /documents                - Lista documenti (filtri: status, type, user_id, search)
POST    /documents                - Carica nuovo documento
GET     /documents/{id}           - Dettaglio documento
GET     /documents/{id}/download  - Download documento
DELETE  /documents/{id}           - Elimina documento
```

### **Admin Only**
```
GET     /documents/statistics     - Statistiche documenti
PUT     /documents/{id}           - Aggiorna metadata documento
POST    /documents/{id}/approve   - Approva documento (rate limited 5/min)
POST    /documents/{id}/reject    - Rifiuta documento (rate limited 5/min)
POST    /documents/bulk-action    - Azioni multiple (rate limited 5/min)
```

**Tipi documento:**
- `identity_card` - Carta d'identità
- `tax_code` - Codice fiscale
- `medical_certificate` - Certificato medico
- `privacy_consent` - Consenso privacy
- `photo_consent` - Consenso foto
- `other` - Altro

**File supportati:** PDF, JPG, JPEG, PNG (max 10MB)

**Bulk Actions:**
```json
{
  "action": "approve|reject|delete",
  "document_ids": [1, 2, 3]
}
```

---

## 🖼️ GALLERIES API

### **Student & Admin**
```
GET     /galleries                - Lista gallerie (filtri: is_public, search)
GET     /galleries/{id}           - Dettaglio galleria
GET     /galleries/{id}/media     - Lista media della galleria
```

### **Admin Only**
```
POST    /galleries                - Crea nuova galleria
PUT     /galleries/{id}           - Aggiorna galleria
DELETE  /galleries/{id}           - Elimina galleria (+ tutti i media)
GET     /galleries/stats          - Statistiche gallerie

# Media Management
POST    /galleries/{id}/upload             - Carica foto/video
POST    /galleries/{id}/external-link      - Aggiungi link esterno (YouTube, etc)
PUT     /galleries/{galleryId}/media/{mediaId}  - Aggiorna media
DELETE  /galleries/{galleryId}/media/{mediaId}  - Elimina media
POST    /galleries/{id}/cover-image        - Imposta copertina
```

**File supportati:** JPG, JPEG, PNG, GIF, MP4, MOV, AVI (max 20MB)

**Crea Galleria:**
```json
{
  "title": "Saggio 2025",
  "description": "Foto e video del saggio di fine anno",
  "is_public": true
}
```

**Upload Media:**
```json
{
  "title": "Performance gruppo A",
  "description": "Esibizione danza classica",
  "file": "<binary>"
}
```

**Aggiungi Link Esterno:**
```json
{
  "title": "Video YouTube Saggio",
  "url": "https://youtube.com/watch?v=xxxxx",
  "description": "Saggio completo su YouTube"
}
```

---

## 🏢 ROOMS API (Admin Only)

```
GET     /rooms                    - Lista aule (filtri: active, search)
POST    /rooms                    - Crea nuova aula
GET     /rooms/statistics         - Statistiche aule
GET     /rooms/{id}               - Dettaglio aula
PUT     /rooms/{id}               - Aggiorna aula
DELETE  /rooms/{id}               - Elimina aula (se non usata)
POST    /rooms/{id}/toggle-status - Attiva/disattiva aula
GET     /rooms/{id}/availability  - Disponibilità aula (corsi, occupazione)
```

**Crea Aula:**
```json
{
  "name": "Sala Grande",
  "description": "Sala principale per corsi collettivi",
  "location": "Piano terra - ala est",
  "capacity": 25,
  "active": true
}
```

**Response Availability:**
```json
{
  "room": {...},
  "courses": [
    {
      "id": 1,
      "name": "Danza Classica Avanzato",
      "instructor": "Mario Rossi",
      "schedule": "Lunedì 18:00-20:00",
      "enrolled_count": 18
    }
  ],
  "utilization": {
    "total_courses": 3,
    "total_students": 45,
    "capacity_usage": 60.0
  }
}
```

---

## 🔐 AUTENTICAZIONE

Tutte le API richiedono autenticazione tramite **Laravel Sanctum**.

### **Header Richiesto:**
```
Authorization: Bearer {token}
```

### **Ottenere il Token:**
```
POST /api/mobile/v1/auth/login
{
  "email": "user@example.com",
  "password": "password"
}

Response:
{
  "success": true,
  "data": {
    "user": {...},
    "token": "1|xxxxxxxxxxxxxxxxxxxxxxx"
  }
}
```

---

## ⚡ RATE LIMITING

**Limiti Applicati:**
- **api-auth:** 60 richieste/minuto per utente (API autenticate)
- **api-sensitive:** 5 richieste/minuto per utente (operazioni critiche)
  - `POST /documents/{id}/approve`
  - `POST /documents/{id}/reject`
  - `POST /documents/bulk-action`

---

## 📊 RESPONSE FORMAT

### **Success Response:**
```json
{
  "success": true,
  "data": {
    "items": [...],
    "pagination": {
      "current_page": 1,
      "total_pages": 5,
      "per_page": 15,
      "total": 73
    }
  }
}
```

### **Error Response:**
```json
{
  "success": false,
  "message": "Messaggio errore",
  "errors": {
    "field": ["Errore validazione campo"]
  }
}
```

### **HTTP Status Codes:**
- `200` - Successo
- `201` - Creato con successo
- `400` - Bad Request (dati non validi)
- `401` - Non autenticato
- `403` - Non autorizzato (permessi insufficienti)
- `404` - Risorsa non trovata
- `422` - Validation Error
- `429` - Too Many Requests (rate limit superato)
- `500` - Internal Server Error

---

## 🧪 TESTING ENDPOINTS

### **Esempio cURL - Lista Tickets:**
```bash
curl -X GET "http://localhost:8089/api/mobile/v1/tickets?status=open&per_page=10" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### **Esempio cURL - Carica Documento:**
```bash
curl -X POST "http://localhost:8089/api/mobile/v1/documents" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: multipart/form-data" \
  -F "title=Carta Identità" \
  -F "type=identity_card" \
  -F "file=@/path/to/document.pdf"
```

### **Esempio cURL - Crea Ticket:**
```bash
curl -X POST "http://localhost:8089/api/mobile/v1/tickets" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Problema con iscrizione corso",
    "description": "Non riesco a completare l iscrizione al corso di danza classica",
    "category": "course",
    "priority": "medium"
  }'
```

---

## 📝 NOTE IMPLEMENTAZIONE FLUTTER

### **Paginazione:**
Tutte le liste supportano paginazione con parametro `per_page`:
```dart
final response = await http.get(
  Uri.parse('$baseUrl/tickets?per_page=20&page=2'),
  headers: {'Authorization': 'Bearer $token'}
);
```

### **File Upload:**
Usare `MultipartRequest` per upload documenti/media:
```dart
var request = http.MultipartRequest('POST', Uri.parse('$baseUrl/documents'));
request.headers['Authorization'] = 'Bearer $token';
request.fields['title'] = 'Documento';
request.fields['type'] = 'identity_card';
request.files.add(await http.MultipartFile.fromPath('file', filePath));

var response = await request.send();
```

### **Error Handling:**
```dart
if (response.statusCode == 429) {
  // Rate limit exceeded - retry after delay
  await Future.delayed(Duration(seconds: 60));
} else if (response.statusCode == 401) {
  // Token expired - refresh or logout
  await refreshToken();
}
```

---

## ✅ CHECKLIST INTEGRAZIONE

**Prima di iniziare lo sviluppo Flutter:**
- [x] Tickets API implementata
- [x] Documents API implementata
- [x] Galleries API implementata
- [x] Rooms API implementata
- [x] Rate limiting configurato
- [x] Autenticazione Sanctum attiva
- [x] Validazione file upload (FileUploadHelper)
- [x] Multi-tenancy (school.ownership middleware)
- [x] Role-based access control

**Tutti i controller API estendono `BaseApiController` che fornisce:**
- `successResponse()`
- `errorResponse()`
- Response standardizzate JSON

---

## 🚀 DEPLOYMENT

**Variabili ambiente necessarie:**
```env
SANCTUM_STATEFUL_DOMAINS=localhost:8089
SESSION_DRIVER=cookie
SESSION_DOMAIN=localhost
```

**Comandi deploy:**
```bash
php artisan config:cache
php artisan route:cache
php artisan migrate --force
```
