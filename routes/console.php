<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule email funnel processing every hour
Schedule::command('emails:process-scheduled')->hourly();

// Schedule lesson reminder notifications every 15 minutes
Schedule::command('notifications:send-lesson-reminders')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->runInBackground();

// Schedule cleanup guest scaduti (GDPR) - ogni giorno alle 2:00
Schedule::command('guests:cleanup --days=180')
    ->dailyAt('02:00')
    ->timezone('Europe/Rome');

// Schedule processing eventi email (reminder + thank you) - ogni ora
Schedule::command('events:process-email-scheduler')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();
