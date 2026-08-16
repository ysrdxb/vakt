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

        // Parse log tail for patterns
        if (!empty($data['log_tail'])) {
            $this->parser->parseRawContent($project, $data['log_tail']);
        }

        // Update project last_checked_at
        $project->update(['last_checked_at' => now()]);

        return response()->json(['ok' => true]);
    }
}
