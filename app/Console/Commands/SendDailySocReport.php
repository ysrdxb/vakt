<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Project;
use App\Services\NotificationService;

class SendDailySocReport extends Command
{
    protected $signature = 'vakt:daily-report';
    protected $description = 'Send daily SOC security briefing to project webhooks';

    public function handle(NotificationService $notifications)
    {
        $projects = Project::where('active', true)
            ->where(function($query) {
                $query->whereNotNull('slack_webhook_url')
                      ->orWhereNotNull('discord_webhook_url');
            })
            ->get();

        $this->info("Found {$projects->count()} projects with webhooks configured.");

        foreach ($projects as $project) {
            $startDate = now()->subDays(1);
            $endDate = now();

            $uptimeLogs = $project->uptimeLogs()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            $totalPings = $uptimeLogs->count();
            $successfulPings = $uptimeLogs->where('status_code', 200)->count();
            $uptimePercentage = $totalPings > 0 ? round(($successfulPings / $totalPings) * 100, 2) : 100;

            // Check if there are any incidents open
            $openIncidents = $project->incidents()
                ->whereNotIn('status', ['resolved', 'closed'])
                ->count();

            // Check if there was a backup failure incident in the last 24h
            $backupFailed = $project->incidents()
                ->where('title', 'like', '%backup missing%')
                ->where('created_at', '>=', $startDate)
                ->exists();

            $stats = [
                'uptime_percentage' => $uptimePercentage,
                'open_incidents'    => $openIncidents,
                'backup_healthy'    => !$backupFailed,
            ];

            $notifications->notifyDailyReport($project, $stats);
            $this->info("Sent daily report for {$project->domain}");
        }

        $this->info('Daily SOC reports sent successfully.');
    }
}
