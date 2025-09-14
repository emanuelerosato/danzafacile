<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendance';

    protected $fillable = [
        'user_id',
        'school_id',
        'attendable_type',
        'attendable_id',
        'date',
        'check_in_time',
        'check_out_time',
        'status',
        'notes',
        'marked_by'
    ];

    protected $casts = [
        'date' => 'date',
        'check_in_time' => 'datetime:H:i',
        'check_out_time' => 'datetime:H:i'
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
        static::creating(function (Attendance $attendance) {
            $user = Auth::user();
            if ($user && !$user->isSuperAdmin() && $user->school_id && !$attendance->school_id) {
                $attendance->school_id = $user->school_id;
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

    public function attendable()
    {
        return $this->morphTo();
    }

    public function markedBy()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    // Scopes
    public function scopePresent(Builder $query): Builder
    {
        return $query->where('status', 'present');
    }

    public function scopeAbsent(Builder $query): Builder
    {
        return $query->where('status', 'absent');
    }

    public function scopeLate(Builder $query): Builder
    {
        return $query->where('status', 'late');
    }

    public function scopeExcused(Builder $query): Builder
    {
        return $query->where('status', 'excused');
    }

    public function scopeForDate(Builder $query, $date): Builder
    {
        return $query->whereDate('date', $date);
    }

    public function scopeForDateRange(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeForCourse(Builder $query, int $courseId): Builder
    {
        return $query->where('attendable_type', Course::class)
                    ->where('attendable_id', $courseId);
    }

    public function scopeForEvent(Builder $query, int $eventId): Builder
    {
        return $query->where('attendable_type', Event::class)
                    ->where('attendable_id', $eventId);
    }

    // Accessors
    public function getIsPresentAttribute(): bool
    {
        return $this->status === 'present';
    }

    public function getIsAbsentAttribute(): bool
    {
        return $this->status === 'absent';
    }

    public function getIsLateAttribute(): bool
    {
        return $this->status === 'late';
    }

    public function getIsExcusedAttribute(): bool
    {
        return $this->status === 'excused';
    }

    public function getDurationMinutesAttribute(): ?int
    {
        if (!$this->check_in_time || !$this->check_out_time) {
            return null;
        }

        return $this->check_in_time->diffInMinutes($this->check_out_time);
    }
}