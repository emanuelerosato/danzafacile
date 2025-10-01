<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
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
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // SECURITY: Global security headers middleware (applies to all responses)
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        // Global middleware che si applica a tutte le richieste web autenticate
        $middleware->web(append: [
            \App\Http\Middleware\SchoolScopeMiddleware::class,
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'school.ownership' => \App\Http\Middleware\SchoolOwnership::class,
            'school.scope' => \App\Http\Middleware\SchoolScopeMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // SECURITY: Custom exception rendering to prevent information disclosure
        $exceptions->render(function (\Throwable $e, $request) {
            // In production, sanitize error messages to prevent information disclosure
            if (config('app.env') === 'production' && !config('app.debug')) {
                // For API requests, return generic JSON error
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Si è verificato un errore. Riprova più tardi.',
                        'error_code' => 'INTERNAL_ERROR'
                    ], 500);
                }

                // For web requests, return generic error view
                // (Laravel's default 500.blade.php will be used)
                return null; // Let Laravel handle it with default view
            }

            // In development, let Laravel show full error details
            return null;
        });
    })->create();
