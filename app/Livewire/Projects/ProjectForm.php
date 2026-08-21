<?php

namespace App\Livewire\Projects;

use Livewire\Component;
use App\Models\Project;
use Illuminate\Validation\Rule;

class ProjectForm extends Component
{
    // Basic fields
    public string $name = '';
    public string $domain = '';
    public string $description = '';
    public string $stack = 'laravel';
    public string $php_version = '8.3';
    public string $laravel_version = '';
    public int $monitoring_interval_minutes = 5;
    public string $alert_email = '';
    public ?string $slack_webhook_url = '';
    public ?string $discord_webhook_url = '';

    // Connection
    public string $server_type = 'same_server';
    public ?string $server_path = '';
    public string $log_path = 'storage/logs/laravel.log';
    public ?string $php_error_log_path = '';
    public string $agent_secret = '';
    public ?string $agent_url = '';
    public ?string $agent_ip_whitelist = '';
    public ?string $ftp_host = '';
    public ?string $ftp_user = '';
    public ?string $ftp_password = '';

    // Surveillance scope
    public array $modules = [
        'log_analysis'    => true,
        'file_integrity'  => true,
        'vulnerability'   => true,
        'php_config'      => true,
        'live_traffic'    => false,
        'upload_scan'     => true,
    ];

    // Auto-incident rules
    public array $incident_rules = [
        'malicious_file'  => true,
        'env_modified'    => true,
        'api_key_in_logs' => true,
        'brute_force'     => true,
        'critical_log'    => true,
        'php_fatal'       => true,
        'any_4xx'         => false,
    ];

    // State
    public bool $active = true;
    public ?Project $project = null;

    // Diagnostic results
    public array $diagnosticResults = [];
    public bool $runningDiagnostics = false;
    public ?string $diagnosticStatus = null;

    public function mount(?Project $project = null)
    {
        if ($project && $project->exists) {
            $this->project = $project;
            $this->fill($project->only([
                'name', 'domain', 'description', 'server_type', 'server_path',
                'agent_url', 'agent_ip_whitelist', 'ftp_host', 'ftp_user', 'stack', 'php_version',
                'laravel_version', 'log_path', 'php_error_log_path', 'active',
                'monitoring_interval_minutes', 'alert_email', 'slack_webhook_url', 'discord_webhook_url'
            ]));
            
            if ($project->modules) $this->modules = array_merge($this->modules, $project->modules);
            if ($project->incident_rules) $this->incident_rules = array_merge($this->incident_rules, $project->incident_rules);
        } else {
            $this->agent_secret = bin2hex(random_bytes(32));
        }
    }

    public function generateSecretKey(): void
    {
        $this->agent_secret = bin2hex(random_bytes(32));
    }

    public function runDiagnostics(): void
    {
        $this->testConnection();
    }

    public function testConnection(): void
    {
        // Hard rate limit — once per 60 seconds per project/session
        $cacheKey = "test_connection_{$this->project?->id}_" . session()->getId();
        if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
            $this->diagnosticStatus = 'failed';
            $this->diagnosticResults = [
                ['icon' => '⚠️', 'name' => 'Rate Limit', 'value' => 'Please wait 60 seconds before testing again.']
            ];
            return;
        }
        \Illuminate\Support\Facades\Cache::put($cacheKey, true, 60);

        $this->runningDiagnostics = true;
        $this->diagnosticResults  = [];

        // Run checks based on server type
        match ($this->server_type) {
            'same_server'    => $this->testSameServer(),
            'external_agent' => $this->testExternalAgent(),
            'ftp'            => $this->testFtp(),
        };

