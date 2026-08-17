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
        if (!empty($result->logEntries)) {
            // LogParserService might expect a raw string, if so, join it
            $content = is_array($result->logEntries) ? json_encode($result->logEntries) : $result->logEntries;
            $this->parser->parseRawContent($project, $content);
        }
    }
}
