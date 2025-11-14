<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'sequence_order',
        'delay_days',
        'subject',
        'body',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sequence_order' => 'integer',
        'delay_days' => 'integer',
    ];

    /**
     * Relazione con i log email
     */
    public function emailLogs()
    {
        return $this->hasMany(LeadEmailLog::class);
    }

    /**
     * Scope per template attivi
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope per ordinare per sequenza
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sequence_order');
    }

    /**
     * Sostituisce i placeholder con i dati del lead
     */
    public function fillPlaceholders($lead): array
    {
        $replacements = [
            '{{Nome}}' => $lead->name,
            '{{Email}}' => $lead->email,
            '{{Telefono}}' => $lead->phone,
            '{{Scuola}}' => $lead->school_name ?? 'la tua scuola',
        ];

        return [
            'subject' => str_replace(array_keys($replacements), array_values($replacements), $this->subject),
            'body' => str_replace(array_keys($replacements), array_values($replacements), $this->body),
        ];
    }
}
