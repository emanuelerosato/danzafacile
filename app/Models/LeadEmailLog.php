<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadEmailLog extends Model
{
    protected $fillable = [
        'lead_id',
        'email_template_id',
        'subject',
        'body',
        'status',
        'scheduled_at',
        'sent_at',
        'opened_at',
        'clicked_at',
        'error_message',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
    ];

    /**
     * Relazione con Lead
     */
    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Relazione con EmailTemplate
     */
    public function emailTemplate()
    {
        return $this->belongsTo(EmailTemplate::class);
    }

    /**
     * Scope per email da inviare
     */
    public function scopePending($query)
    {
        return $query->where('status', 'scheduled')
                     ->where('scheduled_at', '<=', now());
    }

    /**
     * Scope per email inviate
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope per email fallite
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Ottieni badge colore status
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'scheduled' => 'blue',
            'sent' => 'green',
            'failed' => 'red',
            'opened' => 'purple',
            'clicked' => 'yellow',
            default => 'gray',
        };
    }

    /**
     * Ottieni label status
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'scheduled' => 'Programmata',
            'sent' => 'Inviata',
            'failed' => 'Fallita',
            'opened' => 'Aperta',
            'clicked' => 'Cliccata',
            default => $this->status,
        };
    }
}
