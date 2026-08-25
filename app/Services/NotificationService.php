<?php

namespace App\Services;

use App\Models\Incident;
use App\Models\Project;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function notifyIncidentCreated(Incident $incident): void
    {
        $project = $incident->project;

        if ($project->alert_email) {
            try {
                Mail::to($project->alert_email)->queue(new \App\Mail\IncidentAlert($incident));
            } catch (\Exception $e) {
                Log::error("Failed to send Incident email alert for incident {$incident->id}: " . $e->getMessage());
            }
        }
    }

    public function notifyDailyReport(Project $project, array $stats): void
    {
        if ($project->alert_email) {
            $text = "Daily Security Briefing for {$project->domain}\n\n";
            $text .= "Uptime: {$stats['uptime_percentage']}%\n";
            $text .= "Open Incidents: {$stats['open_incidents']}\n";
            $backupStatus = $stats['backup_healthy'] ? 'Verified' : 'Missing/Failed';
            $text .= "Backup Status: {$backupStatus}\n\n";
            $text .= "All systematic checks completed for the last 24 hours.\n";
            $text .= "Vakt SOC Platform";

            try {
                Mail::raw($text, function ($message) use ($project) {
                    $message->to($project->alert_email)
                            ->subject("Daily SOC Report: {$project->domain}");
                });
            } catch (\Exception $e) {
                Log::error("Failed to send Daily Report email: " . $e->getMessage());
            }
        }
    }
}
