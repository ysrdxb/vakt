{!! "<?php\n" !!}
// ============================================================
// SOC Agent v1.0 — Vakt Security Platform
// Project: {{ $project->domain }}
// Generated: {{ now()->format('Y-m-d H:i:s') }}
//
// INSTALLATION:
//   1. Place this file in your project root or a private directory
//   2. Add to cron: */5 * * * * php /path/to/soc-agent-{{ $project->domain }}.php
//   3. Make sure the file is NOT web-accessible
// ============================================================

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    define('SOC_ENDPOINT', '{{ route('api.agent.report') }}');
    define('PROJECT_ID', '{{ $project->id }}');
    define('SECRET_KEY', '{{ $project->agent_secret }}');
    define('LOG_PATH', __DIR__ . '/storage/logs/laravel.log');
    define('PHP_ERROR_LOG', '{{ $project->php_error_log_path ?: '/var/log/php_errors.log' }}');

    // ── Collect data ──────────────────────────────────────────────
    $disk_free = @disk_free_space(__DIR__);
    
    $data = [
        'project_id'    => PROJECT_ID,
        'timestamp'     => date('c'),
        'log_tail'      => getLogTail(500),
        'php_errors'    => getPhpErrors(),
        'file_changes'  => getRecentFileChanges(),
        'env_debug'     => getEnvDebugStatus(),
        'php_version'   => PHP_VERSION,
        'disk_free_gb'  => $disk_free !== false ? round($disk_free / 1073741824, 2) : 0,
    ];

    // ── Sign with HMAC ────────────────────────────────────────────
    $payload   = json_encode($data);
    $signature = hash_hmac('sha256', $payload, SECRET_KEY);

    // ── Send to SOC ───────────────────────────────────────────────
    $ch = curl_init(SOC_ENDPOINT);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'X-Agent-Signature: ' . $signature,
            'X-Project-ID: ' . PROJECT_ID,
        ],
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $response = curl_exec($ch);
    $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error    = curl_error($ch);
    curl_close($ch);

    if ($code !== 200) {
        @error_log("SOC Agent: Failed to report. HTTP {$code}. Response: {$response}");
        echo "ERROR: Failed to report to Vakt Hub.\nHTTP Code: {$code}\nResponse: {$response}\nEndpoint: " . SOC_ENDPOINT . "\nCURL Error: {$error}\n";
    } else {
        echo "SUCCESS: Agent payload sent to Vakt Hub successfully.\n";
    }

} catch (\Throwable $e) {
    echo "AGENT CRASHED: " . $e->getMessage() . " on line " . $e->getLine() . "\n";
}

// ── Helper functions ──────────────────────────────────────────
function getLogTail(int $lines): string
{
    if (!file_exists(LOG_PATH) || !is_readable(LOG_PATH)) return '';
    $file = new SplFileObject(LOG_PATH, 'r');
    $file->seek(PHP_INT_MAX);
    $total = $file->key();
    $start = max(0, $total - $lines);
    $file->seek($start);
    $out = '';
    while (!$file->eof()) $out .= $file->fgets();
    return $out;
}

function isShellEnabled(): bool
{
    if (!function_exists('shell_exec')) return false;
    $disabled = explode(',', ini_get('disable_functions'));
    return !in_array('shell_exec', array_map('trim', $disabled));
}

function getPhpErrors(): array
{
    $errors = [];
    if (file_exists(PHP_ERROR_LOG) && is_readable(PHP_ERROR_LOG)) {
        if (!isShellEnabled()) return ['shell_exec is disabled on this server.'];
        $tail = shell_exec('tail -50 ' . escapeshellarg(PHP_ERROR_LOG)) ?? '';
        $lines = array_filter(explode("\n", $tail));
        foreach (array_slice($lines, -20) as $line) {
            if (trim($line)) $errors[] = $line;
        }
    }
    return $errors;
}

function getRecentFileChanges(): array
{
    if (!isShellEnabled()) return ['shell_exec is disabled on this server.'];
    $changes = [];
    $rootDir = __DIR__;
    try {
        $cmd = 'find ' . escapeshellarg($rootDir) . ' -newer ' . escapeshellarg($rootDir . '/.env') . ' -type f -not -path "*/vendor/*" -not -path "*/.git/*" -not -path "*/node_modules/*" 2>/dev/null | head -20';
        $output = shell_exec($cmd) ?? '';
        $changes = array_filter(explode("\n", trim($output)));
    } catch (Exception $e) {}
    return array_values($changes);
}

function getEnvDebugStatus(): bool
{
    $envFile = __DIR__ . '/.env';
    if (!file_exists($envFile)) return false;
    $content = file_get_contents($envFile);
    return (bool) preg_match('/^APP_DEBUG\s*=\s*true/im', $content);
}