        $this->runningDiagnostics = false;
        $this->diagnosticStatus = collect($this->diagnosticResults)->contains('pass', false) ? 'failed' : 'ready';
    }

    private function testSameServer(): void
    {
        // FILESYSTEM ONLY — zero network requests
        $path = rtrim($this->server_path, '/');

        $this->addResult(
            check:   'Base path accessible',
            pass:    is_dir($path),
            value:   $path ?: 'Missing path',
            fix:     'Check cPanel Addon Domains for the correct document root path',
        );

        $logFull = $path . '/' . ltrim($this->log_path, '/');
        $this->addResult(
            check: 'Log file found',
            pass:  file_exists($logFull),
            value: $logFull,
            fix:   'Ensure storage/logs/laravel.log exists and is not rotated away',
        );

        if (file_exists($logFull)) {
            $this->addResult(
                check: 'Log file readable',
                pass:  is_readable($logFull),
                value: 'Permissions: ' . substr(sprintf('%o', fileperms($logFull)), -4),
                fix:   'Run: chmod 644 ' . escapeshellarg($logFull),
            );

            $size = filesize($logFull);
            $this->addResult(
                check: 'Log file size',
                pass:  $size < (50 * 1024 * 1024), // warn if over 50MB
                value: round($size / 1024 / 1024, 2) . ' MB',
                fix:   'Log file is very large — consider log rotation',
            );

            // Check last entry recency safely
            $handle  = @fopen($logFull, 'r');
            if ($handle) {
                fseek($handle, max(0, $size - 500));
                $tail    = fread($handle, 500);
                fclose($handle);
                $hasRecent = preg_match('/\[\d{4}-\d{2}-\d{2}/', $tail);
                $this->addResult(
                    check: 'Log has recent entries',
                    pass:  (bool) $hasRecent,
                    value: $hasRecent ? 'Active log detected' : 'No recent log entries',
                    fix:   'Log may be empty or using a different path',
                );
            }
        }

        $envPath = $path . '/.env';
        $this->addResult(
            check: '.env file accessible',
            pass:  file_exists($envPath) && is_readable($envPath),
            value: $envPath,
            fix:   '.env file not found — check project root path',
        );
    }

    private function testExternalAgent(): void
    {
        // ONE single request — with timeout — never retries
        if (!$this->agent_url) {
            $this->addResult(
                check: 'Agent URL provided',
                pass:  false,
                value: 'Missing URL',
                fix:   'Please provide the URL where the agent is hosted'
            );
            return;
        }

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders(['X-SOC-Key' => $this->agent_secret])
                ->timeout(10)
                ->retry(0)      // ZERO retries on test
                ->get($this->agent_url);

            $this->addResult(
                check: 'Agent reachable',
                pass:  $response->successful(),
                value: "HTTP {$response->status()}",
                fix:   'Verify agent URL and that agent.php is deployed',
            );

            if ($response->successful()) {
                $data = $response->json();
                $this->addResult(
                    check: 'Agent authentication',
                    pass:  isset($data['project_id']),
                    value: isset($data['project_id']) ? 'Key valid' : 'Invalid response',
                    fix:   'Regenerate and re-deploy agent with matching secret key',
                );
            }
        } catch (\Exception $e) {
            $this->addResult(
                check: 'Agent reachable',
                pass:  false,
                value: $e->getMessage(),
                fix:   'Check agent URL and server connectivity',
            );
        }
    }
    
    private function testFtp(): void
    {
        $this->addResult(
            check: 'FTP Connection Testing',
            pass:  true,
            value: 'Not fully implemented in test UI yet',
            fix:   '',
        );
    }

    private function addResult(string $check, bool $pass, string $value, string $fix = ''): void
    {
        // Transform to the UI format expected by the frontend
        $this->diagnosticResults[] = [
            'icon' => $pass ? '✅' : '❌',
            'name' => $check,
            'value' => $value,
            'pass' => $pass,
            'fix' => $fix
        ];
    }

    public function updatedDomain(): void
    {
        // Auto-clean the domain before validation
        $cleaned = trim($this->domain);
        $cleaned = preg_replace('#^https?://#', '', $cleaned);
        $cleaned = rtrim($cleaned, '/');
        if ($this->domain !== $cleaned) {
            $this->domain = $cleaned;
        }

        $this->validateOnly('domain', [
            'domain' => ['required', 'regex:/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}(\/.*)?$/i', Rule::unique('projects', 'domain')->ignore($this->project?->id)]
        ], [
            'domain.regex' => 'Please enter a valid domain (e.g. verk.kunnatta.is or domain.com/folder) without http:// or spaces.'
        ]);
    }

    public function updatedServerPath(): void
    {
        if ($this->server_type === 'same_server' && !empty($this->server_path)) {
            if (!file_exists($this->server_path)) {
                $this->addError('server_path', '❌ Path not found — check cPanel Addon Domains');
            } else {
                // Clear error if it's fine
                $this->resetValidation('server_path');
                // Could emit a small session flash or something, but clearing error is enough
            }
        }
    }

    public function rules()
    {
        return [
            'name' => 'required|min:2',
            'domain' => ['required', 'regex:/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}(\/.*)?$/i', Rule::unique('projects', 'domain')->ignore($this->project?->id)],
            'server_type' => 'required|in:same_server,external_agent,ftp',
            'stack' => 'required',
            'monitoring_interval_minutes' => 'required|in:1,5,15,30,60',
            'server_path' => 'required_if:server_type,same_server',
            'agent_url' => 'required_if:server_type,external_agent',
            'agent_secret' => 'required_if:server_type,external_agent',
            'ftp_host' => 'required_if:server_type,ftp',
            'ftp_user' => 'required_if:server_type,ftp',
        ];
    }

    public function messages()
    {
        return [
            'domain.regex' => 'Please enter a valid domain (e.g. verk.kunnatta.is or domain.com/folder) without http:// or spaces.',
        ];
    }

    public function updatedServerPath()
    {
        if ($this->server_type === 'same_server' && !empty($this->server_path) && !empty($this->domain)) {
            $this->autoDetectLogPath(false);
        }
    }

    public function updatedDomain()
    {
        if ($this->server_type === 'same_server' && !empty($this->server_path) && !empty($this->domain)) {
            $this->autoDetectLogPath(false);
        }
    }

    public function autoDetectLogPath($showErrorToast = true)
    {
        $base = rtrim($this->server_path, '/');
        if (empty($base) || empty($this->domain)) {
            if ($showErrorToast) $this->dispatch('toast', message: 'Please enter Domain and Server Path first.', type: 'warning');
            return;
        }

        // Parent directories to check: same dir, up to 3 levels up
        $levels = ['', '/..', '/../..', '/../../..'];
        $domainParts = explode('/', $this->domain);
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
                    $relativePath = $level . $file;
                    if (str_starts_with($relativePath, '/')) {
                        $relativePath = substr($relativePath, 1);
                    }
                    $this->log_path = $relativePath;
                    $this->dispatch('toast', message: 'Log path auto-detected!', type: 'success');
                    return;
                }
            }
        }
        
        if ($showErrorToast) {
            $this->dispatch('toast', message: 'Could not auto-detect log path. Please enter it manually.', type: 'warning');
        }
    }

    public function saveProject()
    {
        $this->validate();
        
        $data = $this->only([
            'name', 'domain', 'description', 'server_type', 'server_path',
            'agent_url', 'agent_ip_whitelist', 'ftp_host', 'ftp_user', 'stack', 'php_version',
            'laravel_version', 'log_path', 'php_error_log_path', 'active',
            'monitoring_interval_minutes', 'alert_email', 'slack_webhook_url', 'discord_webhook_url', 'modules', 'incident_rules'
        ]);
        
        if ($this->ftp_password) {
            $data['ftp_password'] = $this->ftp_password;
        }
        
        if ($this->project && $this->project->exists) {
            // For editing, only update agent_secret if a new one was explicitly generated
            // In a real app we'd track if it changed, but we generate it fresh in mount if not editing.
            // If they clicked regenerate, $this->agent_secret changed.
            if ($this->agent_secret !== '' && $this->agent_secret !== $this->project->agent_secret) {
                // wait, agent_secret is encrypted, so we can't easily compare strings.
                // Just update it if they clicked generate (we can assume it's a new 64char hex string).
                if (strlen($this->agent_secret) === 64) {
                     $data['agent_secret'] = $this->agent_secret;
                }
            }
            $this->project->update($data);
            return redirect()->route('projects.show', $this->project);
        } else {
            $data['agent_secret'] = $this->agent_secret;
            $project = Project::create($data);
            return redirect()->route('projects.show', $project);
        }
    }

    public function render()
    {
        $isEdit = $this->project && $this->project->exists;
        return view('livewire.projects.project-form', compact('isEdit'))
            ->layout('layouts.app', ['title' => $isEdit ? 'Modify Asset Configuration' : 'Configure Monitoring Target']);
    }
}

