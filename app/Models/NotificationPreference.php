<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'enabled',
        'lesson_reminders',
        'reminder_minutes_before',
        'event_reminders',
        'payment_reminders',
        'system_notifications',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'lesson_reminders' => 'boolean',
        'reminder_minutes_before' => 'integer',
        'event_reminders' => 'boolean',
        'payment_reminders' => 'boolean',
        'system_notifications' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessors
     */
    public function getShouldSendLessonRemindersAttribute(): bool
    {
        return $this->enabled && $this->lesson_reminders;
    }

    /**
     * Helper: Get available reminder times
     */
    public static function availableReminderTimes(): array
    {
        return [15, 30, 60, 120, 1440];
    }

    /**
     * Helper: Get default preferences
     */
    public static function defaults(): array
    {
        return [
            'enabled' => true,
            'lesson_reminders' => true,
            'reminder_minutes_before' => 60,
            'event_reminders' => true,
            'payment_reminders' => true,
            'system_notifications' => true,
        ];
    }
}
