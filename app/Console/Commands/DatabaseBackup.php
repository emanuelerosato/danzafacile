<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DatabaseBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:database 
                            {--compress : Compress the backup file}
                            {--s3 : Upload to S3 storage}
                            {--local : Keep local copy}
                            {--notify : Send notification email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a database backup with optional compression and cloud storage';

    private string $backupPath;
    private string $fileName;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🗄️ Starting database backup process...');
        
        try {
            $this->setupBackupDetails();
            $this->createDatabaseDump();
            
            if ($this->option('compress')) {
                $this->compressBackup();
            }
            
            if ($this->option('s3')) {
                $this->uploadToS3();
            }
            
            $this->cleanupOldBackups();
            
            if ($this->option('notify')) {
                $this->sendNotification();
            }
            
            $this->info('✅ Database backup completed successfully!');
            $this->displayBackupInfo();
            
        } catch (\Exception $e) {
            $this->error('❌ Backup failed: ' . $e->getMessage());
            Log::error('Database backup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
        
        return Command::SUCCESS;
    }

    private function setupBackupDetails(): void
    {
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $this->fileName = "danzafacile_backup_{$timestamp}.sql";
        $this->backupPath = storage_path('app/backups');
        
        if (!file_exists($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }
    }

    private function createDatabaseDump(): void
    {
        $this->task('Creating database dump', function () {
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');
            $port = config('database.connections.mysql.port', 3306);

            $dumpFile = $this->backupPath . '/' . $this->fileName;
            
            // Create mysqldump command
            $command = sprintf(
                'mysqldump --user=%s --password=%s --host=%s --port=%s --single-transaction --routines --triggers %s > %s',
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($database),
                escapeshellarg($dumpFile)
            );

            $process = new \Symfony\Component\Process\Process(['bash', '-c', $command]);
            $process->setTimeout(300); // 5 minutes timeout
            $process->run();

            if (!$process->isSuccessful()) {
                throw new \Exception('mysqldump failed: ' . $process->getErrorOutput());
            }

            // Verify the backup file exists and has content
            if (!file_exists($dumpFile) || filesize($dumpFile) === 0) {
                throw new \Exception('Backup file was not created or is empty');
            }

            return true;
        });
    }

    private function compressBackup(): void
    {
        $this->task('Compressing backup file', function () {
            $sourceFile = $this->backupPath . '/' . $this->fileName;
            $compressedFile = $sourceFile . '.gz';

            $fp = fopen($sourceFile, 'rb');
            $gzfp = gzopen($compressedFile, 'wb9');

            while (!feof($fp)) {
                gzwrite($gzfp, fread($fp, 16384));
            }

            fclose($fp);
            gzclose($gzfp);

            // Remove original file after compression
            unlink($sourceFile);
            
            // Update filename to compressed version
            $this->fileName .= '.gz';

            return true;
        });
    }

    private function uploadToS3(): void
    {
        $this->task('Uploading to S3 storage', function () {
            $localFile = $this->backupPath . '/' . $this->fileName;
            $s3Path = 'backups/' . Carbon::now()->format('Y/m') . '/' . $this->fileName;

            Storage::disk('s3')->put($s3Path, file_get_contents($localFile));

            // Verify upload
            if (!Storage::disk('s3')->exists($s3Path)) {
                throw new \Exception('S3 upload verification failed');
            }

            // Remove local file if not keeping it
            if (!$this->option('local')) {
                unlink($localFile);
            }

            return true;
        });
    }

    private function cleanupOldBackups(): void
    {
        $this->task('Cleaning up old backups', function () {
            $retentionDays = config('backup.retention_days', 30);
            $cutoffDate = Carbon::now()->subDays($retentionDays);

            // Clean local backups
            $files = glob($this->backupPath . '/danzafacile_backup_*');
            foreach ($files as $file) {
                $fileTime = Carbon::createFromTimestamp(filemtime($file));
                if ($fileTime->lt($cutoffDate)) {
                    unlink($file);
                }
            }

            // Clean S3 backups if S3 is enabled
            if ($this->option('s3')) {
                $s3Files = Storage::disk('s3')->files('backups');
                foreach ($s3Files as $file) {
                    $lastModified = Storage::disk('s3')->lastModified($file);
                    $fileTime = Carbon::createFromTimestamp($lastModified);
                    if ($fileTime->lt($cutoffDate)) {
                        Storage::disk('s3')->delete($file);
                    }
                }
            }

            return true;
        });
    }

    private function sendNotification(): void
    {
        $this->task('Sending notification email', function () {
            $backupSize = $this->getHumanReadableSize();
            $notificationEmail = config('backup.notification_email', config('mail.from.address'));

            Mail::raw(
                "Database backup completed successfully.\n\n" .
                "Details:\n" .
                "- File: {$this->fileName}\n" .
                "- Size: {$backupSize}\n" .
                "- Date: " . Carbon::now()->format('Y-m-d H:i:s') . "\n" .
                "- Storage: " . ($this->option('s3') ? 'S3 + Local' : 'Local only') . "\n\n" .
                "System: " . config('app.name'),
                function ($message) use ($notificationEmail) {
                    $message->to($notificationEmail)
                           ->subject('[' . config('app.name') . '] Database Backup Completed');
                }
            );

            return true;
        });
    }

    private function displayBackupInfo(): void
    {
        $backupSize = $this->getHumanReadableSize();
        
        $this->table(
            ['Property', 'Value'],
            [
                ['File Name', $this->fileName],
                ['File Size', $backupSize],
                ['Location', $this->option('s3') ? 'S3 Storage' : 'Local Storage'],
                ['Compressed', $this->option('compress') ? 'Yes' : 'No'],
                ['Created At', Carbon::now()->format('Y-m-d H:i:s')],
            ]
        );
    }

    private function getHumanReadableSize(): string
    {
        $filePath = $this->backupPath . '/' . $this->fileName;
        
        if (!file_exists($filePath)) {
            return 'Unknown';
        }
        
        $bytes = filesize($filePath);
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
