<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // App Basic Settings
            [
                'key' => 'app_name',
                'value' => 'Scuola di Danza',
                'type' => 'string',
                'description' => 'Nome dell\'applicazione mostrato nella sidebar e nei layout'
            ],
            [
                'key' => 'app_description',
                'value' => 'Sistema di gestione per scuole di danza',
                'type' => 'string',
                'description' => 'Descrizione dell\'applicazione'
            ],
            [
                'key' => 'app_logo',
                'value' => 'SD',
                'type' => 'string',
                'description' => 'Acronimo/sigla mostrata nel logo (max 3 caratteri)'
            ],

            // Contact Information
            [
                'key' => 'contact_email',
                'value' => 'info@danzafacile.it',
                'type' => 'string',
                'description' => 'Email di contatto principale'
            ],
            [
                'key' => 'contact_phone',
                'value' => '+39 123 456 7890',
                'type' => 'string',
                'description' => 'Numero di telefono di contatto'
            ],

            // System Settings
            [
                'key' => 'timezone',
                'value' => 'Europe/Rome',
                'type' => 'string',
                'description' => 'Fuso orario dell\'applicazione'
            ],
            [
                'key' => 'default_language',
                'value' => 'it',
                'type' => 'string',
                'description' => 'Lingua di default dell\'applicazione'
            ]
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}