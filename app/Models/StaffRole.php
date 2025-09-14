<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class StaffRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'school_id',
        'role_name',
        'description',
        'specializations',
        'hourly_rate',
        'availability',
        'start_date',
        'end_date',
        'permissions',
        'can_mark_attendance',
        'can_view_payments',
        'active'
    ];

    protected $casts = [
        'specializations' => 'array',
        'availability' => 'array',
        'permissions' => 'array',
        'hourly_rate' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'can_mark_attendance' => 'boolean',
        'can_view_payments' => 'boolean',
        'active' => 'boolean'
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
        static::creating(function (StaffRole $staffRole) {
            $user = Auth::user();
            if ($user && !$user->isSuperAdmin() && $user->school_id && !$staffRole->school_id) {
                $staffRole->school_id = $user->school_id;
            }
        });
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class, 'instructor_id', 'user_id')
                    ->where('school_id', $this->school_id);
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function scopeByRole(Builder $query, string $roleName): Builder
    {
        return $query->where('role_name', $roleName);
    }

    public function scopeInstructors(Builder $query): Builder
    {
        return $query->where('role_name', 'Istruttore');
    }

    public function scopeAssistants(Builder $query): Builder
    {
        return $query->where('role_name', 'Assistente');
    }

    public function scopeCurrent(Builder $query): Builder
    {
        $today = now()->toDateString();
        return $query->where('start_date', '<=', $today)
                    ->where(function ($q) use ($today) {
                        $q->whereNull('end_date')
                          ->orWhere('end_date', '>=', $today);
                    });
    }

    public function scopeCanMarkAttendance(Builder $query): Builder
    {
        return $query->where('can_mark_attendance', true);
    }

    public function scopeCanViewPayments(Builder $query): Builder
    {
        return $query->where('can_view_payments', true);
    }

    // Accessors
    public function getIsActiveAttribute(): bool
    {
        if (!$this->active) {
            return false;
        }

        $today = now()->toDateString();

        if ($this->start_date > $today) {
            return false;
        }

        if ($this->end_date && $this->end_date < $today) {
            return false;
        }

        return true;
    }

    public function getIsCurrentAttribute(): bool
    {
        return $this->is_active;
    }

    public function getSpecializationsListAttribute(): string
    {
        if (!$this->specializations || !is_array($this->specializations)) {
            return 'Nessuna specializzazione';
        }

        return implode(', ', $this->specializations);
    }

    public function getAvailabilityDescriptionAttribute(): string
    {
        if (!$this->availability || !is_array($this->availability)) {
            return 'Disponibilità non specificata';
        }

        $days = [
            'monday' => 'Lunedì',
            'tuesday' => 'Martedì',
            'wednesday' => 'Mercoledì',
            'thursday' => 'Giovedì',
            'friday' => 'Venerdì',
            'saturday' => 'Sabato',
            'sunday' => 'Domenica'
        ];

        $available = [];
        foreach ($this->availability as $day => $hours) {
            if (isset($days[$day]) && !empty($hours)) {
                $available[] = $days[$day] . ': ' . $hours;
            }
        }

        return !empty($available) ? implode(', ', $available) : 'Disponibilità non specificata';
    }

    // Methods
    public function hasPermission(string $permission): bool
    {
        if (!$this->permissions || !is_array($this->permissions)) {
            return false;
        }

        return in_array($permission, $this->permissions);
    }

    public function canAccessSection(string $section): bool
    {
        $sectionPermissions = [
            'courses' => ['manage_courses', 'view_courses'],
            'students' => ['manage_students', 'view_students'],
            'attendance' => ['mark_attendance', 'view_attendance'],
            'payments' => ['view_payments', 'manage_payments'],
            'reports' => ['view_reports']
        ];

        if (!isset($sectionPermissions[$section])) {
            return false;
        }

        foreach ($sectionPermissions[$section] as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }
}