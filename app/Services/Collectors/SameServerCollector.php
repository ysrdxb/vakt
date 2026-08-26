<?php

namespace App\Services\Collectors;

use App\Models\Project;
use App\DTOs\CollectionResult;
use Illuminate\Support\Facades\Log;

class SameServerCollector
{
    private string $basePath;

    public function __construct(private Project $project)
    {
        $this->basePath = rtrim($project->server_path, '/');
    }

    public function collect(): CollectionResult
    {
        return new CollectionResult(
            projectId:    $this->project->id,
            collectedAt:  now(),
            logEntries:   $this->readLogs(),
            fileChanges:  $this->checkFileIntegrity(),
            envStatus:    $this->checkEnv(),
            phpErrors:    $this->readPhpErrors(),
            uploadScan:   $this->scanUploads(),
            systemMetrics: [],
            backupStatus: $this->checkBackupStatus(),
            secretsExposure: $this->checkSecretsExposure(),
            source:       'filesystem',
        );
    }

    private function readLogs(): array
    {
        $primaryPath = $this->resolvePath($this->project->log_path);

        $candidatePaths = array_unique([
            $primaryPath,
            $this->basePath . '/logs/errors.log',
            $this->basePath . '/logs/error.log',
            $this->basePath . '/logs/php-error.log',
            $this->basePath . '/vakt-logs/error.log',
        ]);

        $path = null;
        foreach ($candidatePaths as $candidate) {
            if ($this->pathSafe($candidate) && @is_readable($candidate)) {
                $path = $candidate;
                break;
            }
        }

        if (!$path) {
            return ['error' => 'Log file not accessible. Tried: ' . implode(' | ', $candidatePaths)];
        }

        // Read last 2MB of log file only — never load entire file into memory
        $size   = filesize($path);
        $offset = max(0, $size - (2 * 1024 * 1024));
        $handle = fopen($path, 'r');
        fseek($handle, $offset);
        $content = fread($handle, $size - $offset);
        fclose($handle);

        return $this->parseLogContent($content);
    }

    private function checkEnv(): array
    {
        $path = $this->basePath . '/.env';

        if (!$this->pathSafe($path) || !is_readable($path)) {
            return ['readable' => false];
        }

        $content = file_get_contents($path);

        return [
            'readable'        => true,
            'debug_on'        => str_contains($content, 'APP_DEBUG=true'),
            'env_production'  => str_contains($content, 'APP_ENV=production'),
            'has_api_keys'    => preg_match('/API_KEY|SECRET|TOKEN/i', $content) === 1,
            'hash'            => md5($content), // to detect changes
            'size'            => strlen($content),
        ];
    }

    private function readPhpErrors(): array
    {
        if (!$this->project->php_error_log_path) {
            return [];
        }

        $path = $this->project->php_error_log_path; // absolute path
        if (!$this->pathSafe($path) || !is_readable($path)) {
            return [];
        }

        $size    = filesize($path);
        $offset  = max(0, $size - (512 * 1024)); // last 512KB only
        $handle  = fopen($path, 'r');
        fseek($handle, $offset);
        $content = fread($handle, $size - $offset);
        fclose($handle);

        return $this->parsePhpErrors($content);
    }

    private function checkFileIntegrity(): array
    {
        $criticalFiles = [
            $this->basePath . '/.env',
            $this->basePath . '/public/index.php',
            $this->basePath . '/public/.htaccess',
            $this->basePath . '/bootstrap/app.php',
        ];

        $results = [];

        foreach ($criticalFiles as $file) {
            if (!$this->pathSafe($file)) continue;
            if (!file_exists($file)) {
                $results[] = ['file' => $file, 'status' => 'missing'];
                continue;
            }
            $results[] = [
                'file'     => str_replace($this->basePath . '/', '', $file),
                'hash'     => md5_file($file),
                'size'     => filesize($file),
                'modified' => filemtime($file),
                'status'   => 'found',
            ];
        }

        return $results;
    }

    private function scanUploads(): array
    {
        if (!$this->project->modules['upload_scan'] ?? false) {
            return [];
        }

        $uploadsPath = $this->basePath . '/public/uploads';
        if (!is_dir($uploadsPath)) {
            $uploadsPath = $this->basePath . '/storage/app/public';
        }
        if (!is_dir($uploadsPath)) {
            return ['status' => 'no_upload_dir'];
        }

        $suspicious = [];
        $iterator   = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($uploadsPath, \FilesystemIterator::SKIP_DOTS)
        );

        $maliciousPatterns = [
            'eval('        , 'base64_decode(' , 'system('    ,
            'exec('        , 'shell_exec('    , 'passthru('  ,
            'proc_open('   , '$_REQUEST['     , 'str_rot13(' ,
            'gzinflate('   , 'assert('        , 'preg_replace.*\/e',
        ];

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') continue;

            $content = file_get_contents($file->getPathname());
            $found   = [];

            foreach ($maliciousPatterns as $pattern) {
                if (str_contains($content, $pattern)) {
                    $found[] = $pattern;
                }
            }

