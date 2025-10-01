<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Security Configuration Check Command
 *
 * SECURITY: Validates production security settings to ensure:
 * - Debug mode is disabled in production
 * - APP_ENV is set correctly
 * - Sensitive data exposure is minimized
 * - Critical security configurations are in place
 */
class SecurityCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:check
                            {--strict : Fail on warnings (for CI/CD)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check security configuration for production readiness';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔒 Running Security Configuration Check...');
        $this->newLine();

        $errors = [];
        $warnings = [];
        $passed = [];

        // CHECK #1: Debug Mode
        if (config('app.debug') === true && config('app.env') === 'production') {
            $errors[] = 'APP_DEBUG is enabled in production! This exposes sensitive information.';
        } elseif (config('app.debug') === false) {
            $passed[] = 'Debug mode is disabled ✓';
        } else {
            $warnings[] = 'Debug mode is enabled (OK for development)';
        }

        // CHECK #2: Environment
        if (config('app.env') === 'production' && config('app.debug') === true) {
            $errors[] = 'Production environment with debug enabled!';
        } elseif (config('app.env') === 'production') {
            $passed[] = 'Environment is set to production ✓';
        } else {
            $warnings[] = 'Environment is "' . config('app.env') . '" (not production)';
        }

        // CHECK #3: APP_KEY
        if (empty(config('app.key'))) {
            $errors[] = 'APP_KEY is not set! Run: php artisan key:generate';
        } else {
            $passed[] = 'APP_KEY is configured ✓';
        }

        // CHECK #4: HTTPS in Production
        if (config('app.env') === 'production' && !str_starts_with(config('app.url'), 'https://')) {
            $warnings[] = 'APP_URL should use HTTPS in production';
        } elseif (str_starts_with(config('app.url'), 'https://')) {
            $passed[] = 'APP_URL uses HTTPS ✓';
        }

        // CHECK #5: Log Level
        if (config('app.env') === 'production' && config('logging.channels.single.level') === 'debug') {
            $warnings[] = 'Log level is "debug" in production (consider "error" or "warning")';
        }

        // CHECK #6: PayPal Webhook Verification
        if (config('paypal.webhook_verification.enabled') === false) {
            $warnings[] = 'PayPal webhook verification is disabled';
        } else {
            $passed[] = 'PayPal webhook verification is enabled ✓';
        }

        // CHECK #7: Session Driver
        if (config('app.env') === 'production' && config('session.driver') === 'file') {
            $warnings[] = 'Session driver is "file" (consider "database" or "redis" for production)';
        }

        // CHECK #8: Queue Connection
        if (config('app.env') === 'production' && config('queue.default') === 'sync') {
            $warnings[] = 'Queue connection is "sync" (consider "redis" or "database" for production)';
        }

        // Display Results
        $this->newLine();
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('  SECURITY CHECK RESULTS');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();

        if (!empty($errors)) {
            $this->error('❌ CRITICAL ERRORS (' . count($errors) . '):');
            foreach ($errors as $error) {
                $this->line('   • ' . $error);
            }
            $this->newLine();
        }

        if (!empty($warnings)) {
            $this->warn('⚠️  WARNINGS (' . count($warnings) . '):');
            foreach ($warnings as $warning) {
                $this->line('   • ' . $warning);
            }
            $this->newLine();
        }

        if (!empty($passed)) {
            $this->info('✅ PASSED CHECKS (' . count($passed) . '):');
            foreach ($passed as $pass) {
                $this->line('   • ' . $pass);
            }
            $this->newLine();
        }

        // Summary
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        if (!empty($errors)) {
            $this->error('Security check FAILED! Fix critical errors before deploying.');
            return self::FAILURE;
        }

        if (!empty($warnings) && $this->option('strict')) {
            $this->warn('Security check passed with warnings (strict mode enabled).');
            return self::FAILURE;
        }

        $this->info('✅ Security check PASSED!');

        if (!empty($warnings)) {
            $this->warn('Note: ' . count($warnings) . ' warning(s) found. Review recommended.');
        }

        return self::SUCCESS;
    }
}
