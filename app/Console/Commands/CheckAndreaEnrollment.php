<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckAndreaEnrollment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-andrea-enrollment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Andrea Conti enrollment status in course 41';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $course = \App\Models\Course::find(41);
        $this->info("🔍 VERIFICA ANDREA CONTI NEL CORSO 41");
        $this->info("=====================================");
        $this->info("");

        // Cerco Andrea Conti (ID 16)
        $andrea = \App\Models\User::find(16);
        if ($andrea) {
            $this->info("👤 UTENTE TROVATO:");
            $this->info("   - ID: {$andrea->id}");
            $this->info("   - Nome: {$andrea->name}");
            $this->info("   - Email: {$andrea->email}");
            $this->info("");

            // Cerco tutti gli enrollments di Andrea per questo corso
            $enrollments = \App\Models\CourseEnrollment::where('course_id', 41)
                ->where('user_id', $andrea->id)
                ->get();

            $this->info("📋 ENROLLMENTS DI ANDREA NEL CORSO 41:");
            if ($enrollments->count() > 0) {
                foreach($enrollments as $enrollment) {
                    $this->info("   - Enrollment ID: {$enrollment->id}");
                    $this->info("   - Status: {$enrollment->status}");
                    $this->info("   - Data iscrizione: {$enrollment->enrollment_date}");
                    $this->info("   - Created: {$enrollment->created_at}");
                    $this->info("   - Updated: {$enrollment->updated_at}");
                    $this->info("");
                }
            } else {
                $this->info("   ❌ Nessun enrollment trovato!");
                $this->info("");
            }

            // Verifica se appare nella query active enrollments
            $activeEnrollments = $course->enrollments()->with('user')->where('status', 'active')->get();
            $andreaInActive = $activeEnrollments->where('user_id', $andrea->id)->first();

            $this->info("🎯 VERIFICA NELLA QUERY ACTIVE ENROLLMENTS:");
            $this->info("   Totale enrollments attivi: " . $activeEnrollments->count());

            foreach($activeEnrollments as $enrollment) {
                $userName = $enrollment->user ? $enrollment->user->name : 'USER NULL';
                $this->info("   - User ID: {$enrollment->user_id} | Name: {$userName} | Enrollment: {$enrollment->id}");

                if ($enrollment->user_id == 16) {
                    $this->info("     🔍 ANDREA CONTI FOUND!");
                    if (!$enrollment->user) {
                        $this->error("     ❌ RELATIONSHIP USER È NULL!");
                    } else {
                        $this->info("     ✅ User relationship OK: {$enrollment->user->name}");
                    }
                }
            }

            if ($andreaInActive) {
                $this->info("   ✅ Andrea Conti APPARE nella query active enrollments");
                $this->info("   - Enrollment ID: {$andreaInActive->id}");
                $this->info("   - Status: {$andreaInActive->status}");
            } else {
                $this->info("   ❌ Andrea Conti NON APPARE nella query active enrollments");
            }

        } else {
            $this->error("❌ Andrea Conti (ID 16) non trovato nel database!");
        }

        // Verifica anche la route di rimozione
        $this->info("");
        $this->info("🔗 VERIFICA ROUTE DI RIMOZIONE:");
        try {
            $url = route('admin.courses.students.destroy', [41, 16]);
            $this->info("   ✅ Route URL generata: {$url}");
        } catch (\Exception $e) {
            $this->error("   ❌ Errore nella generazione route: " . $e->getMessage());
        }

        $this->info("");
        $this->info("🔍 VERIFICA COMPLETATA!");
    }
}
