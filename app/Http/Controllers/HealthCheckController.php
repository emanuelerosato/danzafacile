<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use App\Services\DatabaseOptimizationService;

class HealthCheckController extends Controller
{
    private DatabaseOptimizationService $dbService;

    public function __construct(DatabaseOptimizationService $dbService)
    {
        $this->dbService = $dbService;
    }

    /**
     * Simple health check endpoint
     */
    public function simple(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
            'environment' => app()->environment(),
        ]);
    }

    /**
     * Detailed health check with all services
     */
    public function detailed(Request $request): JsonResponse
    {
        // Verify secret for detailed info
        if ($request->get('secret') !== config('app.health_check_secret')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $checks = [
            'application' => $this->checkApplication(),
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'redis' => $this->checkRedis(),
            'storage' => $this->checkStorage(),
            'mail' => $this->checkMail(),
            'queue' => $this->checkQueue(),
        ];

        $overallStatus = collect($checks)->every(fn($check) => $check['status'] === 'ok') ? 'ok' : 'error';

        return response()->json([
            'status' => $overallStatus,
            'timestamp' => now()->toISOString(),
            'checks' => $checks,
            'system' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'memory_usage' => $this->formatBytes(memory_get_usage(true)),
                'memory_peak' => $this->formatBytes(memory_get_peak_usage(true)),
                'uptime' => $this->getUptime(),
            ]
        ], $overallStatus === 'ok' ? 200 : 503);
    }

    /**
     * Get system metrics for monitoring
     */
    public function metrics(Request $request): JsonResponse
    {
        if ($request->get('secret') !== config('app.health_check_secret')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $metrics = [
            'application' => [
                'environment' => app()->environment(),
                'debug_mode' => config('app.debug'),
                'maintenance_mode' => app()->isDownForMaintenance(),
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
            ],
            'performance' => [
                'memory_usage_bytes' => memory_get_usage(true),
                'memory_peak_bytes' => memory_get_peak_usage(true),
                'memory_usage_formatted' => $this->formatBytes(memory_get_usage(true)),
                'memory_peak_formatted' => $this->formatBytes(memory_get_peak_usage(true)),
            ],
            'database' => $this->getDatabaseMetrics(),
            'cache' => $this->getCacheMetrics(),
            'storage' => $this->getStorageMetrics(),
        ];

        return response()->json($metrics);
    }

    // PRIVATE METHODS

    private function checkApplication(): array
    {
        try {
            $configCached = file_exists(bootstrap_path('cache/config.php'));
            $routesCached = file_exists(bootstrap_path('cache/routes-v7.php'));
            
            return [
                'status' => 'ok',
                'config_cached' => $configCached,
                'routes_cached' => $routesCached,
                'debug_mode' => config('app.debug'),
                'environment' => app()->environment(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    private function checkDatabase(): array
    {
        try {
            $start = microtime(true);
            DB::select('SELECT 1');
            $responseTime = round((microtime(true) - $start) * 1000, 2);
            
            $connectionName = config('database.default');
            $databaseName = config("database.connections.{$connectionName}.database");
            
            return [
                'status' => 'ok',
                'connection' => $connectionName,
                'database' => $databaseName,
                'response_time_ms' => $responseTime,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    private function checkCache(): array
    {
        try {
            $key = 'health_check_' . time();
            $value = 'test_value';
            
            Cache::put($key, $value, 60);
            $retrieved = Cache::get($key);
            Cache::forget($key);
            
            if ($retrieved !== $value) {
                throw new \Exception('Cache value mismatch');
            }
            
            return [
                'status' => 'ok',
                'driver' => config('cache.default'),
                'prefix' => config('cache.prefix'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    private function checkRedis(): array
    {
        try {
            $start = microtime(true);
            Redis::ping();
            $responseTime = round((microtime(true) - $start) * 1000, 2);
            
            return [
                'status' => 'ok',
                'response_time_ms' => $responseTime,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    private function checkStorage(): array
    {
        try {
            $testFile = 'health_check_' . time() . '.txt';
            $testContent = 'Health check test file';
            
            Storage::put($testFile, $testContent);
            $retrieved = Storage::get($testFile);
            Storage::delete($testFile);
            
            if ($retrieved !== $testContent) {
                throw new \Exception('Storage content mismatch');
            }
            
            return [
                'status' => 'ok',
                'default_disk' => config('filesystems.default'),
                'writable' => true,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    private function checkMail(): array
    {
        try {
            $mailer = config('mail.default');
            $host = config("mail.mailers.{$mailer}.host");
            
            return [
                'status' => 'ok',
                'mailer' => $mailer,
                'host' => $host,
                'from_address' => config('mail.from.address'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    private function checkQueue(): array
    {
        try {
            $connection = config('queue.default');
            
            return [
                'status' => 'ok',
                'connection' => $connection,
                'driver' => config("queue.connections.{$connection}.driver"),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    private function getDatabaseMetrics(): array
    {
        try {
            $connectionCounts = DB::select("SHOW STATUS LIKE 'Connections'");
            $queries = DB::select("SHOW STATUS LIKE 'Queries'");
            $uptime = DB::select("SHOW STATUS LIKE 'Uptime'");
            
            return [
                'connections' => $connectionCounts[0]->Value ?? 'Unknown',
                'queries' => $queries[0]->Value ?? 'Unknown',
                'uptime_seconds' => $uptime[0]->Value ?? 'Unknown',
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function getCacheMetrics(): array
    {
        try {
            if (config('cache.default') === 'redis') {
                $info = Redis::info();
                return [
                    'used_memory' => $info['used_memory_human'] ?? 'Unknown',
                    'connected_clients' => $info['connected_clients'] ?? 'Unknown',
                    'total_commands_processed' => $info['total_commands_processed'] ?? 'Unknown',
                ];
            }
            
            return ['driver' => config('cache.default')];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function getStorageMetrics(): array
    {
        try {
            $path = storage_path();
            $totalSpace = disk_total_space($path);
            $freeSpace = disk_free_space($path);
            $usedSpace = $totalSpace - $freeSpace;
            
            return [
                'total_space' => $this->formatBytes($totalSpace),
                'used_space' => $this->formatBytes($usedSpace),
                'free_space' => $this->formatBytes($freeSpace),
                'usage_percentage' => round(($usedSpace / $totalSpace) * 100, 2),
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    private function getUptime(): string
    {
        if (function_exists('sys_getloadavg')) {
            $uptime = shell_exec('uptime');
            return trim($uptime ?: 'Unknown');
        }
        
        return 'Unknown';
    }
}