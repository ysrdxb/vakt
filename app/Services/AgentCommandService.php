<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AgentCommandService
{
    /**
     * Dispatch a command to the remote agent via POST request.
     * Returns an array with 'status' (success/error) and 'message'.
     */
    public function dispatchCommand(Project $project, string $command, array $payload = []): array
    {
        if ($project->server_type !== 'external_agent' || empty($project->agent_url)) {
            return [
                'status' => 'error',
                'message' => 'Commands can only be dispatched to External Agent projects with a configured Agent URL.'
            ];
        }

        $payload['command'] = $command;

        try {
            $response = Http::withHeaders([
                'X-SOC-Key'    => $project->agent_secret,
                'X-Project-ID' => (string) $project->id,
                'Accept'       => 'application/json',
                'User-Agent'   => 'Vakt-SOC-Commander/1.0',
            ])
            ->timeout(10)
            ->post($project->agent_url, $payload);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'status' => $data['status'] ?? 'success',
                    'message' => $data['message'] ?? 'Command executed successfully.',
                ];
            }

            return [
                'status' => 'error',
                'message' => "Agent returned HTTP {$response->status()}: " . ($response->json('error') ?? 'Unknown error'),
            ];

        } catch (\Exception $e) {
            Log::error("Failed to dispatch command {$command} to project {$project->id}: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Connection failed: ' . $e->getMessage(),
            ];
        }
    }

    public function blockIp(Project $project, string $ip): array
    {
        return $this->dispatchCommand($project, 'block_ip', ['ip' => $ip]);
    }

    public function fixPermissions(Project $project): array
    {
        return $this->dispatchCommand($project, 'fix_permissions');
    }

    public function clearCache(Project $project): array
    {
        return $this->dispatchCommand($project, 'clear_cache');
    }
}
