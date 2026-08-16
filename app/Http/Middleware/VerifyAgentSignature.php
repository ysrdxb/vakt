<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyAgentSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $projectId = $request->header('X-Project-ID');
        $signature = $request->header('X-Agent-Signature');

        if (!$projectId || !$signature) {
            \Log::warning('Agent request missing headers', ['ip' => $request->ip()]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $project = \App\Models\Project::where('domain', $projectId)
            ->orWhere('id', $projectId)
            ->first();

        if (!$project) {
            return response()->json(['error' => 'Unknown project'], 401);
        }

        // Verify IP whitelist
        if ($project->agent_ip_whitelist) {
            $allowedIps = array_map('trim', explode(',', $project->agent_ip_whitelist));
            if (!in_array($request->ip(), $allowedIps)) {
                \Log::warning('Agent IP not whitelisted', [
                    'project' => $project->domain,
                    'ip'      => $request->ip(),
                ]);
                return response()->json(['error' => 'IP not allowed'], 403);
            }
        }

        // Verify HMAC signature
        $payload = $request->getContent();
        $expectedSig = hash_hmac('sha256', $payload, $project->agent_secret);

        if (!hash_equals($expectedSig, $signature)) {
            \Log::warning('Agent signature mismatch', [
                'project' => $project->domain,
                'ip'      => $request->ip(),
            ]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $request->merge(['verified_project' => $project]);

        return $next($request);
    }
}
