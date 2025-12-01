<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class EventRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'user_id',
        'school_id',
        'status',
        'registration_date',
        'confirmed_at',
        'notes',
        'additional_info',
        'qr_code_token',
        'checked_in_at',
        'checked_in_by'
    ];

    protected $casts = [
        'registration_date' => 'datetime',
        'confirmed_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'additional_info' => 'array'
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
        static::creating(function (EventRegistration $registration) {
            $user = Auth::user();
            if ($user && !$user->isSuperAdmin() && $user->school_id && !$registration->school_id) {
                $registration->school_id = $user->school_id;
            }
        });
    }

    // Relationships
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function eventPayment()
    {
        return $this->hasOne(\App\Models\EventPayment::class);
    }

    public function gdprConsents()
    {
        return $this->hasMany(\App\Models\GdprConsent::class);
    }

    public function checkedInBy()
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['registered', 'confirmed']);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeWaitlist(Builder $query): Builder
    {
        return $query->where('status', 'waitlist');
    }

    // Accessors
    public function getIsActiveAttribute(): bool
    {
        return in_array($this->status, ['registered', 'confirmed']);
    }

    public function getIsConfirmedAttribute(): bool
    {
        return $this->status === 'confirmed';
    }

    public function getIsOnWaitlistAttribute(): bool
    {
        return $this->status === 'waitlist';
    }
}