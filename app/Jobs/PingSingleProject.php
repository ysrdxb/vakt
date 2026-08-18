<?php

namespace App\Jobs;

use App\Models\Project;
use App\Models\UptimeLog;
use App\Models\Incident;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class PingSingleProject implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;        // NEVER retry failed ping jobs automatically
    public int $timeout = 30;     // hard 30s limit per job

    public function __construct(private int $projectId) {}

    public function handle(): void
    {
        $project = Project::find($this->projectId);

        if (!$project || !$project->active || $project->server_type === 'same_server') {
            return;
        }

        // Mutex — prevents two jobs for same project running simultaneously
        $lockKey = "pinging_project_{$this->projectId}";
        if (Cache::has($lockKey)) return;
        Cache::put($lockKey, true, 30);

        try {
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
        } finally {
            Cache::forget($lockKey);
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
