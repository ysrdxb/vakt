<?php

namespace App\Services;

use App\Models\Project;
use App\Models\AgentReport;
use App\DTOs\CollectionResult;
use App\Services\LogParserService;

class AnalyzerService
{
    public function __construct(private LogParserService $parser) {}

    public function process(CollectionResult $result): void
    {
        $project = Project::find($result->projectId);
        if (!$project) return;

        // Store raw report (kept 30 days)
        AgentReport::create([
            'project_id'      => $project->id,
            'payload'         => [
                'log_tail'     => $result->logEntries,
                'file_changes' => $result->fileChanges,
                'env_status'   => $result->envStatus,
                'php_errors'   => $result->phpErrors,
                'upload_scan'  => $result->uploadScan,
                'source'       => $result->source,
            ],
            'agent_ip'        => '127.0.0.1', // local or pulled
            'signature_valid' => true,
            'received_at'     => $result->collectedAt,
        ]);

        // Parse log tail for patterns (simulate passing raw string or array)
        // Parse log tail for patterns
        if (!empty($result->logEntries)) {
            // Join array with newlines so the parser can process it line-by-line
            $content = is_array($result->logEntries) ? implode("\\n", $result->logEntries) : $result->logEntries;
            $this->parser->parseRawContent($project, $content);
        }

        // Process Phase 5 Proactive Scans
        $incidentCreator = app(\App\Services\IncidentAutoCreatorService::class);

        if (isset($result->backupStatus['healthy']) && !$result->backupStatus['healthy']) {
            $incidentCreator->createIfNotExists(
                $project,
                "Database backup missing or failed",
                "p1",
                "No database backups were detected in the last 24 hours. Verify your automated backup schedule immediately."
            );
        }

        if (isset($result->secretsExposure['exposed']) && $result->secretsExposure['exposed']) {
            $keys = implode(', ', $result->secretsExposure['matches'] ?? []);
            $incidentCreator->createIfNotExists(
                $project,
                "Exposed API Key detected on server",
                "p1",
                "The secrets scanner detected exposed keys: {$keys}. Check your log files and .env exposures."
            );
        }
    }
}
