<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\School;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Payment;
use App\Models\Document;
use App\Models\MediaItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting comprehensive database seeding...');

        // ===========================================
        // STEP 1: Create Schools
        // ===========================================
        $this->command->info('📚 Creating schools...');
        
        $schools = [
            [
                'name' => 'Accademia Danza Eleganza',
                'description' => 'La scuola di danza più prestigiosa di Milano, specializzata in danza classica e moderna',
                'address' => 'Via Brera 15, 20121 Milano MI',
                'phone' => '+39 02 1234567',
                'email' => 'info@eleganza.it',
                'active' => true,
            ],
            [
                'name' => 'Centro Danza Roma',
                'description' => 'Scuola di danza nel cuore di Roma con corsi per tutte le età',
                'address' => 'Via del Corso 45, 00186 Roma RM',
                'phone' => '+39 06 9876543',
                'email' => 'contatti@centrodanzaroma.it',
                'active' => true,
            ],
            [
                'name' => 'Studio Danza Firenze',
                'description' => 'Corsi di danza contemporanea e hip hop nel centro storico di Firenze',
                'address' => 'Piazza della Signoria 12, 50122 Firenze FI',
                'phone' => '+39 055 5555555',
                'email' => 'info@studiodanzafirenze.it',
                'active' => true,
            ]
        ];

        $createdSchools = [];
        foreach ($schools as $schoolData) {
            $createdSchools[] = School::create($schoolData);
        }

        // ===========================================
        // STEP 2: Create Super Admin
        // ===========================================
        $this->command->info('👑 Creating Super Admin...');

        $superAdmin = User::create([
            'name' => 'Super Amministratore',
            'first_name' => 'Super',
            'last_name' => 'Amministratore',
            'email' => 'superadmin@scuoladanza.it',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'phone' => '+39 333 0000000',
            'date_of_birth' => '1985-01-01',
            'active' => true,
            'email_verified_at' => now(),
        ]);

        // ===========================================
        // STEP 3: Create School Admins & Instructors
        // ===========================================
        $this->command->info('👨‍💼 Creating admins and instructors...');
        
        $schoolUsers = [];
        foreach ($createdSchools as $index => $school) {
            // Create Admin for each school
            $admin = User::create([
                'name' => "Amministratore {$school->name}",
                'first_name' => 'Amministratore',
                'last_name' => substr($school->name, 0, 20),
                'email' => str_replace('@', "+admin@", $school->email),
                'password' => Hash::make('password'),
                'role' => 'admin',
                'school_id' => $school->id,
                'phone' => '+39 333 111' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'date_of_birth' => '1980-0' . ($index + 1) . '-15',
                'active' => true,
                'email_verified_at' => now(),
            ]);

            // Create 2 Instructors per school
            for ($i = 1; $i <= 2; $i++) {
                $instructor = User::create([
                    'name' => "Istruttore $i - {$school->name}",
                    'first_name' => "Istruttore$i",
                    'last_name' => substr($school->name, 0, 15),
                    'email' => str_replace('@', "+istruttore{$i}@", $school->email),
                    'password' => Hash::make('password'),
                    'role' => 'admin', // Using admin role as instructor equivalent
                    'school_id' => $school->id,
                    'phone' => '+39 333 222' . str_pad(($index * 2 + $i), 4, '0', STR_PAD_LEFT),
                    'date_of_birth' => '1985-0' . ($i + 2) . '-10',
                    'active' => true,
                    'email_verified_at' => now(),
                ]);

                $schoolUsers[$school->id]['instructors'][] = $instructor;
            }

            $schoolUsers[$school->id]['admin'] = $admin;
        }

        // ===========================================
        // STEP 4: Create Students
        // ===========================================
        $this->command->info('🎓 Creating students...');
        
        $studentNames = [
            ['Giulia', 'Ferrari'], ['Marco', 'Rossi'], ['Alessia', 'Bianchi'], 
            ['Luca', 'Romano'], ['Sofia', 'Galli'], ['Andrea', 'Conti'],
            ['Francesca', 'Ricci'], ['Matteo', 'Greco'], ['Chiara', 'Bruno'],
            ['Davide', 'Rizzo'], ['Elena', 'Lombardi'], ['Federico', 'Martini'],
            ['Valentina', 'Fontana'], ['Simone', 'Santoro'], ['Martina', 'Caruso']
        ];

        $allStudents = [];
        foreach ($createdSchools as $schoolIndex => $school) {
            // Create 5 students per school
            for ($i = 1; $i <= 5; $i++) {
                $nameIndex = ($schoolIndex * 5 + $i - 1) % count($studentNames);
                $studentData = $studentNames[$nameIndex];
                
                $student = User::create([
                    'name' => "{$studentData[0]} {$studentData[1]}",
                    'first_name' => $studentData[0],
                    'last_name' => $studentData[1],
                    'email' => strtolower($studentData[0]) . '.' . strtolower($studentData[1]) . ($schoolIndex + 1) . "@example.com",
                    'password' => Hash::make('password'),
                    'role' => 'user', // Using user role as student equivalent
                    'school_id' => $school->id,
                    'phone' => '+39 333 333' . str_pad(($schoolIndex * 5 + $i), 4, '0', STR_PAD_LEFT),
                    'date_of_birth' => '199' . (5 + $i % 5) . '-0' . (($i % 12) + 1) . '-' . str_pad(($i * 3) % 28 + 1, 2, '0', STR_PAD_LEFT),
                    'active' => true,
                    'email_verified_at' => now(),
                ]);

                $allStudents[] = $student;
                $schoolUsers[$school->id]['students'][] = $student;
            }
        }

        // ===========================================
        // STEP 5: Create Courses
        // ===========================================
        $this->command->info('🎵 Creating courses...');
        
        $courseTemplates = [
            [
                'name' => 'Danza Classica - Principianti',
                'description' => 'Corso base di danza classica per chi inizia. Include tecnica di base, posizioni fondamentali e primi esercizi alla sbarra.',
                'difficulty_level' => 'beginner',
                'duration_weeks' => 12,
                'price' => 120.00,
                'max_students' => 15,
                'schedule' => 'Lunedì e Mercoledì 18:00-19:30'
            ],
            [
                'name' => 'Hip Hop - Intermedio',
                'description' => 'Corso di hip hop per studenti con esperienza base. Focus su ritmo, coordinazione e freestyle.',
                'difficulty_level' => 'intermediate',
                'duration_weeks' => 10,
                'price' => 150.00,
                'max_students' => 12,
                'schedule' => 'Martedì e Giovedì 19:00-20:30'
            ],
            [
                'name' => 'Danza Moderna - Avanzato',
                'description' => 'Corso avanzato di danza moderna con focus su espressività e tecnica contemporanea.',
                'difficulty_level' => 'advanced',
                'duration_weeks' => 14,
                'price' => 180.00,
                'max_students' => 10,
                'schedule' => 'Mercoledì e Venerdì 20:00-21:30'
            ],
            [
                'name' => 'Danza Contemporanea',
                'description' => 'Esplorazione della danza contemporanea attraverso movimenti fluidi e espressione corporea.',
                'difficulty_level' => 'intermediate',
                'duration_weeks' => 16,
                'price' => 160.00,
                'max_students' => 14,
                'schedule' => 'Sabato 15:00-17:00'
            ]
        ];

        $allCourses = [];
        foreach ($createdSchools as $school) {
            $instructors = $schoolUsers[$school->id]['instructors'];
            
            // Create 3-4 courses per school
            $coursesToCreate = array_slice($courseTemplates, 0, rand(3, 4));
            
            foreach ($coursesToCreate as $index => $template) {
                $instructor = $instructors[$index % count($instructors)];
                
                $course = Course::create([
                    'name' => $template['name'],
                    'description' => $template['description'],
                    'instructor_id' => $instructor->id,
                    'level' => $template['difficulty_level'],
                    'price' => $template['price'],
                    'max_students' => $template['max_students'],
                    'schedule' => json_encode(['description' => $template['schedule']]),
                    'start_date' => now()->addDays(rand(7, 30)),
                    'end_date' => now()->addDays(rand(84, 120)),
                    'school_id' => $school->id,
                    'active' => rand(0, 100) > 10, // 90% active courses
                ]);

                $allCourses[] = $course;
            }
        }

        // ===========================================
        // STEP 6: Create Enrollments and Payments
        // ===========================================
        $this->command->info('📝 Creating enrollments and payments...');
        
        $enrollmentStatuses = ['active', 'completed', 'cancelled', 'pending'];
        $paymentStatuses = ['completed', 'pending', 'failed'];
        $paymentMethods = ['credit_card', 'bank_transfer', 'cash'];

        foreach ($allCourses as $course) {
            // Enroll random number of students (40-80% of max capacity)
            $maxEnrollments = (int)($course->max_students * 0.8);
            $numEnrollments = rand((int)($course->max_students * 0.4), $maxEnrollments);
            
            $courseStudents = collect($schoolUsers[$course->school_id]['students'])
                ->random(min($numEnrollments, count($schoolUsers[$course->school_id]['students'])));

            foreach ($courseStudents as $student) {
                $enrollmentStatus = $enrollmentStatuses[array_rand($enrollmentStatuses)];
                
                // Weight towards active enrollments
                if (rand(1, 100) <= 70) {
                    $enrollmentStatus = 'active';
                }

                $enrollment = CourseEnrollment::create([
                    'user_id' => $student->id,
                    'course_id' => $course->id,
                    'enrollment_date' => now()->subDays(rand(1, 30)),
                    'status' => $enrollmentStatus,
                ]);

                // Create payment for enrollment
                $paymentStatus = $paymentStatuses[array_rand($paymentStatuses)];
                
                // Most payments should be completed for active enrollments
                if ($enrollmentStatus === 'active' && rand(1, 100) <= 85) {
                    $paymentStatus = 'completed';
                }

                Payment::create([
                    'user_id' => $student->id,
                    'school_id' => $student->school_id,
                    'course_id' => $course->id,
                    'amount' => $course->price,
                    'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                    'status' => $paymentStatus,
                    'payment_date' => $paymentStatus === 'completed' ? now()->subDays(rand(1, 25)) : null,
                ]);
            }
        }

        // ===========================================
        // STEP 7: Create Sample Documents
        // ===========================================
        $this->command->info('📄 Creating sample documents...');
        
        $documentCategories = ['medical', 'photo', 'agreement'];
        $documentStatuses = ['pending', 'approved', 'rejected'];

        // Create 2-3 documents for some students
        $studentsWithDocs = collect($allStudents)->random(min(10, count($allStudents)));
        
        foreach ($studentsWithDocs as $student) {
            for ($i = 0; $i < rand(1, 3); $i++) {
                $category = $documentCategories[array_rand($documentCategories)];
                Document::create([
                    'user_id' => $student->id,
                    'school_id' => $student->school_id,
                    'name' => "Documento " . ucfirst($category),
                    'category' => $category,
                    'file_path' => 'documents/sample_' . uniqid() . '.pdf',
                    'file_size' => rand(50000, 2000000),
                    'file_type' => 'pdf',
                    'status' => $documentStatuses[array_rand($documentStatuses)],
                    'uploaded_at' => now()->subDays(rand(1, 15)),
                ]);
            }
        }

        // ===========================================
        // FINAL SUMMARY
        // ===========================================
        $this->command->info('');
        $this->command->line('🎉 <fg=green>Database seeding completed successfully!</fg=green>');
        $this->command->line('');
        $this->command->info('📊 <fg=yellow>SEEDING SUMMARY:</fg=yellow>');
        $this->command->line("   • Schools: " . count($createdSchools));
        $this->command->line("   • Total Users: " . User::count());
        $this->command->line("     - Super Admins: " . User::where('role', 'super_admin')->count());
        $this->command->line("     - Admins: " . User::where('role', 'admin')->count());
        $this->command->line("     - Users/Students: " . User::where('role', 'user')->count());
        $this->command->line("   • Courses: " . Course::count());
        $this->command->line("   • Enrollments: " . CourseEnrollment::count());
        $this->command->line("   • Payments: " . Payment::count());
        $this->command->line("   • Documents: " . Document::count());
        
        $this->command->line('');
        $this->command->info('🔑 <fg=cyan>TEST CREDENTIALS:</fg=cyan>');
        $this->command->line('   Super Admin: <fg=white>superadmin@scuoladanza.it</fg=white> / <fg=white>password</fg=white>');
        $this->command->line('   School Admins:');
        foreach ($createdSchools as $school) {
            $adminEmail = str_replace('@', "+admin@", $school->email);
            $this->command->line("     • {$school->name}: <fg=white>{$adminEmail}</fg=white> / <fg=white>password</fg=white>");
        }
        $this->command->line('   Sample Student: <fg=white>giulia.ferrari1@example.com</fg=white> / <fg=white>password</fg=white>');
        
        $this->command->line('');
        $this->command->info('🌐 <fg=green>Sistema pronto per il testing!</fg=green>');
    }
}
