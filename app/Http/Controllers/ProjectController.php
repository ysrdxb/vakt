<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ProjectController extends Controller
{
    public function create()
    {
        $project = new Project();
        $agent_secret = bin2hex(random_bytes(32));
        $isEdit = false;
        
        return view('projects.form', compact('project', 'agent_secret', 'isEdit'));
    }

    public function edit(Project $project)
    {
        $agent_secret = $project->agent_secret;
        $isEdit = true;
        
        return view('projects.form', compact('project', 'agent_secret', 'isEdit'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateProject($request);
        $validated['agent_secret'] = $request->input('agent_secret', bin2hex(random_bytes(32)));
        
        $project = Project::create($validated);
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'redirect_url' => route('projects.show', $project),
                'message' => 'Project created successfully!'
            ]);
        }

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project created successfully!');
    }

    public function update(Request $request, Project $project)
    {
        $validated = $this->validateProject($request, $project->id);
        
        if ($request->filled('agent_secret') && strlen($request->input('agent_secret')) === 64) {
            $validated['agent_secret'] = $request->input('agent_secret');
        }
        
        if ($request->filled('ftp_password')) {
            $validated['ftp_password'] = $request->input('ftp_password');
        }
        
        $project->update($validated);
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'redirect_url' => route('projects.show', $project),
                'message' => 'Project updated successfully!'
            ]);
        }

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project updated successfully!');
    }

    private function validateProject(Request $request, ?int $ignoreId = null): array
    {
        // Clean domain
        $domain = trim($request->input('domain', ''));
        $domain = preg_replace('#^https?://#', '', $domain);
        $domain = rtrim($domain, '/');
        $request->merge(['domain' => $domain]);

        return $request->validate([
            'name' => 'required|string|min:2|max:255',
            'domain' => ['required', 'regex:/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}(\/.*)?$/i', Rule::unique('projects', 'domain')->ignore($ignoreId)],
            'description' => 'nullable|string',
            'server_type' => 'required|in:same_server,external_agent,ftp',
            'stack' => 'required|string',
            'php_version' => 'required|string',
            'laravel_version' => 'nullable|string',
            'monitoring_interval_minutes' => 'required|integer|in:1,5,15,30,60',
            'alert_email' => 'nullable|email',
            'slack_webhook_url' => 'nullable|url',
            'discord_webhook_url' => 'nullable|url',
            'server_path' => 'required_if:server_type,same_server',
            'log_path' => 'nullable|string',
            'php_error_log_path' => 'nullable|string',
            'agent_url' => 'required_if:server_type,external_agent',
            'agent_ip_whitelist' => 'nullable|string',
            'ftp_host' => 'required_if:server_type,ftp',
            'ftp_user' => 'required_if:server_type,ftp',
            'active' => 'boolean',
            'modules' => 'nullable|array',
            'incident_rules' => 'nullable|array',
        ], [
            'domain.regex' => 'Please enter a valid domain (e.g. verk.kunnatta.is or domain.com/folder) without http:// or spaces.',
        ]);
    }

    public function testConnection(Request $request)
    {
        $server_type = $request->input('server_type', 'same_server');
        $server_path = $request->input('server_path');
        $log_path = $request->input('log_path', 'storage/logs/laravel.log');
        $agent_url = $request->input('agent_url');
        $agent_secret = $request->input('agent_secret');

        $results = [];

        if ($server_type === 'same_server') {
            $path = rtrim($server_path, '/');
            $results[] = [
                'icon' => is_dir($path) ? '✅' : '❌',
                'name' => 'Base path accessible',
                'value' => $path ?: 'Missing path',
                'pass' => is_dir($path),
                'fix' => 'Check cPanel Addon Domains for the correct document root path'
            ];

            $logFull = $path . '/' . ltrim($log_path, '/');
            $exists = file_exists($logFull);
            $results[] = [
                'icon' => $exists ? '✅' : '❌',
                'name' => 'Log file found',
                'value' => $logFull,
                'pass' => $exists,
                'fix' => 'Ensure storage/logs/laravel.log exists'
            ];

            if ($exists) {
                $readable = is_readable($logFull);
                $results[] = [
                    'icon' => $readable ? '✅' : '❌',
                    'name' => 'Log file readable',
                    'value' => 'Permissions: ' . substr(sprintf('%o', fileperms($logFull)), -4),
                    'pass' => $readable,
                    'fix' => 'Check file permissions'
                ];
            }
        } elseif ($server_type === 'external_agent') {
            if (!$agent_url) {
                $results[] = ['icon' => '❌', 'name' => 'Agent URL', 'value' => 'Missing URL', 'pass' => false, 'fix' => 'Provide agent URL'];
            } else {
                try {
                    $response = Http::withHeaders(['X-SOC-Key' => $agent_secret])->timeout(10)->get($agent_url);
                    $results[] = ['icon' => $response->successful() ? '✅' : '❌', 'name' => 'Agent reachable', 'value' => "HTTP {$response->status()}", 'pass' => $response->successful(), 'fix' => 'Verify agent URL'];
                } catch (\Exception $e) {
                    $results[] = ['icon' => '❌', 'name' => 'Agent reachable', 'value' => $e->getMessage(), 'pass' => false, 'fix' => 'Check URL connectivity'];
                }
            }
        } else {
            $results[] = ['icon' => '✅', 'name' => 'FTP Settings', 'value' => 'Ready', 'pass' => true, 'fix' => ''];
        }

        $allPass = !collect($results)->contains('pass', false);

        return response()->json([
            'status' => $allPass ? 'ready' : 'failed',
            'results' => $results
        ]);
    }

    public function autoDetectLogPath(Request $request)
    {
        $base = rtrim($request->input('server_path', ''), '/');
        $domain = $request->input('domain', '');

        if (empty($base) || empty($domain)) {
            return response()->json(['success' => false, 'message' => 'Please enter Domain and Server Path first.']);
        }

        $levels = ['', '/..', '/../..', '/../../..'];
        $domainParts = explode('/', $domain);
        $domainFolder = $domainParts[0];

        $possibleFiles = [
            "/logs/{$domainFolder}/error.log",
            "/logs/{$domainFolder}/access.log",
            "/logs/{$domainFolder}.log",
            "/{$domainFolder}.log",
            "/error.log"
        ];

        foreach ($levels as $level) {
            $checkDir = $base . $level;
            $realDir = realpath($checkDir);
            
            if (!$realDir) continue;

            foreach ($possibleFiles as $file) {
                if (file_exists($realDir . $file) && is_readable($realDir . $file)) {
                    $relativePath = ltrim($level . $file, '/');
                    return response()->json(['success' => true, 'log_path' => $relativePath]);
                }
            }
        }

        return response()->json(['success' => false, 'message' => 'Could not auto-detect log path. Please enter it manually.']);
    }
}
