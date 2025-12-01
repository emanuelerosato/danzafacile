<?php

namespace App\Jobs;

use App\Mail\ThankYouPostEventMail;
use App\Models\EventRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendThankYouEmail implements ShouldQueue
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
        // Invia solo a registrazioni che hanno fatto check-in
        if (!$this->registration->checked_in_at) {
            return;
        }

        $user = $this->registration->user;
        $event = $this->registration->event;

        Mail::to($user->email)->send(
            new ThankYouPostEventMail($user, $event, $this->registration)
        );

        Log::info('Thank you email sent', [
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
        Log::error('Thank you email failed', [
            'registration_id' => $this->registration->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
