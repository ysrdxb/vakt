<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $filterStatus = $request->input('filterStatus');

        $projects = Project::when($search, function ($q) use ($search) {
                $q->where('domain', 'like', '%' . $search . '%')
                  ->orWhere('name', 'like', '%' . $search . '%');
            })
            ->when($filterStatus, function ($q) use ($filterStatus) {
                $q->where('status', $filterStatus);
            })
            ->orderBy('status')
            ->orderBy('domain')
            ->get();



        return Inertia::render('ProjectList', compact('projects'));
    }

    public function show(Project $project)
    {
        $project->load([
            'incidents' => fn($q) => $q->orderByDesc('detected_at')->limit(10),
            'monitoringChecks' => fn($q) => $q->orderByDesc('checked_at')->limit(5),
            'logEntries' => fn($q) => $q->orderByDesc('id')->limit(10)
        ]);

        $latestReport = \App\Models\AgentReport::where('project_id', $project->id)
                            ->orderByDesc('received_at')
                            ->first();

        $uptimeLogs = \App\Models\UptimeLog::where('project_id', $project->id)
                            ->orderByDesc('created_at')
                            ->limit(60)
                            ->get();

        return Inertia::render('ProjectDetail', compact('project', 'latestReport', 'uptimeLogs'));
    }

    public function confirmWhitelist(Request $request, Project $project)
    {
        $project->update(['firewall_whitelist_confirmed' => true]);
        return response()->json(['success' => true, 'message' => 'Firewall whitelist confirmed. You may now deploy the agent.']);
    }

    public function runScan(Request $request, Project $project)
    {
        try {
            \App\Jobs\CollectProjectData::dispatchSync($project->id);
            $project->refresh();
            
            if ($project->last_error) {
                return response()->json(['success' => false, 'message' => 'Agent Error: ' . $project->last_error], 500);
            }
            
            return response()->json(['success' => true, 'message' => 'Data pulled successfully. (Note: Only errors and warnings are recorded in Vakt)']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function testReport(Request $request, Project $project)
    {
        try {
            $startDate = now()->subDays(1);
            $endDate = now();

            $uptimeLogs = $project->uptimeLogs()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            $totalPings = $uptimeLogs->count();
            $successfulPings = $uptimeLogs->where('status_code', 200)->count();
            $uptimePercentage = $totalPings > 0 ? round(($successfulPings / $totalPings) * 100, 2) : 100;

            $openIncidents = $project->incidents()
                ->whereNotIn('status', ['resolved', 'closed'])
                ->count();

            $backupFailed = $project->incidents()
                ->where('title', 'like', '%backup missing%')
                ->where('created_at', '>=', $startDate)
                ->exists();

            $stats = [
                'uptime_percentage' => $uptimePercentage,
                'open_incidents'    => $openIncidents,
                'backup_healthy'    => !$backupFailed,
            ];

            app(\App\Services\NotificationService::class)->notifyDailyReport($project, $stats);
            
            return response()->json(['success' => true, 'message' => 'Daily SOC Report pushed to webhooks successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function toggleActive(Request $request, Project $project)
    {
        $project->update(['active' => !$project->active]);
        return response()->json([
            'success' => true,
            'active' => $project->active,
            'message' => $project->domain . ' monitoring ' . ($project->active ? 'enabled' : 'disabled')
        ]);
    }

    public function destroy(Request $request, Project $project)
    {
        \Log::info("deleteProject called for project: " . $project->id);
        $project->delete();
        return response()->json([
            'success' => true,
            'message' => 'Project removed.'
        ]);
    }

    public function create()
    {
        $project = new Project();
        $agent_secret = bin2hex(random_bytes(32));
        $isEdit = false;
        
        return Inertia::render('ProjectForm', compact('project', 'agent_secret', 'isEdit'));
    }

    public function edit(Project $project)
    {
        $agent_secret = $project->agent_secret;
        $isEdit = true;
        
        return Inertia::render('ProjectForm', compact('project', 'agent_secret', 'isEdit'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateProject($request);
        $validated['agent_secret'] = $request->input('agent_secret', bin2hex(random_bytes(32)));
        
        $project = Project::create($validated);
        
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
        
        return redirect()->route('projects.show', $project)
            ->with('success', 'Project configuration updated.');
    }

    private function validateProject(Request $request, ?int $ignoreId = null): array
    {
        // Clean domain
        $domain = trim($request->input('domain', ''));
        $domain = preg_replace('#^https?://#', '', $domain);
        $domain = rtrim($domain, '/');
        $request->merge([
            'domain' => $domain,
            // Cast to int so the integer validation rule passes even when sent as string from Vue
            'monitoring_interval_minutes' => (int) $request->input('monitoring_interval_minutes', 5),
        ]);

        $validated = $request->validate([
            'name'                         => 'required|string|min:2|max:255',
            'domain'                       => ['required', 'regex:/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}(\/.*)?$/i', Rule::unique('projects', 'domain')->ignore($ignoreId)],
            'description'                  => 'nullable|string',
            'server_type'                  => 'required|in:same_server,external_agent,ftp',
            'stack'                        => 'required|string',
            'php_version'                  => 'required|string',
            'laravel_version'              => 'nullable|string',
            'monitoring_interval_minutes'  => 'required|integer|in:1,5,15,30,60',
            'alert_email'                  => 'nullable|email',
            // Conditional fields — validated manually below
            'server_path'                  => 'nullable|string',
            'log_path'                     => 'nullable|string',
            'php_error_log_path'           => 'nullable|string',
            'agent_url'                    => 'nullable|url',
            'agent_secret'                 => 'nullable|string',
            'agent_ip_whitelist'           => 'nullable|string',
            'ftp_host'                     => 'nullable|string',
            'ftp_user'                     => 'nullable|string',
            'active'                       => 'boolean',
            'modules'                      => 'nullable|array',
            'incident_rules'               => 'nullable|array',
        ], [
            'domain.regex' => 'Please enter a valid domain (e.g. verk.kunnatta.is or domain.com/folder) without http:// or spaces.',
        ]);

        // Manual conditional validation
        $serverType = $request->input('server_type');
        if ($serverType === 'same_server' && empty(trim($request->input('server_path', '')))) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'server_path' => ['The server path field is required for same-server connections.'],
            ]);
        }
        if ($serverType === 'external_agent' && empty(trim($request->input('agent_url', '')))) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'agent_url' => ['The agent URL field is required for external agent connections.'],
            ]);
        }
        if ($serverType === 'ftp') {
            if (empty(trim($request->input('ftp_host', '')))) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'ftp_host' => ['The FTP host field is required.'],
                ]);
            }
            if (empty(trim($request->input('ftp_user', '')))) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'ftp_user' => ['The FTP username field is required.'],
                ]);
            }
        }

        return $validated;
    }

    public function testConnection(Request $request)
    {
        $server_type = $request->input('server_type', 'same_server');
        $server_path = (string) $request->input('server_path', '');
        $log_path = (string) $request->input('log_path', 'storage/logs/laravel.log');
        $agent_url = $request->input('agent_url');
        $agent_secret = $request->input('agent_secret');

        $results = [];

        if ($server_type === 'same_server') {
            $path = rtrim($server_path, '/');
            $openBasedir = ini_get('open_basedir');
            $isDir = @is_dir($path);
            
            $results[] = [
                'icon' => $isDir ? '✅' : ($openBasedir ? '⚠️' : '❌'),
                'name' => 'Base path accessible',
                'value' => $path ?: 'Missing path',
                'pass' => $isDir || $openBasedir,
                'fix' => $openBasedir && !$isDir ? 'Web UI blocked by open_basedir (normal on shared hosting). Cron CLI may still have access.' : 'Check cPanel Addon Domains for the correct document root path'
            ];

            $logFull = str_starts_with($log_path, '/') ? $log_path : $path . '/' . ltrim($log_path, '/');
            $exists = @file_exists($logFull);
            $results[] = [
                'icon' => $exists ? '✅' : ($openBasedir ? '⚠️' : '❌'),
                'name' => 'Log file found',
                'value' => $logFull,
                'pass' => $exists || $openBasedir,
                'fix' => $openBasedir && !$exists ? 'Web UI cannot verify due to open_basedir, but CLI may read it.' : 'Ensure log file exists'
            ];

            if ($exists || $openBasedir) {
                $readable = @is_readable($logFull);
                $results[] = [
                    'icon' => $readable ? '✅' : ($openBasedir ? '⚠️' : '❌'),
                    'name' => 'Log file readable',
                    'value' => 'Permissions: ' . ($readable ? substr(sprintf('%o', @fileperms($logFull)), -4) : 'Unknown'),
                    'pass' => $readable || $openBasedir,
                    'fix' => $openBasedir && !$readable ? 'Web UI cannot read due to open_basedir.' : 'Check file permissions'
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
        $base = rtrim((string)$request->input('server_path', ''), '/');
        $domain = (string)$request->input('domain', '');

        if (empty($base) || empty($domain)) {
            return response()->json(['success' => false, 'message' => 'Please enter Domain and Server Path first.']);
        }

        $levels = ['', '/..', '/../..', '/../../..'];
        $domainParts = explode('/', $domain);
        $domainFolder = $domainParts[0];

        $possibleFiles = [
            "/storage/logs/laravel.log",
            "/logs/{$domainFolder}/error.log",
            "/logs/{$domainFolder}/access.log",
            "/logs/{$domainFolder}.log",
            "/{$domainFolder}.log",
            "/error.log"
        ];

        foreach ($levels as $level) {
            $checkDir = $base . $level;
            $realDir = @realpath($checkDir);
            
            if (!$realDir) continue;

            foreach ($possibleFiles as $file) {
                if (@file_exists($realDir . $file) && @is_readable($realDir . $file)) {
                    $relativePath = ltrim($level . $file, '/');
                    return response()->json(['success' => true, 'log_path' => $relativePath]);
                }
            }
        }

        return response()->json(['success' => false, 'message' => 'Could not auto-detect log path. Please enter it manually.']);
    }
}
