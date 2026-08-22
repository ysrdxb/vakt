<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentReport;
use App\Services\LogParserService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AgentController extends Controller
{
    public function __construct(private LogParserService $parser)
    {
    }

    public function receive(Request $request): JsonResponse
    {
        $project = $request->get('verified_project');
        $data    = $request->json()->all();

        // Store raw report (kept 30 days)
        AgentReport::create([
            'project_id'     => $project->id,
            'payload'        => $data,
            'agent_ip'       => $request->ip(),
            'signature_valid' => true,
            'received_at'    => now(),
        ]);

        $errorsFound = 0;
        $warningsFound = 0;
        $criticalPatterns = [];
        $linesScanned = 0;

        // Parse log tail for patterns
        if (!empty($data['log_tail'])) {
            $linesScanned = substr_count($data['log_tail'], "\n") + 1;
            // parseRawContent should return the array instead of void, but let's just use the reflection or change parseRawContent.
            // Wait, LogParserService->parseRawContent returns void currently. We will change LogParserService.
            [$errorsFound, $warningsFound, $criticalPatterns] = $this->parser->parseRawContent($project, $data['log_tail']);
        }

        $status = 'ok';
        if (!empty($criticalPatterns)) $status = 'critical';
        elseif ($errorsFound > 0 || $warningsFound > 5) $status = 'warning';

        // Update project last_checked_at and status
        $project->update([
            'status' => match($status) {
                'critical' => 'critical',
                'warning'  => 'warning',
                default    => 'healthy',
            },
            'last_checked_at' => now()
        ]);

        \App\Models\MonitoringCheck::create([
            'project_id'              => $project->id,
            'checked_at'              => now(),
            'status'                  => $status,
            'log_lines_scanned'       => $linesScanned,
            'errors_found'            => $errorsFound,
            'warnings_found'          => $warningsFound,
            'critical_patterns_found' => $criticalPatterns ?: null,
            'duration_ms'             => 0, // Agent push
        ]);

        return response()->json(['ok' => true]);
    }
}
