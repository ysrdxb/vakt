{!! "<?php\n" !!}
/**
 * Vakt SOC Agent v1.0
 * ==================
 * Place this file in a NON-PUBLIC directory on the target server.
 * Example: /home/user/vakt-agent/agent.php  (NOT inside public_html)
 *
 * Access via a PHP CLI cron or a restricted route — NOT a public URL.
 *
 * RECOMMENDED SETUP:
 * Create a restricted route in the project that proxies to this file,
 * protected by the secret key check below.
 *
 * The SOC server will call this file via a single GET request
 * with the X-SOC-Key header. It returns JSON and exits.
 * No persistent connections. No callbacks. One request, one response.
 */

// ── CONFIG (auto-filled when downloaded from Vakt) ────────────────────────
define('VAKT_SECRET',     '{{ $project->agent_secret }}');
define('VAKT_PROJECT_ID', '{{ $project->id }}');
define('VAKT_LOG_PATH',   __DIR__ . '/../storage/logs/laravel.log');
define('VAKT_MAX_LOG_KB', 512);                // max KB of log to return
// ──────────────────────────────────────────────────────────────────────────

// Reject immediately if not CLI and key is wrong
// This prevents the file from being useful even if someone finds the URL
if (php_sapi_name() !== 'cli') {
    $providedKey = $_SERVER['HTTP_X_SOC_KEY'] ?? '';
    if (!hash_equals(VAKT_SECRET, $providedKey)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
}

// Rate limit: write a timestamp file, reject if called too soon
$rateLimitFile = sys_get_temp_dir() . '/vakt_' . md5(VAKT_PROJECT_ID) . '.lock';
if (file_exists($rateLimitFile)) {
    $lastCall = (int) file_get_contents($rateLimitFile);
    if ((time() - $lastCall) < 240) { // minimum 4 minutes between calls
        http_response_code(429);
        echo json_encode(['error' => 'Rate limited', 'retry_after' => 240]);
        exit;
    }
}
file_put_contents($rateLimitFile, time());

// Collect and respond
header('Content-Type: application/json');
echo json_encode([
    'project_id'   => VAKT_PROJECT_ID,
    'collected_at' => date('c'),
    'log_tail'     => getLogTail(),
    'env_status'   => getEnvStatus(),
    'file_changes' => getRecentFileChanges(),
    'php_errors'   => getPhpErrors(),
    'disk_free'    => disk_free_space(__DIR__),
]);
exit;

// ── COLLECTION FUNCTIONS ───────────────────────────────────────────────────

function getLogTail(): array
{
    if (!file_exists(VAKT_LOG_PATH) || !is_readable(VAKT_LOG_PATH)) {
        return ['error' => 'Log not readable'];
    }
    $maxBytes = VAKT_MAX_LOG_KB * 1024;
    $size     = filesize(VAKT_LOG_PATH);
    $offset   = max(0, $size - $maxBytes);
    $handle   = fopen(VAKT_LOG_PATH, 'r');
    fseek($handle, $offset);
    $content  = fread($handle, $size - $offset);
    fclose($handle);

    // Parse log lines
    $lines   = explode("\n", $content);
    $entries = [];
    foreach ($lines as $line) {
        if (empty(trim($line))) continue;
        $entries[] = $line;
    }
    return array_slice($entries, -200); // last 200 lines max
}

function getEnvStatus(): array
{
    $envPath = dirname(VAKT_LOG_PATH, 3) . '/.env';
    if (!file_exists($envPath)) return ['found' => false];

    $content = file_get_contents($envPath);
    return [
        'found'       => true,
        'debug_on'    => str_contains($content, 'APP_DEBUG=true'),
        'env_set'     => str_contains($content, 'APP_ENV=production'),
        'hash'        => md5($content),
    ];
}

function getRecentFileChanges(): array
{
    $publicPath = dirname(VAKT_LOG_PATH, 3) . '/public';
    if (!is_dir($publicPath)) return [];

    $changed  = [];
    $cutoff   = time() - 600; // files changed in last 10 minutes
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($publicPath, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if ($file->getMTime() > $cutoff) {
            $changed[] = [
                'path'     => $file->getPathname(),
                'modified' => $file->getMTime(),
                'size'     => $file->getSize(),
                'is_php'   => $file->getExtension() === 'php',
            ];
        }
    }
    return $changed;
}

function getPhpErrors(): array
{
    $errorLog = ini_get('error_log');
    if (!$errorLog || !is_readable($errorLog)) return [];

    $size    = filesize($errorLog);
    $offset  = max(0, $size - (256 * 1024));
    $handle  = fopen($errorLog, 'r');
    fseek($handle, $offset);
    $content = fread($handle, $size - $offset);
    fclose($handle);

    return array_slice(explode("\n", $content), -100);
}
