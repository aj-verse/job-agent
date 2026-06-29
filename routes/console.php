<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Setting;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Job Automation Scheduler
// Dynamically schedule based on hourly/daily setting, run time, and timezone
Schedule::command('jobs:automation-run')
    ->hourly()
    ->when(function () {
        try {
            $frequency = Setting::get('search_frequency', 'daily');
            if ($frequency === 'hourly') {
                return true;
            }

            $timezone = Setting::get('timezone', 'Asia/Kolkata');
            $dailyTime = Setting::get('daily_run_time', '07:00');

            // Extract the hour from H:i format (e.g. 07)
            $parts = explode(':', $dailyTime);
            $targetHour = $parts[0] ?? '07';

            // Check if the current hour in the user's timezone matches the target hour
            return \Carbon\Carbon::now($timezone)->format('H') === $targetHour;
        } catch (\Exception $e) {
            // Graceful fallback if database or models are not accessible
            return date('H') === '07';
        }
    });
