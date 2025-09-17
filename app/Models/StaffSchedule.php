<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class StaffSchedule extends Model
{
    protected $fillable = [
        'school_id',
        'staff_id',
        'title',
        'type',
        'date',
        'start_time',
        'end_time',
        'location',
        'description',
        'status',
        'hourly_rate',
        'max_hours',
        'requirements',
        'notes',
        'created_by',
        'confirmed_at',
        'confirmed_by',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'hourly_rate' => 'decimal:2',
        'requirements' => 'array',
        'confirmed_at' => 'datetime',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeForStaff($query, $staffId)
    {
        return $query->where('staff_id', $staffId);
    }

    public function scopeByDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function getDurationAttribute()
    {
        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);
        return $end->diff($start)->format('%H:%I');
    }

    public function getDurationInMinutesAttribute()
    {
        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);
        return $end->diffInMinutes($start);
    }

    public function getCalculatedPayAttribute()
    {
        if (!$this->hourly_rate) {
            return 0;
        }
        $durationInHours = $this->duration_in_minutes / 60;
        return $this->hourly_rate * $durationInHours;
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'scheduled' => '<span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Programmato</span>',
            'confirmed' => '<span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">Confermato</span>',
            'completed' => '<span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Completato</span>',
            'cancelled' => '<span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Annullato</span>',
            'no_show' => '<span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Assente</span>',
        ];

        return $badges[$this->status] ?? $badges['scheduled'];
    }

    public function getTypeLabelAttribute()
    {
        $types = [
            'course' => 'Corso',
            'event' => 'Evento',
            'administrative' => 'Amministrativo',
            'maintenance' => 'Manutenzione',
            'other' => 'Altro',
        ];

        return $types[$this->type] ?? 'Sconosciuto';
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['scheduled', 'confirmed']);
    }

    public function canBeConfirmed(): bool
    {
        return $this->status === 'scheduled';
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['scheduled', 'confirmed']);
    }

    public function canBeCompleted(): bool
    {
        return $this->status === 'confirmed' && $this->date <= now();
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('school', function ($builder) {
            if (auth()->check() && auth()->user()->school_id) {
                $builder->where('school_id', auth()->user()->school_id);
            }
        });
    }
}
