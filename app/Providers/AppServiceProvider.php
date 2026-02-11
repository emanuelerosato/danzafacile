<?php

namespace App\Providers;

use App\Models\CourseEnrollment;
use App\Models\Lead;
use App\Models\User;
use App\Observers\LeadObserver;
use App\Policies\EnrollmentPolicy;
use App\Policies\UserPolicy;
use App\View\Composers\AppSettingsComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * AUTHORIZATION: Laravel Policies for resource authorization
     * Maps models to their respective policy classes for Gate::allows() and $this->authorize()
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        CourseEnrollment::class => EnrollmentPolicy::class,
    ];

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
        // AUTHORIZATION: Register policies
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }

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

        // SECURITY: Register Blade directive for CSP nonce
        // Note: $cspNonce variable is shared in SecurityHeaders middleware via View::share()
        Blade::directive('cspNonce', function () {
            return "<?php echo \$cspNonce ?? ''; ?>";
        });
    }
}
