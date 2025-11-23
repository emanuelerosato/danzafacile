# 💃 DanzaFacile - Sistema Gestione Scuola di Danza

Backend Laravel 12 per gestione completa scuola di danza con sistema push notifications integrato.

## 🚀 Features

- ✅ **Gestione Scuola Multi-Tenant**: Super Admin, Admin, Studenti
- ✅ **Corsi & Iscrizioni**: Sistema completo gestione corsi e iscrizioni studenti
- ✅ **Pagamenti Online**: Integrazione PayPal per pagamenti
- ✅ **Media & Documenti**: Gestione gallerie e documenti studenti
- ✅ **Push Notifications**: Sistema completo notifiche lezioni (Firebase Cloud Messaging)
- ✅ **API REST**: 8+ endpoints per mobile app Flutter
- ✅ **Ticketing System**: Sistema supporto interno

## 📱 Push Notifications System

Sistema completo per promemoria lezioni automatici:

### Backend Features
- 🔔 **Firebase Admin SDK**: Invio push notifications via FCM
- ⏰ **Cron Job Automatico**: Check ogni 15 minuti per lezioni upcoming
- 🎯 **Preferenze Personalizzate**: Ogni utente sceglie timing promemoria (15min, 30min, 1h, 2h, 24h)
- 📊 **Notification Logs**: Tracking completo notifiche inviate
- 🔒 **Multi-Device Support**: Supporto multipli dispositivi per utente
- ♻️ **Auto Cleanup**: Rimozione automatica token invalidi

### API Endpoints
- `GET /api/mobile/v1/student/lessons/upcoming` - Lezioni upcoming
- `GET /api/mobile/v1/student/lessons/{id}` - Dettaglio singola lezione
- `GET /api/mobile/v1/student/lessons/by-date/{date}` - Lezioni per data
- `GET /api/mobile/v1/notifications/preferences` - Preferenze utente
- `PUT /api/mobile/v1/notifications/preferences` - Aggiorna preferenze
- `POST /api/mobile/v1/notifications/fcm-token` - Registra dispositivo
- `DELETE /api/mobile/v1/notifications/fcm-token` - Rimuovi dispositivo

📚 **[API Documentation](docs/API_ENDPOINTS_REFERENCE.md)**

## 🛠️ Tech Stack

- **Framework**: Laravel 12
- **PHP**: 8.2+
- **Database**: MySQL 8.0
- **Cache**: Redis 7.0
- **Search**: Meilisearch
- **Email**: Mailpit (dev), SMTP Aruba (prod)
- **Push**: Firebase Cloud Messaging
- **Frontend Build**: Vite + Tailwind CSS v4
- **Dev Environment**: Docker via Laravel Sail

## 📦 Installation

### Prerequisites
- Docker & Docker Compose
- PHP 8.2+
- Composer 2.x
- Node.js 18+

### Quick Start

```bash
# Clone repository
git clone https://github.com/emanuelerosato/danzafacile.git
cd danzafacile

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Start Docker services
./vendor/bin/sail up -d

# Run migrations
./vendor/bin/sail artisan migrate

# Build frontend assets
npm run dev
```

### Firebase Setup (Push Notifications)

1. Download Firebase credentials da [Firebase Console](https://console.firebase.google.com/)
2. Upload `firebase-credentials.json`:
   ```bash
   scp firebase-credentials.json root@YOUR_SERVER:/var/www/danzafacile/storage/app/firebase/
   ```
3. Configure `.env`:
   ```env
   FIREBASE_CREDENTIALS=storage/app/firebase/firebase-credentials.json
   FIREBASE_DATABASE_URL=https://YOUR_PROJECT-default-rtdb.firebaseio.com
   ```

📚 **[Firebase Setup Guide Completa](docs/FIREBASE_SETUP_GUIDE.md)** | **[Push Notifications Guide](docs/PUSH_NOTIFICATIONS_GUIDE.md)**

## 🧪 Testing

### Test School Data

Per testing è disponibile una scuola isolata con dati di test:

**Login Test:**
- Email: `studente1@test.pushnotif.local`
- Password: `password`
- Scuola: `[TEST] Scuola Push Notifications` (ID: 4)

**Dati disponibili:**
- 3 Studenti test
- 2 Corsi (Danza Classica, Hip Hop)
- 18 Lezioni (prossimi 30 giorni)
- Notification preferences configurate

### Run Tests

```bash
# API Testing
curl -X POST https://www.danzafacile.it/api/mobile/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"studente1@test.pushnotif.local","password":"password"}'

# Firebase Connection Test
./vendor/bin/sail artisan tinker
> $service = new App\Services\FirebasePushService();
> $service->testConnection(); // true = OK

# Cron Job Test
./vendor/bin/sail artisan notifications:send-lesson-reminders
```

## 📚 Documentation

**📖 [Complete Documentation Index](docs/README.md)** - Documentazione completa organizzata

**Quick Links:**
- **[API Endpoints Reference](docs/API_ENDPOINTS_REFERENCE.md)** - Quick reference tutti endpoints
- **[Push Notifications Guide](docs/PUSH_NOTIFICATIONS_GUIDE.md)** - Guida completa push notifications
- **[Deployment Guide](docs/deployment/DEPLOYMENT.md)** - Deploy su VPS
- **[Security Audit Report](docs/security/SECURITY_AUDIT_REPORT_2025-11-22.md)** - Security audit completo
- **[CLAUDE.md](CLAUDE.md)** - Istruzioni per Claude Code AI

## 🔧 Development

### Workflow
```bash
# Start development environment
composer run dev

# Or with Docker
./vendor/bin/sail up -d
./vendor/bin/sail npm run dev
```

### Code Quality
```bash
# Format code
./vendor/bin/pint

# Clear caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Database
```bash
# Fresh migration + seed
php artisan migrate:fresh --seed

# Specific seeder
php artisan db:seed --class=TestSchoolSeeder
```

## 🚀 Deployment

### Production Server
- **Host**: 157.230.114.252
- **Stack**: Nginx + PHP 8.2-FPM + MySQL 8.0 + Redis
- **Cron**: Laravel scheduler running ogni minuto
- **Branch**: `main` (produzione), `feature/*` (development)

### Deploy Process
```bash
# SSH to server
ssh root@157.230.114.252

# Pull latest
cd /var/www/danzafacile
git pull origin main

# Update dependencies
composer install --no-dev --optimize-autoloader

# Run migrations
php artisan migrate --force

# Clear caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart services
systemctl restart php8.2-fpm
systemctl reload nginx
```

## 📊 Project Status

**Version**: 1.0.0
**Status**: Production Ready ✅

**Backend:**
- Database: ✅ 100%
- API Endpoints: ✅ 100%
- Push Notifications: ✅ 100%
- Firebase Integration: ✅ 100%
- Testing: ✅ 100%
- Documentation: ✅ 100%

**Flutter App:** 🚧 In Development (Week 2)

## 🤝 Contributing

Questo è un progetto privato per DanzaFacile. Per contribuire:
1. Crea feature branch da `main`
2. Commit con conventional commits (`feat:`, `fix:`, `docs:`)
3. Pull request con description dettagliata
4. Review richiesta prima del merge

## 📄 License

Proprietary - © 2025 DanzaFacile

---

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
