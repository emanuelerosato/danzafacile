<?php

namespace App\Providers;

use App\Models\Lead;
use App\Observers\LeadObserver;
use App\View\Composers\AppSettingsComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register View Composer for app settings
        // Apply to all views that need app settings (sidebar, layout, etc.)
        View::composer([
            'components.sidebar',
            'layouts.app',
            'layouts.guest'
        ], AppSettingsComposer::class);

        // Register Lead Observer for email funnel automation
        Lead::observe(LeadObserver::class);
    }
}
