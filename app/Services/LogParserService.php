<?php

namespace App\Services;

use App\Models\Project;
use App\Models\LogEntry;
use App\Models\MonitoringCheck;

class LogParserService
{
    protected array $patterns = [
        'sql_error'        => '/SQLSTATE|PDOException|Query failed/i',
        'auth_failure'     => '/Unauthenticated|401|403|unauthorized/i',
        'brute_force'      => '/Too many login attempts|throttle/i',
        'php_error'        => '/Fatal error|Parse error|Warning:|Notice:/i',
        'api_key_exposed'  => '/api.key|API_KEY|secret|token/i',
        'suspicious_curl'  => '/curl_exec|file_get_contents.*http/i',
        'eval_detected'    => '/eval\(|base64_decode\(|str_rot13/i',
        'path_traversal'   => '/\.\.\//i',
        'sql_injection'    => '/UNION SELECT|DROP TABLE|INSERT INTO.*SELECT/i',
        'xss_attempt'      => '/<script|javascript:|onerror=/i',
        'mass_assignment'  => '/MassAssignment|fillable/i',
        'exception'        => '/\[ERROR\]|\[CRITICAL\]|Exception/i',
        'env_exposure'     => '/APP_KEY|DB_PASSWORD|MAIL_PASSWORD/i',
    ];

    public function parseFile(Project $project): MonitoringCheck
    {
        $start = microtime(true);
        $content = '';
        $linesScanned = 0;
        $errorsFound = 0;
        $warningsFound = 0;
        $criticalPatterns = [];

        try {
            $logPath = rtrim($project->server_path, '/') . '/' . ltrim($project->log_path ?? 'storage/logs/laravel.log', '/');

            if (file_exists($logPath) && is_readable($logPath)) {
                // Read last 1000 lines for performance
                $content = $this->tailFile($logPath, 1000);
                $linesScanned = substr_count($content, "\n") + 1;
            }
        } catch (\Exception $e) {
            \Log::error("Log parse error for {$project->domain}: " . $e->getMessage());
        }

        if ($content) {
            [$errorsFound, $warningsFound, $criticalPatterns] = $this->parseContent($project, $content);
        }

        $duration = (int) ((microtime(true) - $start) * 1000);

        $status = 'ok';
        if (!empty($criticalPatterns)) $status = 'critical';
        elseif ($errorsFound > 0 || $warningsFound > 5) $status = 'warning';

        // Update project status
        $project->update([
            'status'          => match($status) {
                'critical' => 'critical',
                'warning'  => 'warning',
                default    => 'healthy',
            },
            'last_checked_at' => now(),
        ]);

        return MonitoringCheck::create([
            'project_id'              => $project->id,
            'checked_at'              => now(),
            'status'                  => $status,
            'log_lines_scanned'       => $linesScanned,
            'errors_found'            => $errorsFound,
            'warnings_found'          => $warningsFound,
            'critical_patterns_found' => $criticalPatterns ?: null,
            'duration_ms'             => $duration,
        ]);
    }

    public function parseRawContent(Project $project, string $content): void
    {
        $this->parseContent($project, $content);
    }

    protected function parseContent(Project $project, string $content): array
    {
        $lines = array_filter(explode("\n", $content));
        $errorsFound = 0;
        $warningsFound = 0;
        $criticalPatterns = [];
        $ipPattern = '/\b(?:\d{1,3}\.){3}\d{1,3}\b/';

        foreach ($lines as $line) {
            $detectedPatterns = [];
            $level = $this->detectLevel($line);

            if (in_array($level, ['error', 'critical'])) $errorsFound++;
            if ($level === 'warning') $warningsFound++;

            foreach ($this->patterns as $name => $pattern) {
                if (preg_match($pattern, $line)) {
                    $detectedPatterns[] = $name;
                    if (in_array($name, ['eval_detected', 'sql_injection', 'api_key_exposed', 'env_exposure', 'xss_attempt'])) {
                        $criticalPatterns[$name] = ($criticalPatterns[$name] ?? 0) + 1;
                    }
                }
            }

            if (!empty($detectedPatterns) || in_array($level, ['error', 'critical'])) {
                preg_match($ipPattern, $line, $ipMatches);

                LogEntry::updateOrCreate(
                    [
                        'project_id'  => $project->id,
                        'message'     => substr(trim($line), 0, 500),
                        'occurred_at' => $this->extractTimestamp($line) ?? now(),
                    ],
                    [
                        'level'             => $level,
                        'detected_patterns' => $detectedPatterns ?: null,
                        'ip_address'        => $ipMatches[0] ?? null,
                    ]
                );
            }
        }

        return [$errorsFound, $warningsFound, $criticalPatterns];
    }

    protected function detectLevel(string $line): string
    {
        if (preg_match('/\[CRITICAL\]|CRITICAL|Fatal error/i', $line)) return 'critical';
        if (preg_match('/\[ERROR\]|ERROR|Exception|SQLSTATE/i', $line))   return 'error';
        if (preg_match('/\[WARNING\]|WARNING|Warning:|Notice:/i', $line))  return 'warning';
        if (preg_match('/\[INFO\]|INFO/i', $line))                          return 'info';
        return 'debug';
    }

    protected function extractTimestamp(string $line): ?\Carbon\Carbon
    {
        if (preg_match('/\[(\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2})/i', $line, $m)) {
            try { return \Carbon\Carbon::parse($m[1]); } catch (\Exception) {}
        }
        return null;
    }

    protected function tailFile(string $path, int $lines): string
    {
        $file = new \SplFileObject($path, 'r');
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key();
        $start = max(0, $totalLines - $lines);
        $file->seek($start);
        $content = '';
        while (!$file->eof()) {
            $content .= $file->fgets();
        }
        return $content;
    }
}
