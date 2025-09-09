<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\School;
use App\Models\Course;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crea una scuola di esempio
        $school = School::create([
            'name' => 'Scuola di Danza Eleganza',
            'description' => 'Una scuola di danza moderna nel centro città',
            'address' => 'Via Roma 123, Milano',
            'phone' => '+39 02 1234567',
            'email' => 'info@eleganza.it',
            'active' => true,
        ]);

        // Crea un Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'superadmin@scuoladanza.it',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'active' => true,
            'email_verified_at' => now(),
        ]);

        // Crea un Admin per la scuola
        $admin = User::create([
            'name' => 'Admin Eleganza',
            'first_name' => 'Maria',
            'last_name' => 'Rossi',
            'email' => 'admin@eleganza.it',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'school_id' => $school->id,
            'phone' => '+39 333 1234567',
            'active' => true,
            'email_verified_at' => now(),
        ]);

        // Crea alcuni studenti
        $students = [];
        for ($i = 1; $i <= 5; $i++) {
            $students[] = User::create([
                'name' => "Studente $i",
                'first_name' => "Nome$i",
                'last_name' => "Cognome$i",
                'email' => "studente$i@example.com",
                'password' => Hash::make('password'),
                'role' => 'user',
                'school_id' => $school->id,
                'phone' => '+39 333 ' . str_pad($i, 7, '0', STR_PAD_LEFT),
                'active' => true,
                'email_verified_at' => now(),
            ]);
        }

        // Crea alcuni corsi
        $courses = [
            [
                'name' => 'Danza Classica - Principianti',
                'description' => 'Corso base di danza classica per principianti',
                'level' => 'principiante',
                'max_students' => 15,
                'price' => 80.00,
                'start_date' => now()->addDays(7),
                'end_date' => now()->addDays(97),
                'schedule' => json_encode([
                    'lunedi' => '18:00-19:30',
                    'mercoledi' => '18:00-19:30',
                ]),
                'instructor_id' => $admin->id,
                'school_id' => $school->id,
                'active' => true,
            ],
            [
                'name' => 'Hip Hop - Intermedio',
                'description' => 'Corso di hip hop per studenti con esperienza',
                'level' => 'intermedio',
                'max_students' => 12,
                'price' => 90.00,
                'start_date' => now()->addDays(14),
                'end_date' => now()->addDays(104),
                'schedule' => json_encode([
                    'martedi' => '19:00-20:30',
                    'venerdi' => '19:00-20:30',
                ]),
                'instructor_id' => $admin->id,
                'school_id' => $school->id,
                'active' => true,
            ],
            [
                'name' => 'Danza Moderna - Avanzato',
                'description' => 'Corso avanzato di danza moderna',
                'level' => 'avanzato',
                'max_students' => 10,
                'price' => 100.00,
                'start_date' => now()->addDays(21),
                'end_date' => now()->addDays(111),
                'schedule' => json_encode([
                    'giovedi' => '20:00-21:30',
                    'sabato' => '15:00-16:30',
                ]),
                'instructor_id' => $admin->id,
                'school_id' => $school->id,
                'active' => true,
            ]
        ];

        foreach ($courses as $courseData) {
            Course::create($courseData);
        }

        $this->command->info('Database seeded successfully!');
        $this->command->info('Super Admin: superadmin@scuoladanza.it / password');
        $this->command->info('Admin: admin@eleganza.it / password');
        $this->command->info('Studenti: studente1@example.com to studente5@example.com / password');
    }
}
