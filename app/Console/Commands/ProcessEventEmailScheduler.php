<?php

namespace App\Console\Commands;

use App\Jobs\SendEventReminderEmail;
use App\Jobs\SendThankYouEmail;
use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessEventEmailScheduler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:process-email-scheduler';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processa reminder e thank you email per eventi';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔄 Processing event email scheduler...');

        // 1. Trova eventi che iniziano tra 3 giorni e manda reminder
        $remindersSent = $this->sendReminders();

        // 2. Trova eventi conclusi ieri e manda thank you
        $thankYousSent = $this->sendThankYous();

        $this->info("✅ Reminder: {$remindersSent}, Thank you: {$thankYousSent}");

        return Command::SUCCESS;
    }

    /**
     * Invia reminder per eventi che iniziano tra 3 giorni
     *
     * @return int
     */
    protected function sendReminders(): int
    {
        $targetDate = now()->addDays(3)->startOfDay();

        // Eventi pubblici che iniziano tra 3 giorni
        $events = Event::where('is_public', true)
            ->where('active', true)
            ->whereDate('start_date', $targetDate)
            ->get();

        $count = 0;
        foreach ($events as $event) {
            // Trova registrazioni confermate senza reminder già inviato
            $registrations = EventRegistration::where('event_id', $event->id)
                ->where('status', 'confirmed')
                ->whereNull('reminder_sent_at')
                ->get();

            foreach ($registrations as $registration) {
                SendEventReminderEmail::dispatch($registration);

                // Marca reminder come inviato
                $registration->update(['reminder_sent_at' => now()]);

                $count++;
            }
        }

        Log::info('Event reminders scheduled', ['count' => $count]);

        return $count;
    }

    /**
     * Invia thank you per eventi conclusi ieri
     *
     * @return int
     */
    protected function sendThankYous(): int
    {
        $targetDate = now()->subDay()->startOfDay();

        // Eventi pubblici conclusi ieri
        $events = Event::where('is_public', true)
            ->whereDate('end_date', $targetDate)
            ->get();

        $count = 0;
        foreach ($events as $event) {
            // Trova registrazioni con check-in fatto, senza thank you già inviato
            $registrations = EventRegistration::where('event_id', $event->id)
                ->whereNotNull('checked_in_at')
                ->whereNull('thank_you_sent_at')
                ->get();

            foreach ($registrations as $registration) {
                SendThankYouEmail::dispatch($registration);

                // Marca thank you come inviato
                $registration->update(['thank_you_sent_at' => now()]);

                $count++;
            }
        }

        Log::info('Thank you emails scheduled', ['count' => $count]);

        return $count;
    }
}
