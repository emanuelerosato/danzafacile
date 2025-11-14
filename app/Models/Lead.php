<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Lead extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'school_name',
        'students_count',
        'message',
        'status',
        'notes',
        'contacted_at',
        'demo_sent_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'contacted_at' => 'datetime',
        'demo_sent_at' => 'datetime',
    ];

    /**
     * Ottieni la label per lo status
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'nuovo' => 'Nuovo',
            'contattato' => 'Contattato',
            'demo_inviata' => 'Demo Inviata',
            'interessato' => 'Interessato',
            'chiuso_vinto' => 'Chiuso Vinto',
            'chiuso_perso' => 'Chiuso Perso',
            default => $this->status,
        };
    }

    /**
     * Ottieni il colore del badge per lo status
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'nuovo' => 'blue',
            'contattato' => 'yellow',
            'demo_inviata' => 'purple',
            'interessato' => 'green',
            'chiuso_vinto' => 'green',
            'chiuso_perso' => 'red',
            default => 'gray',
        };
    }

    /**
     * Scope per filtrare per status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope per cercare
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('school_name', 'like', "%{$search}%");
        });
    }

    /**
     * Scope per lead nuovi
     */
    public function scopeNew($query)
    {
        return $query->where('status', 'nuovo');
    }

    /**
     * Scope per lead chiusi vinti
     */
    public function scopeWon($query)
    {
        return $query->where('status', 'chiuso_vinto');
    }

    /**
     * Scope per lead chiusi persi
     */
    public function scopeLost($query)
    {
        return $query->where('status', 'chiuso_perso');
    }

    /**
     * Relazione con email logs (funnel)
     */
    public function emailLogs()
    {
        return $this->hasMany(LeadEmailLog::class)->orderBy('scheduled_at');
    }

    /**
     * Ottieni la prossima email da inviare nel funnel
     */
    public function getNextEmailAttribute()
    {
        return $this->emailLogs()
                    ->where('status', 'scheduled')
                    ->orderBy('scheduled_at')
                    ->first();
    }

    /**
     * Ottieni il progresso nel funnel (%)
     */
    public function getFunnelProgressAttribute(): int
    {
        $total = EmailTemplate::active()->count();
        if ($total === 0) return 0;

        $sent = $this->emailLogs()->sent()->count();
        return (int) (($sent / $total) * 100);
    }

    /**
     * Ottieni lo step corrente nel funnel (1-5)
     */
    public function getCurrentFunnelStepAttribute(): int
    {
        $sent = $this->emailLogs()->sent()->count();
        return min($sent + 1, 5); // Max 5 step
    }
}
