<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\ScanProjectLogs;
use App\Jobs\ScanFileIntegrity;
use App\Jobs\GenerateDailyLogs;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ─── Vakt SOC Scheduler ─────────────────────────────

// Every minute — log scanning (same-server projects)
Schedule::job(new ScanProjectLogs)->everyMinute();

// Every 5 minutes — file integrity check
Schedule::job(new ScanFileIntegrity)->everyFiveMinutes();

// Daily at 08:00 — generate daily monitoring logs
Schedule::job(new GenerateDailyLogs)->dailyAt('08:00');

// Daily at 08:30 — send daily digest email
Schedule::command('vakt:send-digest')->dailyAt('08:30');

// Weekly Monday 09:00 — send weekly summary
Schedule::command('vakt:send-weekly')->weeklyOn(1, '09:00');

// Purge agent reports older than 30 days
Schedule::command('model:prune', ['--model' => [\App\Models\AgentReport::class]])->daily();
