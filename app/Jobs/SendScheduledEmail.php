<?php

namespace App\Jobs;

use App\Models\LeadEmailLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendScheduledEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $leadEmailLog;

    /**
     * Create a new job instance.
     */
    public function __construct(LeadEmailLog $leadEmailLog)
    {
        $this->leadEmailLog = $leadEmailLog;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Verifica che l'email sia ancora da inviare
            if ($this->leadEmailLog->status !== 'scheduled') {
                Log::info("Email già processata: {$this->leadEmailLog->id}");
                return;
            }

            $lead = $this->leadEmailLog->lead;

            // Invia email
            Mail::send([], [], function ($message) use ($lead) {
                $message->to($lead->email, $lead->name)
                    ->subject($this->leadEmailLog->subject)
                    ->html($this->leadEmailLog->body);
            });

            // Aggiorna status a sent
            $this->leadEmailLog->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            Log::info("Email inviata con successo", [
                'lead_id' => $lead->id,
                'email_log_id' => $this->leadEmailLog->id,
                'template' => $this->leadEmailLog->emailTemplate->name,
            ]);

        } catch (\Exception $e) {
            // Logga errore
            $this->leadEmailLog->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error("Errore invio email", [
                'lead_id' => $this->leadEmailLog->lead_id,
                'email_log_id' => $this->leadEmailLog->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
