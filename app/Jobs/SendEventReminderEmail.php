<?php

namespace App\Jobs;

use App\Mail\EventReminderMail;
use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEventReminderEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public EventRegistration $registration
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Verifica che la registrazione sia confermata
        if ($this->registration->status !== 'confirmed') {
            return;
        }

        $user = $this->registration->user;
        $event = $this->registration->event;

        // Verifica che l'evento non sia già passato
        if ($event->start_date->isPast()) {
            return;
        }

        Mail::to($user->email)->send(
            new EventReminderMail($user, $event, $this->registration)
        );

        Log::info('Event reminder email sent', [
            'user_id' => $user->id,
            'event_id' => $event->id,
            'registration_id' => $this->registration->id,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Event reminder email failed', [
            'registration_id' => $this->registration->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
