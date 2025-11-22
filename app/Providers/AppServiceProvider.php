<?php

namespace App\Providers;

use App\Models\Lead;
use App\Observers\LeadObserver;
use App\View\Composers\AppSettingsComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        // SECURITY: Configure API rate limiters
        RateLimiter::for('api-public', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        RateLimiter::for('api-auth', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('api-sensitive', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });

        // Register View Composer for app settings
        View::composer([
            'components.sidebar',
            'layouts.app',
            'layouts.guest'
        ], AppSettingsComposer::class);

        // Register Lead Observer for email funnel automation
        Lead::observe(LeadObserver::class);
    }
}
