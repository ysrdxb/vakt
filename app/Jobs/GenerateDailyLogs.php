<?php

namespace App\Jobs;

use App\Models\Project;
use App\Models\DailyLog;
use App\Models\MonitoringCheck;
use App\Models\Incident;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateDailyLogs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $projects = Project::where('active', true)->get();
        $today = now()->startOfDay();

        foreach ($projects as $project) {
            // Skip if already generated today
            if (DailyLog::where('project_id', $project->id)->whereDate('checked_at', today())->exists()) {
                continue;
            }

            // Gather today's data
            $checks = MonitoringCheck::where('project_id', $project->id)
                ->whereDate('checked_at', today())
                ->get();

            $incidents = Incident::where('project_id', $project->id)
                ->whereDate('detected_at', today())
                ->get();

            $status = 'ok';
            if ($checks->where('status', 'critical')->count() > 0 || $incidents->where('severity', 'p1')->count() > 0) {
                $status = 'critical';
            } elseif ($checks->where('status', 'warning')->count() > 0 || $incidents->count() > 0) {
                $status = 'warning';
            }

            $summary = sprintf(
                "%d monitoring checks run. %d errors found. %d incidents detected. Status: %s.",
                $checks->count(),
                $checks->sum('errors_found'),
                $incidents->count(),
                strtoupper($status)
            );

            DailyLog::create([
                'project_id'    => $project->id,
                'checked_at'    => now(),
                'status'        => $status,
                'summary'       => $summary,
                'findings'      => [
                    'checks_run'     => $checks->count(),
                    'errors_found'   => $checks->sum('errors_found'),
                    'warnings_found' => $checks->sum('warnings_found'),
                    'incidents'      => $incidents->count(),
                    'p1_incidents'   => $incidents->where('severity', 'p1')->count(),
                ],
                'auto_generated' => true,
            ]);
        }
    }
}
