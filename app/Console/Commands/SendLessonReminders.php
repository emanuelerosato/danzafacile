<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lesson;
use App\Models\NotificationPreference;
use App\Services\FirebasePushService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendLessonReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:send-lesson-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send push notifications for upcoming lessons based on user preferences';

    protected $firebasePushService;

    public function __construct(FirebasePushService $firebasePushService)
    {
        parent::__construct();
        $this->firebasePushService = $firebasePushService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔔 Starting lesson reminder notifications...');

        // Get all enabled notification preferences
        $preferences = NotificationPreference::where('enabled', true)
            ->where('lesson_reminders', true)
            ->with('user')
            ->get();

        if ($preferences->isEmpty()) {
            $this->info('No users with lesson reminders enabled');
            return Command::SUCCESS;
        }

        $this->info("Found {$preferences->count()} users with reminders enabled");

        $totalSent = 0;
        $totalSkipped = 0;

        foreach ($preferences as $preference) {
            $user = $preference->user;

            if (!$user) {
                $this->warn("User not found for preference ID {$preference->id}");
                continue;
            }

            // Get user's enrolled courses
            $courseIds = $user->enrollments()->pluck('course_id');

            if ($courseIds->isEmpty()) {
                $this->line("  User {$user->name} has no course enrollments, skipping");
                $totalSkipped++;
                continue;
            }

            // Find lessons that match the reminder time window
            $reminderMinutes = $preference->reminder_minutes_before;
            $targetTime = now()->addMinutes($reminderMinutes);

            // Get lessons within ±7 minutes of target time
            $lessons = Lesson::whereIn('course_id', $courseIds)
                ->where('status', 'scheduled')
                ->where('lesson_date', $targetTime->toDateString())
                ->with(['course', 'instructor', 'room'])
                ->get()
                ->filter(function ($lesson) use ($targetTime, $reminderMinutes) {
                    // Check if lesson starts within ±7 minutes of target reminder time
                    $lessonStart = $lesson->start_datetime;
                    $timeDiff = $targetTime->diffInMinutes($lessonStart, false);

                    // timeDiff should be close to 0 (lesson starts at target time ± 7 min tolerance)
                    return abs($timeDiff) <= 7;
                });

            if ($lessons->isEmpty()) {
                continue;
            }

            // Send notification for each lesson
            foreach ($lessons as $lesson) {
                $title = $lesson->getNotificationTitle();
                $body = $lesson->getNotificationBody($reminderMinutes);
                $data = $lesson->getNotificationPayload();

                $result = $this->firebasePushService->sendToUser(
                    $user->id,
                    $title,
                    $body,
                    $data,
                    $lesson->id
                );

                if ($result['success']) {
                    $this->info("  ✅ Sent to {$user->name} for lesson: {$lesson->course->name} at {$lesson->start_time}");
                    $totalSent++;
                } else {
                    $this->warn("  ⚠️  Failed to send to {$user->name}");
                }
            }
        }

        $this->newLine();
        $this->info("✅ Lesson reminders completed!");
        $this->info("   Sent: {$totalSent}");
        $this->info("   Skipped: {$totalSkipped}");

        Log::info("Lesson reminders cron completed: {$totalSent} sent, {$totalSkipped} skipped");

        return Command::SUCCESS;
    }
}
