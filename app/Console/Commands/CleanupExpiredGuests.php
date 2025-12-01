<?php

namespace App\Console\Commands;

use App\Services\GuestRegistrationService;
use Illuminate\Console\Command;

class CleanupExpiredGuests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'guests:cleanup {--days=180 : Giorni prima della scadenza}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pulisce utenti guest scaduti (GDPR compliance)';

    /**
     * Execute the console command.
     */
    public function __construct(
        protected GuestRegistrationService $guestService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');

        $this->info("Pulizia guest più vecchi di {$days} giorni...");

        $count = $this->guestService->cleanupExpiredGuests($days);

        $this->info("✅ {$count} utenti guest archiviati.");

        return Command::SUCCESS;
    }
}
