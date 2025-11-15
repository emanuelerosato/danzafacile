<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Str;

class SanitizeStudentNames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'students:sanitize-names';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sanitize student names to proper case (Nome Cognome)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Sanitizing student names...');

        $students = User::where('role', 'student')
            ->where('school_id', 1)
            ->get();

        $updated = 0;
        $unchanged = 0;

        foreach ($students as $student) {
            $oldFirstName = $student->first_name;
            $oldLastName = $student->last_name;
            $oldName = $student->name;

            // Capitalize first letter of each word (handles names like "maria pia")
            $newFirstName = Str::title(Str::lower($oldFirstName));
            $newLastName = Str::title(Str::lower($oldLastName));
            $newName = $newFirstName . ' ' . $newLastName;

            if ($oldFirstName !== $newFirstName || $oldLastName !== $newLastName || $oldName !== $newName) {
                $this->line("  📝 {$oldName} → {$newName}");

                $student->first_name = $newFirstName;
                $student->last_name = $newLastName;
                $student->name = $newName;
                $student->save();

                $updated++;
            } else {
                $unchanged++;
            }
        }

        $this->newLine();
        $this->info("✅ Studenti aggiornati: {$updated}");
        $this->info("⏭️  Studenti già corretti: {$unchanged}");
        $this->info("📊 Totale: " . $students->count());

        return Command::SUCCESS;
    }
}
