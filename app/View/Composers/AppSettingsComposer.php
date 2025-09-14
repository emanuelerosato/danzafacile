<?php

namespace App\View\Composers;

use App\Models\Setting;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;

class AppSettingsComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        // Cache delle impostazioni per 60 minuti per ottimizzare le performance
        $appSettings = Cache::remember('app_settings', 3600, function () {
            return [
                'app_name' => Setting::get('app_name', 'School Management System'),
                'app_description' => Setting::get('app_description', 'Sistema di gestione per scuole di danza'),
                'app_logo' => Setting::get('app_logo', 'SD'), // Sigla per il logo
                // Altre impostazioni globali possono essere aggiunte qui
            ];
        });

        $view->with('appSettings', $appSettings);
    }

    /**
     * Clear the app settings cache.
     * Call this method when settings are updated.
     */
    public static function clearCache(): void
    {
        Cache::forget('app_settings');
    }
}