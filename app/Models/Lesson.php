<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'instructor_id',
        'room_id',
        'lesson_date',
        'start_time',
        'end_time',
        'status',
        'notes',
    ];

    protected $casts = [
        'lesson_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    protected $appends = [
        'start_datetime',
        'end_datetime',
        'is_upcoming',
        'is_today',
    ];

    /**
     * Relationships
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(SchoolRoom::class, 'room_id');
    }

    /**
     * Accessors
     */
    public function getStartDatetimeAttribute(): Carbon
    {
        return Carbon::parse($this->lesson_date->format('Y-m-d') . ' ' . $this->start_time->format('H:i:s'));
    }

    public function getEndDatetimeAttribute(): Carbon
    {
        return Carbon::parse($this->lesson_date->format('Y-m-d') . ' ' . $this->end_time->format('H:i:s'));
    }

    public function getIsUpcomingAttribute(): bool
    {
        return $this->start_datetime->isFuture() && $this->status === 'scheduled';
    }

    public function getIsTodayAttribute(): bool
    {
        return $this->lesson_date->isToday();
    }

    /**
     * Scopes
     */
    public function scopeUpcoming($query, int $days = 7)
    {
        return $query->where('lesson_date', '>=', now()->toDateString())
            ->where('lesson_date', '<=', now()->addDays($days)->toDateString())
            ->where('status', 'scheduled')
            ->orderBy('lesson_date')
            ->orderBy('start_time');
    }

    public function scopeForCourse($query, int $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeByDate($query, Carbon $date)
    {
        return $query->whereDate('lesson_date', $date->toDateString());
    }

    /**
     * Helper: Check if lesson starts within given minutes
     */
    public function startsWithinMinutes(int $minutes): bool
    {
        $targetTime = now()->addMinutes($minutes);
        $lessonStart = $this->start_datetime;

        return $lessonStart->between(now(), $targetTime);
    }

    /**
     * Helper: Get notification title
     */
    public function getNotificationTitle(): string
    {
        return "Lezione in arrivo! 🩰";
    }

    /**
     * Helper: Get notification body
     */
    public function getNotificationBody(int $minutesBefore): string
    {
        $timeLabel = match($minutesBefore) {
            15 => '15 minuti',
            30 => '30 minuti',
            60 => '1 ora',
            120 => '2 ore',
            1440 => '1 giorno',
            default => "{$minutesBefore} minuti",
        };

        return "La tua lezione di {$this->course->name} inizia tra {$timeLabel} ({$this->start_time})";
    }

    /**
     * Helper: Get notification payload data
     */
    public function getNotificationPayload(): array
    {
        return [
            'type' => 'lesson_reminder',
            'lesson_id' => (string) $this->id,
            'course_id' => (string) $this->course_id,
            'lesson_date' => $this->lesson_date->toDateString(),
            'start_time' => $this->start_time,
        ];
    }
}
