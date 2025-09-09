<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SecurityAudit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:audit 
                            {--report : Generate detailed security report}
                            {--fix : Attempt to fix security issues automatically}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform comprehensive security audit of the application';

    private array $issues = [];
    private array $warnings = [];
    private array $recommendations = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔒 Starting security audit...');
        $this->newLine();

        $this->checkEnvironmentSecurity();
        $this->checkConfigSecurity();
        $this->checkFilePermissions();
        $this->checkDatabaseSecurity();
        $this->checkSessionSecurity();
        $this->checkCSRFProtection();
        $this->checkPasswordSecurity();
        $this->checkInputValidation();
        $this->checkDependenciesSecurity();
        $this->checkLogsSecurity();

        $this->displayResults();

        if ($this->option('fix')) {
            $this->attemptAutoFixes();
        }

        if ($this->option('report')) {
            $this->generateReport();
        }

        return empty($this->issues) ? Command::SUCCESS : Command::FAILURE;
    }

    private function checkEnvironmentSecurity(): void
    {
        $this->info('🌍 Checking environment security...');

        // Check if APP_DEBUG is false in production
        if (app()->environment('production') && config('app.debug')) {
            $this->issues[] = 'APP_DEBUG is enabled in production environment';
        }

        // Check APP_KEY
        if (!config('app.key')) {
            $this->issues[] = 'APP_KEY is not set';
        } elseif (Str::startsWith(config('app.key'), 'base64:') && strlen(base64_decode(substr(config('app.key'), 7))) !== 32) {
            $this->issues[] = 'APP_KEY appears to be invalid (wrong length)';
        }

        // Check HTTPS enforcement
        if (app()->environment('production') && !config('session.secure')) {
            $this->warnings[] = 'Secure cookies not enforced in production';
        }

        // Check if .env file has correct permissions
        $envPath = base_path('.env');
        if (File::exists($envPath)) {
            $perms = fileperms($envPath) & 0777;
            if ($perms > 0600) {
                $this->issues[] = '.env file has overly permissive permissions (' . decoct($perms) . ')';
            }
        }
    }

    private function checkConfigSecurity(): void
    {
        $this->info('⚙️ Checking configuration security...');

        // Check session configuration
        if (config('session.same_site') !== 'strict' && config('session.same_site') !== 'lax') {
            $this->warnings[] = 'Session same_site policy should be set to "strict" or "lax"';
        }

        // Check CSRF token lifetime
        if (!config('session.lifetime') || config('session.lifetime') > 120) {
            $this->recommendations[] = 'Consider shorter session lifetime for better security';
        }

        // Check password hashing rounds
        if (config('hashing.bcrypt.rounds') < 10) {
            $this->warnings[] = 'BCrypt rounds should be at least 10 for security';
        }

        // Check mail configuration security
        if (config('mail.mailers.smtp.encryption') !== 'tls' && config('mail.mailers.smtp.encryption') !== 'ssl') {
            $this->warnings[] = 'Email should use TLS or SSL encryption';
        }
    }

    private function checkFilePermissions(): void
    {
        $this->info('📁 Checking file permissions...');

        $criticalPaths = [
            storage_path() => 0755,
            storage_path('logs') => 0755,
            storage_path('framework') => 0755,
            bootstrap_path('cache') => 0755,
        ];

        foreach ($criticalPaths as $path => $expectedPerm) {
            if (File::exists($path)) {
                $perms = fileperms($path) & 0777;
                if ($perms > $expectedPerm) {
                    $this->warnings[] = "Path {$path} has overly permissive permissions (" . decoct($perms) . ")";
                }
            }
        }

        // Check for sensitive files in public directory
        $publicPath = public_path();
        $sensitiveFiles = ['.env', 'composer.json', 'composer.lock', '.git'];
        
        foreach ($sensitiveFiles as $file) {
            if (File::exists($publicPath . '/' . $file)) {
                $this->issues[] = "Sensitive file {$file} found in public directory";
            }
        }
    }

    private function checkDatabaseSecurity(): void
    {
        $this->info('🗄️ Checking database security...');

        try {
            // Check for default passwords
            $defaultPasswords = ['password', '123456', 'admin', 'root', ''];
            $usersWithWeakPasswords = DB::table('users')
                ->whereIn('password', array_map(fn($pwd) => bcrypt($pwd), $defaultPasswords))
                ->count();

            if ($usersWithWeakPasswords > 0) {
                $this->issues[] = "Found {$usersWithWeakPasswords} users with default/weak passwords";
            }

            // Check for users without email verification
            $unverifiedUsers = DB::table('users')
                ->whereNull('email_verified_at')
                ->where('created_at', '<', now()->subDays(7))
                ->count();

            if ($unverifiedUsers > 0) {
                $this->warnings[] = "Found {$unverifiedUsers} unverified users older than 7 days";
            }

            // Check for inactive admin users
            $inactiveAdmins = DB::table('users')
                ->where('role', 'admin')
                ->where('active', false)
                ->count();

            if ($inactiveAdmins > 0) {
                $this->recommendations[] = "Found {$inactiveAdmins} inactive admin accounts - consider cleanup";
            }

        } catch (\Exception $e) {
            $this->warnings[] = 'Could not perform database security checks: ' . $e->getMessage();
        }
    }

    private function checkSessionSecurity(): void
    {
        $this->info('🔐 Checking session security...');

        if (config('session.driver') === 'file' && app()->environment('production')) {
            $this->recommendations[] = 'Consider using Redis or database sessions in production';
        }

        if (!config('session.http_only')) {
            $this->issues[] = 'Session cookies should be HTTP only';
        }

        if (app()->environment('production') && !config('session.secure')) {
            $this->issues[] = 'Session cookies should be secure in production';
        }
    }

    private function checkCSRFProtection(): void
    {
        $this->info('🛡️ Checking CSRF protection...');

        // Check if CSRF middleware is properly configured
        $middlewareGroups = config('app.middleware_groups', []);
        
        if (!isset($middlewareGroups['web']) || 
            !in_array(\App\Http\Middleware\VerifyCsrfToken::class, $middlewareGroups['web']) &&
            !in_array(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class, $middlewareGroups['web'])) {
            $this->issues[] = 'CSRF protection middleware not found in web middleware group';
        }
    }

    private function checkPasswordSecurity(): void
    {
        $this->info('🔑 Checking password security policies...');

        // Check password validation rules
        $passwordRules = config('auth.password_validation', []);
        
        if (empty($passwordRules)) {
            $this->recommendations[] = 'Consider implementing password complexity requirements';
        }

        // Check password reset token expiration
        $resetExpiry = config('auth.passwords.users.expire', 60);
        if ($resetExpiry > 60) {
            $this->recommendations[] = 'Password reset tokens should expire within 60 minutes';
        }
    }

    private function checkInputValidation(): void
    {
        $this->info('✅ Checking input validation...');

        // Check for FormRequest classes
        $formRequests = File::glob(app_path('Http/Requests/*.php'));
        
        if (empty($formRequests)) {
            $this->recommendations[] = 'Consider using FormRequest classes for input validation';
        }

        // Check for mass assignment protection
        $models = File::glob(app_path('Models/*.php'));
        
        foreach ($models as $modelFile) {
            $content = File::get($modelFile);
            if (!str_contains($content, '$fillable') && !str_contains($content, '$guarded')) {
                $filename = basename($modelFile);
                $this->warnings[] = "Model {$filename} doesn't have mass assignment protection";
            }
        }
    }

    private function checkDependenciesSecurity(): void
    {
        $this->info('📦 Checking dependencies security...');

        $composerLock = base_path('composer.lock');
        if (!File::exists($composerLock)) {
            $this->warnings[] = 'composer.lock file not found - dependencies not locked';
            return;
        }

        $lockData = json_decode(File::get($composerLock), true);
        $packages = array_merge($lockData['packages'] ?? [], $lockData['packages-dev'] ?? []);

        $outdatedPackages = 0;
        foreach ($packages as $package) {
            // Simple check for very old packages (this could be enhanced)
            if (isset($package['time'])) {
                $packageTime = new \DateTime($package['time']);
                $oneYearAgo = new \DateTime('-1 year');
                
                if ($packageTime < $oneYearAgo) {
                    $outdatedPackages++;
                }
            }
        }

        if ($outdatedPackages > 0) {
            $this->recommendations[] = "Found {$outdatedPackages} packages older than 1 year - consider updating";
        }
    }

    private function checkLogsSecurity(): void
    {
        $this->info('📝 Checking logs security...');

        $logPath = storage_path('logs');
        if (File::exists($logPath)) {
            $perms = fileperms($logPath) & 0777;
            if ($perms > 0755) {
                $this->warnings[] = "Logs directory has overly permissive permissions (" . decoct($perms) . ")";
            }
        }

        // Check log rotation
        $logFiles = File::glob($logPath . '/*.log');
        foreach ($logFiles as $logFile) {
            $size = File::size($logFile);
            if ($size > 100 * 1024 * 1024) { // 100MB
                $this->recommendations[] = "Log file " . basename($logFile) . " is large (" . $this->formatBytes($size) . ") - consider log rotation";
            }
        }
    }

    private function displayResults(): void
    {
        $this->newLine();
        $this->info('🔍 Security Audit Results:');
        $this->newLine();

        // Display critical issues
        if (!empty($this->issues)) {
            $this->error('❌ Critical Security Issues:');
            foreach ($this->issues as $issue) {
                $this->line("  • {$issue}");
            }
            $this->newLine();
        }

        // Display warnings
        if (!empty($this->warnings)) {
            $this->warn('⚠️ Security Warnings:');
            foreach ($this->warnings as $warning) {
                $this->line("  • {$warning}");
            }
            $this->newLine();
        }

        // Display recommendations
        if (!empty($this->recommendations)) {
            $this->info('💡 Security Recommendations:');
            foreach ($this->recommendations as $recommendation) {
                $this->line("  • {$recommendation}");
            }
            $this->newLine();
        }

        // Summary
        $totalIssues = count($this->issues);
        $totalWarnings = count($this->warnings);
        $totalRecommendations = count($this->recommendations);

        if ($totalIssues === 0 && $totalWarnings === 0) {
            $this->info('✅ No critical security issues found!');
        } else {
            $this->table(
                ['Type', 'Count'],
                [
                    ['Critical Issues', $totalIssues],
                    ['Warnings', $totalWarnings],
                    ['Recommendations', $totalRecommendations],
                ]
            );
        }
    }

    private function attemptAutoFixes(): void
    {
        $this->newLine();
        $this->info('🔧 Attempting automatic fixes...');

        $fixes = 0;

        // Fix .env permissions
        $envPath = base_path('.env');
        if (File::exists($envPath)) {
            chmod($envPath, 0600);
            $fixes++;
            $this->line('  ✓ Fixed .env file permissions');
        }

        // Fix storage permissions
        $storagePath = storage_path();
        if (File::exists($storagePath)) {
            chmod($storagePath, 0755);
            $fixes++;
            $this->line('  ✓ Fixed storage directory permissions');
        }

        if ($fixes > 0) {
            $this->info("Applied {$fixes} automatic fixes");
        } else {
            $this->line('No automatic fixes available');
        }
    }

    private function generateReport(): void
    {
        $reportPath = storage_path('app/security-audit-' . now()->format('Y-m-d_H-i-s') . '.json');
        
        $report = [
            'timestamp' => now()->toISOString(),
            'environment' => app()->environment(),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'audit_results' => [
                'critical_issues' => $this->issues,
                'warnings' => $this->warnings,
                'recommendations' => $this->recommendations,
            ],
            'summary' => [
                'total_issues' => count($this->issues),
                'total_warnings' => count($this->warnings),
                'total_recommendations' => count($this->recommendations),
            ]
        ];

        File::put($reportPath, json_encode($report, JSON_PRETTY_PRINT));
        $this->info("Security audit report saved to: {$reportPath}");
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
