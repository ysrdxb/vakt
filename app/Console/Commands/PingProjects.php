<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\UptimeLog;
use App\Models\Incident;
use Illuminate\Console\Command;
use App\Jobs\PingSingleProject;

class PingProjects extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vakt:ping-projects';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pings all active projects to record uptime and response time safely without bursting.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $projects = Project::where('active', true)->get();
        $delayCounter = 0;

        foreach ($projects as $project) {
            if ($project->server_type === 'same_server') {
                $this->handleSameServerProject($project);
                continue;
            }

            // Stagger by 2 seconds to prevent burst firewall bans
            PingSingleProject::dispatch($project->id)->delay(now()->addSeconds($delayCounter * 2));
            $delayCounter++;
        }
    }

    private function handleSameServerProject(Project $project): void
    {
        // Safe local filesystem check instead of HTTP network request
        $basePath = rtrim($project->server_path, '/');
        
        // Ensure path safety before checking
        $real = realpath($basePath);
        $isSafe = $real !== false && (str_starts_with($real, '/home/') || str_starts_with($real, '/Users/') || str_starts_with($real, 'C:\\'));

        $statusCode = null;
        $errorMessage = null;

        if ($isSafe && is_dir($basePath)) {
            $statusCode = 200; // Local directory exists, consider site healthy
        } else {
            $statusCode = 500;
            $errorMessage = 'Base path is inaccessible or violates security rules';
        }

        UptimeLog::create([
            'project_id' => $project->id,
            'status_code' => $statusCode,
            'response_time_ms' => 0, // Instant
            'error_message' => $errorMessage,
        ]);

        if ($statusCode === 500) {
            $this->handleDownProject($project, $statusCode, $errorMessage);
        }
    }

    private function handleDownProject(Project $project, ?int $statusCode, ?string $errorMessage)
    {
        $recentFailures = UptimeLog::where('project_id', $project->id)
            ->where('created_at', '>=', now()->subMinutes(3))
            ->where(function ($query) {
                $query->whereNull('status_code')
                      ->orWhere('status_code', '>=', 500);
            })
            ->count();

        // If it's been down for 3 consecutive minutes, create an incident
        if ($recentFailures >= 3) {
            $existingIncident = Incident::where('project_id', $project->id)
                ->where('title', 'Site Offline Detected')
                ->whereNotIn('status', ['resolved', 'closed'])
                ->first();

            if (!$existingIncident) {
                $incident = Incident::create([
                    'project_id' => $project->id,
                    'title' => 'Site Offline Detected',
                    'description' => "The site {$project->domain} is unreachable or returning 5xx errors. Last error: " . ($statusCode ? "HTTP {$statusCode}" : $errorMessage),
                    'severity' => 'p1',
                    'status' => 'open',
                    'source' => 'uptime_monitor',
                    'detected_at' => now(),
                ]);

                \App\Jobs\AnalyzeIncidentWithAI::dispatch($incident->id);
            }
        }
    }
}
