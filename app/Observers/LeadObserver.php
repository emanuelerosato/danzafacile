<?php

namespace App\Observers;

use App\Models\Lead;
use App\Models\EmailTemplate;
use App\Models\LeadEmailLog;
use Illuminate\Support\Facades\Log;

class LeadObserver
{
    /**
     * Handle the Lead "created" event.
     *
     * Quando viene creato un nuovo lead, schedula automaticamente
     * tutte le email del funnel attivo
     */
    public function created(Lead $lead): void
    {
        // Ottieni tutti i template attivi ordinati per sequenza
        $templates = EmailTemplate::active()->ordered()->get();

        if ($templates->isEmpty()) {
            Log::warning("Nessun template email attivo trovato per schedulare il funnel");
            return;
        }

        // Schedula ogni email del funnel
        foreach ($templates as $template) {
            // Calcola quando deve essere inviata
            $scheduledAt = $lead->created_at->copy()->addDays($template->delay_days);

            // Sostituisci placeholder con dati del lead
            $emailData = $template->fillPlaceholders($lead);

            // Crea log email schedulata
            LeadEmailLog::create([
                'lead_id' => $lead->id,
                'email_template_id' => $template->id,
                'subject' => $emailData['subject'],
                'body' => $emailData['body'],
                'status' => 'scheduled',
                'scheduled_at' => $scheduledAt,
            ]);

            Log::info("Email funnel schedulata", [
                'lead_id' => $lead->id,
                'lead_name' => $lead->name,
                'template' => $template->name,
                'scheduled_at' => $scheduledAt->toDateTimeString(),
            ]);
        }

        Log::info("Funnel completo schedulato per nuovo lead", [
            'lead_id' => $lead->id,
            'lead_name' => $lead->name,
            'email_count' => $templates->count(),
        ]);
    }
}
