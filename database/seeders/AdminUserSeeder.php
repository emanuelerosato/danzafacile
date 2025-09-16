<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\School;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crea una scuola se non esiste
        $school = School::firstOrCreate(
            ['email' => 'test@scuola.it'],
            [
                'name' => 'Scuola Test',
                'description' => 'Scuola di test per amministrazione',
                'address' => 'Via Test, 1',
                'city' => 'Roma',
                'postal_code' => '00100',
                'phone' => '+39 06 12345678',
                'email' => 'test@scuola.it',
                'website' => null,
                'active' => true,
            ]
        );

        // Crea utente admin se non esiste
        $admin = User::firstOrCreate(
            ['email' => 'admin@test.it'],
            [
                'name' => 'Admin Test',
                'first_name' => 'Admin',
                'last_name' => 'Test',
                'email' => 'admin@test.it',
                'password' => Hash::make('password'),
                'school_id' => $school->id,
                'role' => 'admin',
                'phone' => '+39 333 1111111',
                'date_of_birth' => '1980-01-01',
                'address' => 'Via Test, 1',
                'active' => true,
            ]
        );

        $this->command->info("Admin creato: {$admin->email} / password");
        $this->command->info("Scuola: {$school->name}");
    }
}