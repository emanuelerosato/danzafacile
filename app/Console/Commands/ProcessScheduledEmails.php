<?php

namespace App\Console\Commands;

use App\Jobs\SendScheduledEmail;
use App\Models\LeadEmailLog;
use Illuminate\Console\Command;

class ProcessScheduledEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:process-scheduled
                            {--limit=50 : Numero massimo di email da processare}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processa e invia le email schedulate del funnel marketing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');

        $this->info("🚀 Inizio processamento email schedulate...");

        // Trova email da inviare (scheduled + scheduled_at <= now)
        $pendingEmails = LeadEmailLog::pending()
            ->with(['lead', 'emailTemplate'])
            ->limit($limit)
            ->get();

        if ($pendingEmails->isEmpty()) {
            $this->info("✅ Nessuna email da inviare al momento");
            return 0;
        }

        $this->info("📧 Trovate {$pendingEmails->count()} email da inviare");

        $sent = 0;
        $failed = 0;

        foreach ($pendingEmails as $emailLog) {
            try {
                $this->line("📤 Invio email a {$emailLog->lead->email} - Template: {$emailLog->emailTemplate->name}");

                // Dispatcha il job per inviare l'email
                SendScheduledEmail::dispatch($emailLog);

                $sent++;
                $this->info("  ✅ Email dispatched per invio");

            } catch (\Exception $e) {
                $failed++;
                $this->error("  ❌ Errore: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info("📊 Riepilogo:");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Email dispatched', $sent],
                ['Email fallite', $failed],
                ['Totale processate', $sent + $failed],
            ]
        );

        return 0;
    }
}