            if (!empty($found)) {
                $suspicious[] = [
                    'file'     => str_replace($this->basePath . '/', '', $file->getPathname()),
                    'patterns' => $found,
                    'size'     => $file->getSize(),
                    'modified' => $file->getMTime(),
                ];
            }
        }

        return $suspicious;
    }

    /**
     * SECURITY: Ensure path stays within project's base path.
     * Prevents path traversal attacks on the SOC itself.
     */
    private function pathSafe(string $path): bool
    {
        $real = realpath(dirname($path));
        if ($real === false) return false;

        // Allow php error logs which may be outside project root
        if ($path === $this->project->php_error_log_path) return true;

        // All other paths must be within allowed server directories
        return str_starts_with($real, '/home/') || str_starts_with($real, '/var/www/') || str_starts_with($real, '/Users/') || str_starts_with($real, 'C:\\');
    }

    private function resolvePath(string $relativePath): string
    {
        if (str_starts_with($relativePath, '/')) {
            return $relativePath; // already absolute
        }
        return $this->basePath . '/' . ltrim($relativePath, '/');
    }

    private function parseLogContent(string $content): array 
    {
        $lines = explode("\n", $content);
        $entries = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // 1. Try Laravel Format: [2023-01-01 12:00:00] local.ERROR: message
            if (preg_match('/^\[(.*?)\] (.*?)\.(.*?): (.*)/', $line, $matches)) {
                $entries[] = [
                    'timestamp' => $matches[1],
                    'env'       => $matches[2],
                    'level'     => strtolower($matches[3]),
                    'message'   => $matches[4]
                ];
                continue;
            }

            // 2. Try Apache Error Log Format: [Wed Oct 11 14:32:52 2023] [error] [client 127.0.0.1] message
            if (preg_match('/^\[(.*?)\] \[([a-z]+)\] (?:\[client .*?\] )?(.*)/', $line, $matches)) {
                $entries[] = [
                    'timestamp' => $matches[1],
                    'env'       => 'apache',
                    'level'     => strtolower($matches[2]),
                    'message'   => $matches[3]
                ];
                continue;
            }

            // 3. Try Nginx Error Log Format: 2023/10/11 14:32:52 [error] 1234#0: *5678 message
            if (preg_match('/^([0-9]{4}\/[0-9]{2}\/[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}) \[([a-z]+)\] (.*)/', $line, $matches)) {
                $entries[] = [
                    'timestamp' => $matches[1],
                    'env'       => 'nginx',
                    'level'     => strtolower($matches[2]),
                    'message'   => $matches[3]
                ];
                continue;
            }
            
            // 4. Fallback for unknown formats (just display as info)
            $entries[] = [
                'timestamp' => now()->toDateTimeString(),
                'env'       => 'unknown',
                'level'     => 'info',
                'message'   => $line
            ];
        }
        
        return array_slice($entries, -200); // Last 200 entries
    }

    private function parsePhpErrors(string $content): array 
    {
        $lines = explode("\n", $content);
        return array_slice(array_filter($lines), -100);
    }

    private function checkBackupStatus(): array
    {
        $backupDirs = [
            $this->basePath . '/storage/app/backups',
            $this->basePath . '/storage/app/Laravel',
            $this->basePath . '/storage/app',
            $this->basePath . '/storage/backups',
        ];

        $latestBackupTime = 0;
        $latestBackupFile = null;

        foreach ($backupDirs as $dir) {
            if (!is_dir($dir)) continue;

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                $ext = strtolower($file->getExtension());
                if ($ext === 'zip' || $ext === 'sql') {
                    if ($file->getSize() > 102400 && $file->getMTime() > $latestBackupTime) {
                        $latestBackupTime = $file->getMTime();
                        $latestBackupFile = $file->getPathname();
                    }
                }
            }
        }

        return [
            'healthy'     => $latestBackupTime > (time() - 86400),
            'latest_time' => $latestBackupTime,
            'latest_file' => $latestBackupFile ? basename($latestBackupFile) : null,
        ];
    }

    private function checkSecretsExposure(): array
    {
        $path = $this->resolvePath($this->project->log_path);
        if (!$this->pathSafe($path) || !is_readable($path)) {
            return ['exposed' => false, 'matches' => []];
        }

        $size   = filesize($path);
        $offset = max(0, $size - (512 * 1024)); // 512KB tail
        $handle = fopen($path, 'r');
        fseek($handle, $offset);
        $content = fread($handle, $size - $offset);
        fclose($handle);

        $pattern = '/(sk_live_[0-9a-zA-Z]{24,}|rk_live_[0-9a-zA-Z]{24,}|sk-[a-zA-Z0-9]{32,}|sk-proj-[a-zA-Z0-9_-]{32,})/';
        
        $matches = [];
        if (preg_match_all($pattern, $content, $found)) {
            foreach ($found[0] as $key) {
                $matches[] = substr($key, 0, 8) . '***'; 
            }
        }

        $matches = array_unique($matches);

        return [
            'exposed' => count($matches) > 0,
            'matches' => array_values($matches),
        ];
    }
}
