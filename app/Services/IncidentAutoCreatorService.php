<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Incident;
use App\Models\IncidentTimeline;
use App\Models\LogEntry;

class IncidentAutoCreatorService
{
    public function checkAndCreate(Project $project, array $criticalPatterns, LogEntry $logEntry = null): ?Incident
    {
        foreach ($criticalPatterns as $pattern => $count) {
            $incident = match($pattern) {
                'eval_detected', 'sql_injection' => $this->createIfNotExists(
                    $project, "Malicious code pattern detected ({$pattern})", 'p1',
                    "Log parser detected malicious pattern '{$pattern}' {$count} time(s).", $logEntry
                ),
                'api_key_exposed', 'env_exposure' => $this->createIfNotExists(
                    $project, "API key/credential exposed in logs", 'p1',
                    "Sensitive credential detected in application logs.", $logEntry
                ),
                'brute_force' => $this->createIfNotExists(
                    $project, "Brute force attack detected", 'p2',
                    "Multiple failed authentication attempts detected.", $logEntry
                ),
                'xss_attempt' => $this->createIfNotExists(
                    $project, "XSS attack attempt detected", 'p2',
                    "Cross-site scripting payload found in logs.", $logEntry
                ),
                default => null,
            };

            if ($incident) return $incident;
        }

        return null;
    }

    public function createIfNotExists(
        Project $project,
        string $title,
        string $severity,
        string $description = '',
        LogEntry $logEntry = null
    ): ?Incident {
        $existing = Incident::where('project_id', $project->id)
            ->where('title', 'like', '%' . substr($title, 0, 30) . '%')
            ->whereNotIn('status', ['resolved', 'closed'])
            ->where('created_at', '>=', now()->subHours(24))
            ->first();

        if ($existing) return null;

        $incident = Incident::create([
            'project_id'   => $project->id,
            'title'        => $title,
            'description'  => $description,
            'severity'     => $severity,
            'status'       => 'open',
            'source'       => 'auto_detected',
            'detected_at'  => now(),
            'related_log_entry_ids' => $logEntry ? [$logEntry->id] : null,
        ]);

        IncidentTimeline::create([
            'incident_id'  => $incident->id,
            'action'       => 'Incident auto-created',
            'description'  => "Automatically detected by log scanner. {$description}",
            'performed_by' => 'Vakt SOC System',
            'performed_at' => now(),
        ]);

        // Fire alert
        try {
            \Mail::to($project->alert_email ?? config('mail.from.address'))
                ->send(new \App\Mail\IncidentAlert($incident));
        } catch (\Exception $e) {
            \Log::warning("Could not send incident alert email: " . $e->getMessage());
        }

        // Trigger AI Analysis
        \App\Jobs\AnalyzeIncidentWithAI::dispatch($incident->id);

        return $incident;
    }
}
