<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * SENIOR FIX: Popola first_name e last_name dai dati name esistenti
     *
     * Problema: 13/122 studenti (10.7%) hanno first_name e last_name NULL
     * Causa: Data migration incompleta da vecchio schema
     * Fix: Estrae first_name e last_name dal campo name dove mancanti
     */
    public function up(): void
    {
        DB::transaction(function () {
            // Get all users with NULL first_name or last_name but valid name
            $usersToFix = DB::table('users')
                ->where(function($query) {
                    $query->whereNull('first_name')
                          ->orWhereNull('last_name');
                })
                ->whereNotNull('name')
                ->where('name', '!=', '')
                ->get();

            $fixed = 0;
            $skipped = 0;

            foreach ($usersToFix as $user) {
                // Parse name into first_name and last_name
                $nameParts = explode(' ', trim($user->name));

                if (count($nameParts) >= 2) {
                    // Standard case: "FirstName LastName" or "FirstName MiddleName LastName"
                    $firstName = $nameParts[0];
                    $lastName = implode(' ', array_slice($nameParts, 1)); // All remaining parts as last name

                    DB::table('users')
                        ->where('id', $user->id)
                        ->update([
                            'first_name' => $user->first_name ?? $firstName,
                            'last_name' => $user->last_name ?? $lastName,
                            'updated_at' => now(),
                        ]);

                    $fixed++;
                } elseif (count($nameParts) === 1) {
                    // Edge case: Single word name (e.g., "Madonna")
                    // Set both first_name and last_name to same value
                    $firstName = $nameParts[0];

                    DB::table('users')
                        ->where('id', $user->id)
                        ->update([
                            'first_name' => $user->first_name ?? $firstName,
                            'last_name' => $user->last_name ?? $firstName,
                            'updated_at' => now(),
                        ]);

                    $fixed++;
                } else {
                    // Skip empty or invalid names
                    $skipped++;
                }
            }

            // Log results
            \Log::info('Data migration: populated first_name/last_name from name', [
                'total_found' => $usersToFix->count(),
                'fixed' => $fixed,
                'skipped' => $skipped,
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * CAUTION: This rollback will SET first_name and last_name to NULL
     * for users who had them populated by this migration.
     * Only run if you're absolutely sure you want to revert.
     */
    public function down(): void
    {
        // Rollback is intentionally NO-OP for data safety
        // If you really need to rollback, manually identify and update records
        \Log::warning('Migration rollback attempted: populate_first_last_names_from_name', [
            'action' => 'no-op',
            'reason' => 'data_safety - manual rollback required if needed'
        ]);

        // Uncomment below ONLY if you absolutely need to rollback
        // DB::table('users')
        //     ->where('first_name', '!=', '')
        //     ->whereNotNull('last_name')
        //     ->update([
        //         'first_name' => null,
        //         'last_name' => null,
        //     ]);
    }
};
