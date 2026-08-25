<?php

namespace App\Services\Collectors;

use App\Models\Project;
use App\DTOs\CollectionResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ExternalAgentCollector
{
    public function __construct(private Project $project) {}

    public function collect(): CollectionResult
    {
        // RATE LIMIT ENFORCEMENT — never call more than once per interval
        // Even if scheduler fires, cache prevents actual HTTP call
        $cacheKey = "agent_last_call_{$this->project->id}";
        $minInterval = max(($this->project->monitoring_interval_minutes * 60) - 30, 270); // 30s buffer, minimum 270s

        if (Cache::has($cacheKey)) {
            return CollectionResult::skipped(
                $this->project->id,
                'Rate limited — called too recently'
            );
        }

        // Mark as called BEFORE making the request
        Cache::put($cacheKey, now(), $minInterval);

        if (!$this->project->agent_url) {
            return CollectionResult::failed($this->project->id, 'No agent URL configured');
        }

        try {
            $response = Http::withHeaders([
                    'X-SOC-Key'    => $this->project->agent_secret,
                    'X-Project-ID' => (string) $this->project->id,
                    'Accept'       => 'application/json',
                    'User-Agent'   => 'Vakt-SOC/1.0',
                ])
                ->timeout(15)           // hard 15s timeout — never hang
                ->retry(1, 2000)        // ONE retry only, 2s wait — not a burst
                ->get($this->project->agent_url);

            if ($response->status() === 429) {
                // Agent is rate limiting us — back off and respect it
                Log::warning("Agent rate limited for project {$this->project->id}");
                Cache::put($cacheKey, now(), 600); // force 10 min backoff
                return CollectionResult::skipped($this->project->id, 'Agent rate limited');
            }

            if ($response->status() === 403) {
                // Key mismatch — do NOT retry, this is a config error
                return CollectionResult::failed($this->project->id, 'Agent authentication failed — check secret key');
            }

            if (!$response->successful()) {
                $errorData = $response->json();
                $agentMessage = $errorData['error'] ?? "Agent returned HTTP {$response->status()}";
                return CollectionResult::failed($this->project->id, $agentMessage);
            }

            $data = $response->json();

            return new CollectionResult(
                projectId:   $this->project->id,
                collectedAt: now(),
                logEntries:  $data['log_tail'] ?? [],
                fileChanges: $data['file_changes'] ?? [],
                envStatus:   $data['env_status'] ?? [],
                phpErrors:   $data['php_errors'] ?? [],
                uploadScan:  [],
                systemMetrics: $data['system_metrics'] ?? [],
                backupStatus:  $data['backup_status'] ?? [],
                secretsExposure: $data['secrets_exposure'] ?? [],
                source:      'agent',
            );

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return CollectionResult::failed($this->project->id, 'Agent unreachable: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error("Agent collection failed for project {$this->project->id}: " . $e->getMessage());
            return CollectionResult::failed($this->project->id, $e->getMessage());
        }
    }
}
