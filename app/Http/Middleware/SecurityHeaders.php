<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Security Headers Middleware
 *
 * SECURITY: Implements comprehensive security headers to protect against:
 * - XSS attacks (Content-Security-Policy)
 * - Clickjacking (X-Frame-Options)
 * - MIME type sniffing (X-Content-Type-Options)
 * - XSS filter bypass (X-XSS-Protection)
 * - Information disclosure (X-Powered-By removal)
 * - Downgrade attacks (Strict-Transport-Security)
 */
class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // SECURITY HEADER #1: Content Security Policy (CSP)
        // Prevents XSS by controlling which resources can be loaded

        $isDevelopment = config('app.env') !== 'production';

        // Base CSP directives
        $csp = [
            "default-src 'self'",
        ];

        // Script sources (different for dev/prod)
        if ($isDevelopment) {
            // Development: Allow Vite HMR and unpkg CDN
            $csp[] = "script-src 'self' 'unsafe-inline' 'unsafe-eval' http://localhost:5173 https://cdn.jsdelivr.net https://unpkg.com https://www.paypal.com https://www.paypalobjects.com";
        } else {
            // Production: Stricter policy
            $csp[] = "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://unpkg.com https://www.paypal.com https://www.paypalobjects.com";
        }

        // Style sources (different for dev/prod)
        if ($isDevelopment) {
            // Development: Allow Vite HMR and fonts.bunny.net
            $csp[] = "style-src 'self' 'unsafe-inline' http://localhost:5173 https://fonts.googleapis.com https://fonts.bunny.net https://cdn.jsdelivr.net";
        } else {
            // Production: Stricter policy
            $csp[] = "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.bunny.net https://cdn.jsdelivr.net";
        }

        // Font sources
        $csp[] = "font-src 'self' https://fonts.gstatic.com https://fonts.bunny.net data:";

        // Image sources
        $csp[] = "img-src 'self' data: https: blob:";

        // Connect sources (different for dev/prod)
        if ($isDevelopment) {
            // Development: Allow Vite WebSocket and source maps
            $csp[] = "connect-src 'self' ws://localhost:5173 http://localhost:5173 https://cdn.jsdelivr.net https://api.paypal.com https://api-m.paypal.com";
        } else {
            // Production: No localhost
            $csp[] = "connect-src 'self' https://cdn.jsdelivr.net https://api.paypal.com https://api-m.paypal.com";
        }

        // Other directives (same for dev/prod)
        $csp[] = "frame-src 'self' https://www.paypal.com";
        $csp[] = "object-src 'none'";
        $csp[] = "base-uri 'self'";
        $csp[] = "form-action 'self'";
        $csp[] = "frame-ancestors 'self'";

        // Only upgrade to HTTPS in production
        if (!$isDevelopment) {
            $csp[] = "upgrade-insecure-requests";
        }

        $response->headers->set('Content-Security-Policy', implode('; ', $csp));

        // SECURITY HEADER #2: X-Frame-Options
        // Prevents clickjacking attacks
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // SECURITY HEADER #3: X-Content-Type-Options
        // Prevents MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // SECURITY HEADER #4: X-XSS-Protection
        // Enables browser's XSS filter (legacy browsers)
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // SECURITY HEADER #5: Referrer-Policy
        // Controls how much referrer information is sent
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // SECURITY HEADER #6: Permissions-Policy (formerly Feature-Policy)
        // Controls which browser features can be used
        $permissions = [
            'accelerometer=()',
            'camera=()',
            'geolocation=(self)',
            'microphone=()',
            'payment=(self)',
            'usb=()',
        ];
        $response->headers->set('Permissions-Policy', implode(', ', $permissions));

        // SECURITY HEADER #7: Strict-Transport-Security (HSTS)
        // Forces HTTPS connections (only in production)
        if (config('app.env') === 'production') {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // SECURITY HEADER #8: Remove X-Powered-By
        // Prevents information disclosure about server technology
        $response->headers->remove('X-Powered-By');

        // SECURITY HEADER #9: Cross-Origin-Resource-Policy
        // Prevents cross-origin resource loading
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');

        // SECURITY HEADER #10: Cross-Origin-Embedder-Policy
        // Enables cross-origin isolation
        $response->headers->set('Cross-Origin-Embedder-Policy', 'require-corp');

        // SECURITY HEADER #11: Cross-Origin-Opener-Policy
        // Isolates browsing context
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');

        return $response;
    }
}
