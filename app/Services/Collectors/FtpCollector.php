<?php

namespace App\Services\Collectors;

use App\Models\Project;
use App\DTOs\CollectionResult;
use Illuminate\Support\Facades\Cache;

class FtpCollector
{
    public function __construct(private Project $project) {}

    public function collect(): CollectionResult
    {
        $cacheKey = "ftp_last_call_{$this->project->id}";
        $minInterval = max(($this->project->monitoring_interval_minutes * 60) - 30, 270);

        if (Cache::has($cacheKey)) {
            return CollectionResult::skipped($this->project->id, 'FTP rate limited');
        }

        Cache::put($cacheKey, now(), $minInterval);

        $conn = @ftp_connect($this->project->ftp_host, 21, 10);

        if (!$conn) {
            return CollectionResult::failed($this->project->id, 'FTP connection failed');
        }

        $login = @ftp_login($conn, $this->project->ftp_user, decrypt($this->project->ftp_password));

        if (!$login) {
            ftp_close($conn);
            return CollectionResult::failed($this->project->id, 'FTP authentication failed');
        }

        ftp_pasv($conn, true); // passive mode — safer, less firewall issues

        // Download log to temp file — never stream directly
        $tmpFile = tempnam(sys_get_temp_dir(), 'vakt_ftp_');
        $logPath = '/' . ltrim($this->project->log_path, '/');

        $success = @ftp_get($conn, $tmpFile, $logPath, FTP_BINARY);
        ftp_close($conn); // always close immediately after

        if (!$success) {
            @unlink($tmpFile);
            return CollectionResult::failed($this->project->id, 'Could not download log file via FTP');
        }

        $content = file_get_contents($tmpFile);
        @unlink($tmpFile); // always clean up temp file

        return new CollectionResult(
            projectId:   $this->project->id,
            collectedAt: now(),
            logEntries:  $this->parseLog($content),
            fileChanges: [],
            envStatus:   [],
            phpErrors:   [],
            uploadScan:  [],
            source:      'ftp',
        );
    }

    private function parseLog(string $content): array 
    { 
        $lines = explode("\n", $content);
        $entries = [];
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            if (preg_match('/^\[(.*?)\] (.*?)\.(.*?): (.*)/', $line, $matches)) {
                $entries[] = [
                    'timestamp' => $matches[1],
                    'env' => $matches[2],
                    'level' => strtolower($matches[3]),
                    'message' => $matches[4]
                ];
            }
        }
        return array_slice($entries, -200); 
    }
}
