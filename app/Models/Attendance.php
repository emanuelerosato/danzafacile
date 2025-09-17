<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'date' => 'date'
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
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function attendable()
    {
        return $this->morphTo();
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'attendable_id')->where('attendable_type', Course::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'attendable_id')->where('attendable_type', Event::class);
    }

    public function markedByUser(): BelongsTo
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
        return $query->where('attendable_type', Course::class)->where('attendable_id', $courseId);
    }

    public function scopeForEvent(Builder $query, int $eventId): Builder
    {
        return $query->where('attendable_type', Event::class)->where('attendable_id', $eventId);
    }

    public function scopeForCourses(Builder $query): Builder
    {
        return $query->where('attendable_type', Course::class);
    }

    public function scopeForEvents(Builder $query): Builder
    {
        return $query->where('attendable_type', Event::class);
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

        // Convert time strings to Carbon instances for the same day
        $checkIn = \Carbon\Carbon::createFromFormat('H:i:s', $this->check_in_time);
        $checkOut = \Carbon\Carbon::createFromFormat('H:i:s', $this->check_out_time);

        return $checkIn->diffInMinutes($checkOut);
    }

    public function getAttendanceTypeAttribute(): string
    {
        if ($this->attendable_type === Course::class) {
            return 'course';
        } elseif ($this->attendable_type === Event::class) {
            return 'event';
        }
        return 'unknown';
    }

    public function getAttendanceSubjectAttribute(): ?Model
    {
        return $this->attendable;
    }

    public function getAttendanceSubjectNameAttribute(): string
    {
        $subject = $this->attendance_subject;
        return $subject ? $subject->name : 'N/A';
    }

    // Helper methods
    public function isForCourse(): bool
    {
        return $this->attendable_type === Course::class;
    }

    public function isForEvent(): bool
    {
        return $this->attendable_type === Event::class;
    }

    public function markPresent(?string $checkInTime = null): void
    {
        $this->update([
            'status' => 'present',
            'check_in_time' => $checkInTime ?? now()->format('H:i:s'),
            'marked_by' => auth()->id(),
        ]);
    }

    public function markAbsent(?string $notes = null): void
    {
        $this->update([
            'status' => 'absent',
            'check_in_time' => null,
            'check_out_time' => null,
            'notes' => $notes,
            'marked_by' => auth()->id(),
        ]);
    }

    public function markLate(?string $checkInTime = null): void
    {
        $this->update([
            'status' => 'late',
            'check_in_time' => $checkInTime ?? now()->format('H:i:s'),
            'marked_by' => auth()->id(),
        ]);
    }
}