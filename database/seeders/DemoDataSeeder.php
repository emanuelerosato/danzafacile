<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\School;
use App\Models\User;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Payment;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Document;
use App\Models\MediaGallery;
use App\Models\MediaItem;
use App\Models\Attendance;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🏫 Creazione Demo Data per Scuola di Danza...');

        // 1. Crea Scuole
        $schools = $this->createSchools();

        // 2. Crea Utenti per ogni scuola
        $users = $this->createUsers($schools);

        // 3. Crea Corsi
        $courses = $this->createCourses($schools, $users);

        // 4. Crea Iscrizioni
        $enrollments = $this->createEnrollments($courses, $users);

        // 5. Crea Pagamenti
        $this->createPayments($enrollments, $users);

        // 6. Crea Eventi
        $events = $this->createEvents($schools);

        // 7. Crea Registrazioni Eventi
        $this->createEventRegistrations($events, $users);

        // 8. Crea Documenti
        $this->createDocuments($schools, $users, $courses);

        // 9. Crea Gallerie Media
        $this->createMediaGalleries($schools, $courses);

        // 10. Crea Record Presenze
        $this->createAttendanceRecords($courses, $users);

        $this->command->info('✅ Demo Data creato con successo!');
        $this->printSummary();
    }

    private function createSchools(): array
    {
        $this->command->info('📍 Creazione Scuole...');

        $schools = [
            School::create([
                'name' => 'Academia di Danza Roma',
                'description' => 'La principale scuola di danza della capitale, specializzata in danza classica e moderna.',
                'address' => 'Via del Teatro, 15',
                'city' => 'Roma',
                'postal_code' => '00187',
                'phone' => '+39 06 1234567',
                'email' => 'info@academiroma.it',
                'website' => 'https://www.academiroma.it',
                'active' => true,
            ]),

            School::create([
                'name' => 'Studio Danza Milano',
                'description' => 'Centro di eccellenza per la formazione in danza contemporanea e hip-hop.',
                'address' => 'Corso Buenos Aires, 42',
                'city' => 'Milano',
                'postal_code' => '20124',
                'phone' => '+39 02 9876543',
                'email' => 'contatti@studiomilano.it',
                'website' => 'https://www.studiomilano.it',
                'active' => true,
            ]),

            School::create([
                'name' => 'Scuola Danza Napoli',
                'description' => 'Tradizione e innovazione nella danza del Sud Italia.',
                'address' => 'Via Toledo, 128',
                'city' => 'Napoli',
                'postal_code' => '80134',
                'phone' => '+39 081 5555123',
                'email' => 'info@danzanapoli.it',
                'website' => 'https://www.danzanapoli.it',
                'active' => true,
            ])
        ];

        $this->command->info("   ✓ Create " . count($schools) . " scuole");
        return $schools;
    }

    private function createUsers(array $schools): array
    {
        $this->command->info('👥 Creazione Utenti...');

        $users = collect();

        // Super Admin
        $superAdmin = User::create([
            'name' => 'Super Amministratore',
            'first_name' => 'Mario',
            'last_name' => 'Rossi',
            'email' => 'superadmin@danzafacile.it',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'phone' => '+39 333 1234567',
            'date_of_birth' => '1975-03-15',
            'address' => 'Via Nazionale, 100, Roma',
            'active' => true,
        ]);
        $users->push($superAdmin);

        foreach ($schools as $school) {
            // Admin per ogni scuola
            $admin = User::create([
                'name' => "Admin {$school->name}",
                'first_name' => 'Giulia',
                'last_name' => "Bianchi_{$school->id}",
                'email' => "admin@{$school->id}.scuola.it",
                'password' => Hash::make('password'),
                'school_id' => $school->id,
                'role' => 'admin',
                'phone' => '+39 333 ' . (2000000 + $school->id),
                'date_of_birth' => '1985-06-20',
                'address' => $school->address,
                'emergency_contact' => '+39 335 9876543',
                'active' => true,
            ]);
            $users->push($admin);

            // Istruttori
            for ($i = 1; $i <= 3; $i++) {
                $instructor = User::create([
                    'name' => "Istruttore {$i} {$school->name}",
                    'first_name' => ['Francesca', 'Alessandro', 'Valentina'][$i-1],
                    'last_name' => ['Verdi', 'Neri', 'Gialli'][$i-1],
                    'email' => "istruttore{$i}@{$school->id}.scuola.it",
                    'password' => Hash::make('password'),
                    'school_id' => $school->id,
                    'role' => 'admin', // Gli istruttori hanno ruolo admin nel DB
                    'phone' => '+39 334 ' . (3000000 + $school->id * 10 + $i),
                    'date_of_birth' => Carbon::now()->subYears(rand(25, 45))->format('Y-m-d'),
                    'address' => $school->address,
                    'emergency_contact' => '+39 336 ' . rand(1000000, 9999999),
                    'medical_notes' => $i == 2 ? 'Allergia ai profumi' : null,
                    'active' => true,
                ]);
                $users->push($instructor);
            }

            // Studenti
            for ($i = 1; $i <= 15; $i++) {
                $firstNames = ['Anna', 'Marco', 'Sofia', 'Luca', 'Chiara', 'Matteo', 'Giulia', 'Andrea', 'Francesca', 'Alessandro', 'Valentina', 'Davide', 'Elena', 'Simone', 'Martina'];
                $lastNames = ['Rossi', 'Bianchi', 'Verdi', 'Neri', 'Gialli', 'Ferrari', 'Romano', 'Ricci', 'Marino', 'Greco', 'Bruno', 'Gallo', 'Conti', 'De Luca', 'Mancini'];

                $student = User::create([
                    'name' => "{$firstNames[$i-1]} {$lastNames[$i-1]}",
                    'first_name' => $firstNames[$i-1],
                    'last_name' => $lastNames[$i-1],
                    'email' => "studente{$i}@{$school->id}.test.it",
                    'password' => Hash::make('password'),
                    'school_id' => $school->id,
                    'role' => 'user',
                    'phone' => '+39 335 ' . (4000000 + $school->id * 100 + $i),
                    'date_of_birth' => Carbon::now()->subYears(rand(16, 35))->format('Y-m-d'),
                    'address' => "Via Test {$i}, {$school->city}",
                    'emergency_contact' => '+39 337 ' . rand(1000000, 9999999),
                    'medical_notes' => rand(1, 5) == 1 ? ['Nessuna', 'Allergia stagionale', 'Problemi articolari', 'Asma lieve'][rand(0, 3)] : null,
                    'active' => rand(1, 10) > 1, // 90% attivi
                ]);
                $users->push($student);
            }
        }

        $this->command->info("   ✓ Creati {$users->count()} utenti");
        return $users->toArray();
    }

    private function createCourses(array $schools, array $users): array
    {
        $this->command->info('📚 Creazione Corsi...');

        $courses = collect();
        $courseTypes = [
            ['name' => 'Danza Classica Principianti', 'level' => 'beginner', 'difficulty_level' => 1, 'price' => 80.00],
            ['name' => 'Danza Moderna', 'level' => 'intermediate', 'difficulty_level' => 3, 'price' => 90.00],
            ['name' => 'Hip-Hop Avanzato', 'level' => 'advanced', 'difficulty_level' => 4, 'price' => 100.00],
            ['name' => 'Danza Contemporanea', 'level' => 'intermediate', 'difficulty_level' => 3, 'price' => 95.00],
            ['name' => 'Danza Jazz', 'level' => 'beginner', 'difficulty_level' => 2, 'price' => 85.00],
            ['name' => 'Balletto Avanzato', 'level' => 'advanced', 'difficulty_level' => 5, 'price' => 120.00],
        ];

        foreach ($schools as $school) {
            $instructors = collect($users)->where('school_id', $school->id)->where('role', 'admin')->values();

            foreach ($courseTypes as $index => $courseType) {
                $startDate = Carbon::now()->addDays(rand(7, 30));
                $course = Course::create([
                    'school_id' => $school->id,
                    'name' => $courseType['name'],
                    'description' => "Corso completo di {$courseType['name']} presso {$school->name}. Perfetto per sviluppare tecnica e creatività.",
                    'level' => $courseType['level'],
                    'difficulty_level' => $courseType['difficulty_level'],
                    'duration_weeks' => rand(8, 16),
                    'max_students' => rand(12, 20),
                    'price' => $courseType['price'],
                    'start_date' => $startDate,
                    'end_date' => $startDate->copy()->addWeeks(rand(8, 16)),
                    'schedule' => [
                        'lunedi' => ['19:00-20:30'],
                        'mercoledi' => ['19:00-20:30']
                    ],
                    'location' => "Sala " . chr(65 + ($index % 3)), // Sala A, B, C
                    'instructor_id' => $instructors->random()->id ?? null,
                    'active' => rand(1, 10) > 1, // 90% attivi
                ]);
                $courses->push($course);
            }
        }

        $this->command->info("   ✓ Creati {$courses->count()} corsi");
        return $courses->toArray();
    }

    private function createEnrollments(array $courses, array $users): array
    {
        $this->command->info('📋 Creazione Iscrizioni...');

        $enrollments = collect();
        $students = collect($users)->where('role', 'user');

        foreach ($courses as $course) {
            $studentsInSchool = $students->where('school_id', $course->school_id);
            $enrollCount = rand(3, min(12, $studentsInSchool->count()));

            $studentsInSchool->random($enrollCount)->each(function($student) use ($course, &$enrollments) {
                $enrollment = CourseEnrollment::create([
                    'course_id' => $course->id,
                    'user_id' => $student->id,
                    'enrollment_date' => Carbon::now()->subDays(rand(1, 30)),
                    'status' => ['attiva', 'sospesa', 'completata'][rand(0, 2)],
                    'payment_status' => ['in_attesa', 'pagato', 'parziale'][rand(0, 2)],
                    'notes' => rand(1, 4) == 1 ? 'Note specifiche per questo studente' : null,
                ]);
                $enrollments->push($enrollment);
            });
        }

        $this->command->info("   ✓ Create {$enrollments->count()} iscrizioni");
        return $enrollments->toArray();
    }

    private function createPayments(array $enrollments, array $users): void
    {
        $this->command->info('💰 Creazione Pagamenti...');

        $paymentsCount = 0;

        foreach ($enrollments as $enrollment) {
            if (rand(1, 3) <= 2) { // 66% di possibilità di avere un pagamento
                $course = Course::find($enrollment->course_id);
                Payment::create([
                    'course_id' => $course->id,
                    'user_id' => $enrollment->user_id,
                    'school_id' => $course->school_id,
                    'amount' => $course->price,
                    'payment_method' => ['carta_credito', 'bonifico', 'contanti', 'paypal'][rand(0, 3)],
                    'status' => ['in_attesa', 'completato', 'fallito', 'rimborsato'][rand(0, 3)],
                    'transaction_id' => 'TXN' . strtoupper(uniqid()),
                    'payment_date' => Carbon::now()->subDays(rand(1, 60)),
                    'notes' => rand(1, 5) == 1 ? 'Pagamento con sconto studente' : null,
                ]);
                $paymentsCount++;
            }
        }

        $this->command->info("   ✓ Creati {$paymentsCount} pagamenti");
    }

    private function createEvents(array $schools): array
    {
        $this->command->info('🎭 Creazione Eventi...');

        $events = collect();
        $eventTypes = [
            'Saggio di Fine Anno',
            'Workshop con Ospite Speciale',
            'Masterclass di Danza Contemporanea',
            'Spettacolo Natalizio',
            'Concorso Interno di Danza',
            'Open Day Scuola',
        ];

        foreach ($schools as $school) {
            foreach ($eventTypes as $eventType) {
                $event = Event::create([
                    'school_id' => $school->id,
                    'title' => $eventType,
                    'description' => "Evento speciale di {$eventType} organizzato da {$school->name}. Un'occasione unica per mostrare i progressi degli studenti.",
                    'event_date' => Carbon::now()->addDays(rand(10, 90)),
                    'start_time' => '20:00:00',
                    'end_time' => '22:30:00',
                    'location' => 'Teatro della scuola',
                    'max_participants' => rand(50, 200),
                    'price' => rand(0, 1) ? rand(10, 25) : 0, // Alcuni eventi gratuiti
                    'active' => true,
                ]);
                $events->push($event);
            }
        }

        $this->command->info("   ✓ Creati {$events->count()} eventi");
        return $events->toArray();
    }

    private function createEventRegistrations(array $events, array $users): void
    {
        $this->command->info('🎫 Creazione Registrazioni Eventi...');

        $registrationsCount = 0;
        $students = collect($users)->where('role', 'user');

        foreach ($events as $event) {
            $studentsInSchool = $students->where('school_id', $event->school_id);
            $registrationCount = rand(5, min(30, $studentsInSchool->count()));

            $studentsInSchool->random($registrationCount)->each(function($student) use ($event, &$registrationsCount) {
                EventRegistration::create([
                    'event_id' => $event->id,
                    'user_id' => $student->id,
                    'registration_date' => Carbon::now()->subDays(rand(1, 20)),
                    'status' => ['confermata', 'in_attesa', 'cancellata'][rand(0, 2)],
                    'payment_status' => $event->price > 0 ? ['pagato', 'in_attesa'][rand(0, 1)] : 'gratuito',
                    'notes' => rand(1, 6) == 1 ? 'Richieste speciali per accessibilità' : null,
                ]);
                $registrationsCount++;
            });
        }

        $this->command->info("   ✓ Create {$registrationsCount} registrazioni eventi");
    }

    private function createDocuments(array $schools, array $users, array $courses): void
    {
        $this->command->info('📄 Creazione Documenti...');

        $documentsCount = 0;
        $documentTypes = [
            ['type' => 'certificato_medico', 'name' => 'Certificato Medico'],
            ['type' => 'autorizzazione', 'name' => 'Autorizzazione Genitori'],
            ['type' => 'regolamento', 'name' => 'Regolamento Scuola'],
            ['type' => 'programma_corso', 'name' => 'Programma del Corso'],
            ['type' => 'attestato', 'name' => 'Attestato di Partecipazione'],
        ];

        foreach ($schools as $school) {
            foreach ($documentTypes as $docType) {
                Document::create([
                    'school_id' => $school->id,
                    'user_id' => collect($users)->where('school_id', $school->id)->where('role', 'admin')->first()->id ?? null,
                    'course_id' => rand(1, 3) == 1 ? collect($courses)->where('school_id', $school->id)->random()->id ?? null : null,
                    'title' => $docType['name'],
                    'description' => "Documento ufficiale: {$docType['name']} per {$school->name}",
                    'file_path' => "documents/{$school->id}/" . strtolower(str_replace(' ', '_', $docType['name'])) . '.pdf',
                    'file_type' => 'pdf',
                    'file_size' => rand(100000, 5000000), // Da 100KB a 5MB
                    'document_type' => $docType['type'],
                    'is_public' => rand(1, 3) == 1, // 33% pubblici
                    'active' => true,
                ]);
                $documentsCount++;
            }
        }

        $this->command->info("   ✓ Creati {$documentsCount} documenti");
    }

    private function createMediaGalleries(array $schools, array $courses): void
    {
        $this->command->info('📸 Creazione Gallerie Media...');

        $galleriesCount = 0;
        $mediaItemsCount = 0;

        foreach ($schools as $school) {
            // Galleria principale della scuola
            $mainGallery = MediaGallery::create([
                'school_id' => $school->id,
                'title' => "Galleria Principale - {$school->name}",
                'description' => 'Raccolta delle migliori foto e video della nostra scuola',
                'type' => 'mixed',
                'is_public' => true,
                'active' => true,
            ]);
            $galleriesCount++;

            // Media items per la galleria principale
            for ($i = 1; $i <= 8; $i++) {
                MediaItem::create([
                    'gallery_id' => $mainGallery->id,
                    'user_id' => collect($users)->where('school_id', $school->id)->where('role', 'admin')->first()->id ?? null,
                    'title' => "Foto {$i} - {$school->name}",
                    'description' => "Immagine rappresentativa della vita della scuola",
                    'file_path' => "media/{$school->id}/main/photo_{$i}.jpg",
                    'file_type' => 'image',
                    'file_size' => rand(500000, 3000000), // Da 500KB a 3MB
                    'is_public' => true,
                    'active' => true,
                ]);
                $mediaItemsCount++;
            }

            // Gallerie per alcuni corsi
            $schoolCourses = collect($courses)->where('school_id', $school->id)->take(3);
            foreach ($schoolCourses as $course) {
                $courseGallery = MediaGallery::create([
                    'school_id' => $school->id,
                    'course_id' => $course->id,
                    'title' => "Galleria {$course->name}",
                    'description' => "Foto e video del corso di {$course->name}",
                    'type' => 'course',
                    'is_public' => true,
                    'active' => true,
                ]);
                $galleriesCount++;

                // Media items per il corso
                for ($i = 1; $i <= 5; $i++) {
                    MediaItem::create([
                        'gallery_id' => $courseGallery->id,
                        'user_id' => $course->instructor_id,
                        'title' => "Lezione {$i} - {$course->name}",
                        'description' => "Momenti salienti della lezione",
                        'file_path' => "media/{$school->id}/courses/{$course->id}/lesson_{$i}.jpg",
                        'file_type' => rand(1, 4) == 1 ? 'video' : 'image',
                        'file_size' => rand(1000000, 10000000), // Da 1MB a 10MB
                        'is_public' => true,
                        'active' => true,
                    ]);
                    $mediaItemsCount++;
                }
            }
        }

        $this->command->info("   ✓ Create {$galleriesCount} gallerie e {$mediaItemsCount} media items");
    }

    private function createAttendanceRecords(array $courses, array $users): void
    {
        $this->command->info('✅ Creazione Record Presenze...');

        $attendanceCount = 0;

        foreach ($courses as $course) {
            $enrollments = CourseEnrollment::where('course_id', $course->id)->get();

            if ($enrollments->isEmpty()) continue;

            // Crea record di presenza per le ultime 4 settimane
            for ($week = 0; $week < 4; $week++) {
                $attendanceDate = Carbon::now()->subWeeks($week)->startOfWeek()->addDays(rand(0, 4));

                foreach ($enrollments as $enrollment) {
                    if (rand(1, 10) <= 8) { // 80% di presenza
                        Attendance::create([
                            'course_id' => $course->id,
                            'user_id' => $enrollment->user_id,
                            'school_id' => $course->school_id,
                            'attendance_date' => $attendanceDate,
                            'status' => ['presente', 'assente', 'giustificato'][rand(0, 2)],
                            'marked_by_user_id' => $course->instructor_id,
                            'notes' => rand(1, 8) == 1 ? 'Ottima partecipazione' : null,
                            'marked_at' => $attendanceDate->copy()->addHours(rand(1, 3)),
                        ]);
                        $attendanceCount++;
                    }
                }
            }
        }

        $this->command->info("   ✓ Creati {$attendanceCount} record di presenze");
    }

    private function printSummary(): void
    {
        $this->command->info('');
        $this->command->info('📊 RIEPILOGO DEMO DATA CREATO:');
        $this->command->info('=====================================');
        $this->command->info('🏫 Scuole: ' . School::count());
        $this->command->info('👥 Utenti totali: ' . User::count());
        $this->command->info('   - Super Admin: ' . User::where('role', 'super_admin')->count());
        $this->command->info('   - Admin: ' . User::where('role', 'admin')->count());
        $this->command->info('   - Studenti: ' . User::where('role', 'user')->count());
        $this->command->info('📚 Corsi: ' . Course::count());
        $this->command->info('📋 Iscrizioni: ' . CourseEnrollment::count());
        $this->command->info('💰 Pagamenti: ' . Payment::count());
        $this->command->info('🎭 Eventi: ' . Event::count());
        $this->command->info('🎫 Registrazioni Eventi: ' . EventRegistration::count());
        $this->command->info('📄 Documenti: ' . Document::count());
        $this->command->info('📸 Gallerie Media: ' . MediaGallery::count());
        $this->command->info('🖼️  Media Items: ' . MediaItem::count());
        $this->command->info('✅ Record Presenze: ' . Attendance::count());
        $this->command->info('=====================================');
        $this->command->info('');
        $this->command->info('🔑 CREDENZIALI DEMO:');
        $this->command->info('Super Admin: superadmin@danzafacile.it / password');
        $this->command->info('Admin Scuola 1: admin@1.scuola.it / password');
        $this->command->info('Admin Scuola 2: admin@2.scuola.it / password');
        $this->command->info('Admin Scuola 3: admin@3.scuola.it / password');
        $this->command->info('Studente: studente1@1.test.it / password');
        $this->command->info('');
    }
}
