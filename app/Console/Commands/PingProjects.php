<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\UptimeLog;
use App\Models\Incident;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

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
    protected $description = 'Pings all active projects to record uptime and response time.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $projects = Project::all();

        foreach ($projects as $project) {
            $url = $project->domain;
            if (!str_starts_with($url, 'http')) {
                $url = 'https://' . $url;
            }

            $startTime = microtime(true);
            $statusCode = null;
            $errorMessage = null;

            try {
                $response = Http::timeout(10)->get($url);
                $statusCode = $response->status();
            } catch (\Exception $e) {
                $errorMessage = $e->getMessage();
            }

            $responseTimeMs = (int) ((microtime(true) - $startTime) * 1000);

            UptimeLog::create([
                'project_id' => $project->id,
                'status_code' => $statusCode,
                'response_time_ms' => $responseTimeMs,
                'error_message' => $errorMessage,
            ]);

            // If it failed, check if we need to create an incident
            if (!$statusCode || $statusCode >= 500) {
                $this->handleDownProject($project, $statusCode, $errorMessage);
            }
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
