<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'name',
        'description',
        'type',
        'start_date',
        'end_date',
        'location',
        'max_participants',
        'price',
        'requires_registration',
        'registration_deadline',
        'requirements',
        'image_path',
        'active',
        'is_public'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'registration_deadline' => 'datetime',
        'requirements' => 'array',
        'price' => 'decimal:2',
        'requires_registration' => 'boolean',
        'active' => 'boolean',
        'is_public' => 'boolean'
    ];

    // Automatically filter by school for non-super-admin users
    protected static function booted(): void
    {
        static::addGlobalScope('school', function (Builder $builder) {
            $user = Auth::user();
            if ($user && !$user->isSuperAdmin() && $user->school_id) {
                $builder->where('school_id', $user->school_id);
            }
        });

        // Automatically set school_id when creating
        static::creating(function (Event $event) {
            $user = Auth::user();
            if ($user && !$user->isSuperAdmin() && $user->school_id && !$event->school_id) {
                $event->school_id = $user->school_id;
            }
        });
    }

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function registrations()
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function attendance()
    {
        return $this->morphMany(Attendance::class, 'attendable');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'event_id');
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('start_date', '>', now());
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    // Accessors & Mutators
    public function getIsUpcomingAttribute(): bool
    {
        return $this->start_date > now();
    }

    public function getIsOngoingAttribute(): bool
    {
        return $this->start_date <= now() && $this->end_date >= now();
    }

    public function getCurrentRegistrationsCountAttribute(): int
    {
        return $this->registrations()
            ->whereIn('status', ['registered', 'confirmed'])
            ->count();
    }

    public function getAvailableSpotsAttribute(): ?int
    {
        if (!$this->max_participants) {
            return null;
        }
        return max(0, $this->max_participants - $this->current_registrations_count);
    }

    public function getIsFullAttribute(): bool
    {
        if (!$this->max_participants) {
            return false;
        }
        return $this->current_registrations_count >= $this->max_participants;
    }

    public function getRegistrationStatusAttribute(): string
    {
        if (!$this->requires_registration) {
            return 'not_required';
        }

        if ($this->registration_deadline && $this->registration_deadline < now()) {
            return 'closed';
        }

        if ($this->is_full) {
            return 'full';
        }

        return 'open';
    }
}