<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\School;
use App\Models\User;
use App\Models\SchoolRoom;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Lesson;
use App\Models\NotificationPreference;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TestSchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Crea una scuola di test isolata con dati fittizi per testing push notifications
     */
    public function run(): void
    {
        // 1. Crea scuola di test
        $testSchool = School::create([
            'name' => '[TEST] Scuola Push Notifications',
            'email' => 'test@pushnotif.local',
            'phone' => '+39 000 0000000',
            'address' => 'Via Test 123, TestCity',
            'city' => 'TestCity',
            'postal_code' => '00000',
            'active' => true,
            'description' => 'Scuola test per sistema push notifications',
        ]);

        $this->command->info("✅ Scuola test creata: {$testSchool->name} (ID: {$testSchool->id})");

        // 2. Crea admin scuola test
        $adminTest = User::create([
            'school_id' => $testSchool->id,
            'name' => 'Admin Test',
            'email' => 'admin@test.pushnotif.local',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'active' => true,
        ]);

        $this->command->info("✅ Admin test creato: {$adminTest->email}");

        // 3. Crea 3 studenti test
        $students = [];
        for ($i = 1; $i <= 3; $i++) {
            $student = User::create([
                'school_id' => $testSchool->id,
                'name' => "Studente Test {$i}",
                'email' => "studente{$i}@test.pushnotif.local",
                'password' => Hash::make('password'),
                'role' => 'student',
                'active' => true,
            ]);
            $students[] = $student;

            // Crea preferenze notifiche con defaults variati
            NotificationPreference::create([
                'user_id' => $student->id,
                'enabled' => true,
                'lesson_reminders' => true,
                'reminder_minutes_before' => [60, 120, 30][$i - 1], // 60min, 120min, 30min
                'event_reminders' => true,
                'payment_reminders' => true,
                'system_notifications' => true,
            ]);
        }

        $this->command->info("✅ " . count($students) . " studenti test creati con preferenze notifiche");

        // 4. Crea 2 instructors
        $instructors = [];
        for ($i = 1; $i <= 2; $i++) {
            $instructor = User::create([
                'school_id' => $testSchool->id,
                'name' => "Istruttore Test {$i}",
                'email' => "istruttore{$i}@test.pushnotif.local",
                'password' => Hash::make('password'),
                'role' => 'user', // Instructor = user role (staff non esiste)
                'active' => true,
            ]);
            $instructors[] = $instructor;
        }

        $this->command->info("✅ " . count($instructors) . " instructors creati");

        // 5. Crea 2 rooms
        $rooms = [];
        foreach (['Sala A', 'Sala B'] as $idx => $roomName) {
            $room = SchoolRoom::create([
                'school_id' => $testSchool->id,
                'name' => $roomName,
                'capacity' => 15,
                'description' => "Sala test per push notifications",
            ]);
            $rooms[] = $room;
        }

        $this->command->info("✅ " . count($rooms) . " sale create");

        // 6. Crea 2 corsi
        $courses = [];
        $courseNames = ['Danza Classica Test', 'Hip Hop Test'];
        foreach ($courseNames as $idx => $courseName) {
            $course = Course::create([
                'school_id' => $testSchool->id,
                'instructor_id' => $instructors[$idx]->id,
                'name' => $courseName,
                'description' => "Corso test per sistema push notifications",
                'price' => 50.00,
                'max_students' => 15,
                'schedule' => 'Lunedì e Giovedì 19:00-20:30',
                'start_date' => Carbon::now()->startOfMonth(),
                'end_date' => Carbon::now()->addMonths(3)->endOfMonth(),
                'active' => true,
            ]);
            $courses[] = $course;

            // Iscrive tutti gli studenti al corso
            foreach ($students as $student) {
                CourseEnrollment::create([
                    'user_id' => $student->id,
                    'course_id' => $course->id,
                    'enrollment_date' => Carbon::now()->subDays(7),
                    'status' => 'active',
                ]);
            }
        }

        $this->command->info("✅ " . count($courses) . " corsi creati e studenti iscritti");

        // 7. Crea 30 giorni di lezioni (2 lezioni/settimana per corso: Lunedì e Giovedì)
        $lessonCount = 0;
        $startDate = Carbon::now();
        $endDate = Carbon::now()->addDays(30);

        foreach ($courses as $courseIdx => $course) {
            $currentDate = $startDate->copy();

            while ($currentDate <= $endDate) {
                // Trova prossimo Lunedì
                $monday = $currentDate->copy()->next(Carbon::MONDAY);
                if ($monday <= $endDate) {
                    Lesson::create([
                        'course_id' => $course->id,
                        'instructor_id' => $instructors[$courseIdx]->id,
                        'room_id' => $rooms[$courseIdx]->id,
                        'lesson_date' => $monday->toDateString(),
                        'start_time' => '19:00:00',
                        'end_time' => '20:30:00',
                        'status' => 'scheduled',
                        'notes' => null,
                    ]);
                    $lessonCount++;
                }

                // Trova prossimo Giovedì
                $thursday = $currentDate->copy()->next(Carbon::THURSDAY);
                if ($thursday <= $endDate) {
                    Lesson::create([
                        'course_id' => $course->id,
                        'instructor_id' => $instructors[$courseIdx]->id,
                        'room_id' => $rooms[$courseIdx]->id,
                        'lesson_date' => $thursday->toDateString(),
                        'start_time' => '19:00:00',
                        'end_time' => '20:30:00',
                        'status' => 'scheduled',
                        'notes' => null,
                    ]);
                    $lessonCount++;
                }

                // Avanza di una settimana
                $currentDate->addWeek();
            }
        }

        $this->command->info("✅ {$lessonCount} lezioni create per i prossimi 30 giorni");

        // Summary
        $this->command->info("\n");
        $this->command->info("========================================");
        $this->command->info("🎯 TEST SCHOOL SETUP COMPLETATO");
        $this->command->info("========================================");
        $this->command->info("Scuola ID: {$testSchool->id}");
        $this->command->info("Admin: admin@test.pushnotif.local (password: password)");
        $this->command->info("Studenti: 3 (studente1@test.pushnotif.local, studente2@, studente3@)");
        $this->command->info("Password studenti: password");
        $this->command->info("Corsi: 2");
        $this->command->info("Lezioni: {$lessonCount}");
        $this->command->info("========================================\n");
    }
}
