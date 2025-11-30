<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Google reCAPTCHA Validation Rule
 *
 * Validates reCAPTCHA v2 or v3 responses from Google.
 * Supports both checkbox (v2) and invisible (v3) reCAPTCHA.
 *
 * Usage:
 * ```php
 * $request->validate([
 *     'g-recaptcha-response' => ['required', new Recaptcha],
 * ]);
 * ```
 *
 * Configuration in config/services.php:
 * ```php
 * 'recaptcha' => [
 *     'site_key' => env('RECAPTCHA_SITE_KEY'),
 *     'secret_key' => env('RECAPTCHA_SECRET_KEY'),
 *     'version' => env('RECAPTCHA_VERSION', 'v3'),
 *     'score_threshold' => env('RECAPTCHA_SCORE_THRESHOLD', 0.5),
 *     'enabled' => env('RECAPTCHA_ENABLED', true),
 * ]
 * ```
 */
class Recaptcha implements ValidationRule
{
    /**
     * Google reCAPTCHA verification endpoint
     */
    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * Minimum acceptable score for reCAPTCHA v3 (0.0 to 1.0)
     * Higher is more likely human, lower is more likely bot
     */
    private float $scoreThreshold;

    /**
     * reCAPTCHA version (v2 or v3)
     */
    private string $version;

    /**
     * Whether reCAPTCHA validation is enabled
     */
    private bool $enabled;

    /**
     * Create a new rule instance
     *
     * @param float|null $scoreThreshold Custom score threshold (v3 only)
     * @param string|null $version Custom version override
     */
    public function __construct(?float $scoreThreshold = null, ?string $version = null)
    {
        $this->enabled = config('services.recaptcha.enabled', true);
        $this->version = $version ?? config('services.recaptcha.version', 'v3');
        $this->scoreThreshold = $scoreThreshold ?? config('services.recaptcha.score_threshold', 0.5);

        // Validate score threshold range
        if ($this->scoreThreshold < 0.0 || $this->scoreThreshold > 1.0) {
            throw new \InvalidArgumentException('reCAPTCHA score threshold must be between 0.0 and 1.0');
        }
    }

    /**
     * Run the validation rule
     *
     * @param string $attribute
     * @param mixed $value
     * @param Closure $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Skip validation if reCAPTCHA is disabled (e.g., in development)
        if (!$this->enabled) {
            Log::info('reCAPTCHA validation skipped (disabled in config)');
            return;
        }

        // Check if value is present
        if (empty($value)) {
            $fail('La verifica reCAPTCHA è obbligatoria.');
            return;
        }

        // Get secret key from config
        $secretKey = config('services.recaptcha.secret_key');

        if (empty($secretKey)) {
            Log::error('reCAPTCHA secret key not configured');
            $fail('Errore di configurazione del sistema di verifica.');
            return;
        }

        try {
            // Make request to Google reCAPTCHA API
            $response = Http::asForm()->post(self::VERIFY_URL, [
                'secret' => $secretKey,
                'response' => $value,
                'remoteip' => request()->ip(),
            ]);

            // Check if request was successful
            if (!$response->successful()) {
                Log::error('reCAPTCHA API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                $fail('Impossibile verificare la risposta reCAPTCHA. Riprova.');
                return;
            }

            $result = $response->json();

            // Log the result for debugging
            Log::info('reCAPTCHA validation result', [
                'success' => $result['success'] ?? false,
                'score' => $result['score'] ?? 'N/A',
                'action' => $result['action'] ?? 'N/A',
                'challenge_ts' => $result['challenge_ts'] ?? 'N/A',
                'hostname' => $result['hostname'] ?? 'N/A',
                'error-codes' => $result['error-codes'] ?? [],
            ]);

            // Check if verification was successful
            if (!isset($result['success']) || $result['success'] !== true) {
                $errorCodes = $result['error-codes'] ?? [];
                Log::warning('reCAPTCHA verification failed', [
                    'error_codes' => $errorCodes,
                ]);

                // Provide user-friendly error messages
                $errorMessage = $this->getUserFriendlyErrorMessage($errorCodes);
                $fail($errorMessage);
                return;
            }

            // For reCAPTCHA v3, check the score
            if ($this->version === 'v3') {
                $score = $result['score'] ?? 0.0;

                if ($score < $this->scoreThreshold) {
                    Log::warning('reCAPTCHA score below threshold', [
                        'score' => $score,
                        'threshold' => $this->scoreThreshold,
                        'ip' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                    ]);

                    $fail('La verifica di sicurezza ha rilevato un comportamento sospetto. Riprova o contatta il supporto.');
                    return;
                }

                Log::info('reCAPTCHA v3 validation passed', [
                    'score' => $score,
                    'threshold' => $this->scoreThreshold,
                ]);
            }

            // Validation passed
            Log::info('reCAPTCHA validation successful');

        } catch (\Exception $e) {
            // Log the exception
            Log::error('reCAPTCHA validation exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // In production, fail validation; in development, you might want to allow it
            if (app()->environment('production')) {
                $fail('Errore durante la verifica di sicurezza. Riprova più tardi.');
            } else {
                Log::warning('reCAPTCHA validation failed in development, allowing request');
            }
        }
    }

    /**
     * Get user-friendly error message based on Google error codes
     *
     * @param array $errorCodes
     * @return string
     */
    private function getUserFriendlyErrorMessage(array $errorCodes): string
    {
        if (empty($errorCodes)) {
            return 'Verifica di sicurezza fallita. Riprova.';
        }

        $errorCode = $errorCodes[0];

        return match ($errorCode) {
            'missing-input-secret' => 'Errore di configurazione del sistema.',
            'invalid-input-secret' => 'Errore di configurazione del sistema.',
            'missing-input-response' => 'La verifica reCAPTCHA è obbligatoria.',
            'invalid-input-response' => 'La verifica reCAPTCHA non è valida. Riprova.',
            'bad-request' => 'Richiesta non valida. Riprova.',
            'timeout-or-duplicate' => 'La verifica è scaduta o già utilizzata. Riprova.',
            default => 'Verifica di sicurezza fallita. Riprova.',
        };
    }

    /**
     * Get the validation error message (legacy support)
     *
     * @return string
     */
    public function message(): string
    {
        return 'La verifica reCAPTCHA non è valida.';
    }
}
