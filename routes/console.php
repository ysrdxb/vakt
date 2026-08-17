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

use App\Models\Project;
use App\Jobs\CollectProjectData;

// Collect data — staggered by project ID to spread load
// Never dispatch all projects at once
Schedule::call(function () {
    Project::where('active', true)
        ->where('monitoring_interval_minutes', 1)
        ->each(function (Project $project) {
            // Stagger by project ID — 3 second gap between each
            CollectProjectData::dispatch($project->id)
                ->delay(now()->addSeconds($project->id % 20 * 3));
        });
})->everyMinute()->name('collect-1min')->withoutOverlapping();

Schedule::call(function () {
    Project::where('active', true)
        ->where('monitoring_interval_minutes', 5)
        ->each(function (Project $project) {
            CollectProjectData::dispatch($project->id)
                ->delay(now()->addSeconds($project->id % 20 * 5));
        });
})->everyFiveMinutes()->name('collect-5min')->withoutOverlapping();

// Daily at 08:00 — generate daily monitoring logs
Schedule::job(new GenerateDailyLogs)->dailyAt('08:00');

// Daily at 08:30 — send daily digest email
Schedule::command('vakt:send-digest')->dailyAt('08:30');

// Weekly Monday 09:00 — send weekly summary
Schedule::command('vakt:send-weekly')->weeklyOn(1, '09:00');

// Purge agent reports older than 30 days
Schedule::command('model:prune', ['--model' => [\App\Models\AgentReport::class]])->daily();
