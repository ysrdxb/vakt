<?php

namespace App\Services;

use App\Models\Project;
use App\Models\FileSnapshot;
use App\Models\Incident;

class FileIntegrityService
{
    protected array $suspiciousPatterns = [
        'eval(',
        'base64_decode(',
        'system(',
        'exec(',
        'shell_exec(',
        'passthru(',
        'proc_open(',
        '$_REQUEST[',
        'str_rot13(',
        'gzinflate(',
    ];

    protected array $criticalFiles = [
        '.env',
        'index.php',
        'public/index.php',
        '.htaccess',
        'public/.htaccess',
    ];

    protected array $watchDirs = [
        'public',
        'config',
        'routes',
    ];

    public function takeSnapshot(Project $project): int
    {
        if (!$project->server_path || !is_dir($project->server_path)) {
            return 0;
        }

        $count = 0;
        $basePath = rtrim($project->server_path, '/');

        foreach ($this->criticalFiles as $file) {
            $fullPath = $basePath . '/' . $file;
            if (file_exists($fullPath)) {
                $this->snapshotFile($project, $fullPath, $file);
                $count++;
            }
        }

        foreach ($this->watchDirs as $dir) {
            $fullDir = $basePath . '/' . $dir;
            if (is_dir($fullDir)) {
                $count += $this->snapshotDirectory($project, $fullDir, $dir);
            }
        }

        return $count;
    }

    public function checkIntegrity(Project $project): array
    {
        if (!$project->server_path || !is_dir($project->server_path)) {
            return ['changed' => 0, 'new' => 0, 'suspicious' => 0];
        }

        $basePath = rtrim($project->server_path, '/');
        $changed = 0;
        $new = 0;
        $suspicious = 0;

        $snapshots = FileSnapshot::where('project_id', $project->id)
            ->get()
            ->keyBy('file_path');

        // Check all critical files
        foreach ($this->criticalFiles as $file) {
            $fullPath = $basePath . '/' . $file;
            if (file_exists($fullPath)) {
                $result = $this->checkFile($project, $fullPath, $file, $snapshots);
                $changed   += $result === 'changed'    ? 1 : 0;
                $new       += $result === 'new'        ? 1 : 0;
                $suspicious += $result === 'suspicious' ? 1 : 0;
            }
        }

        // Check watch directories
        foreach ($this->watchDirs as $dir) {
            $fullDir = $basePath . '/' . $dir;
            if (is_dir($fullDir)) {
                $results = $this->checkDirectory($project, $fullDir, $dir, $snapshots);
                $changed   += $results['changed'];
                $new       += $results['new'];
                $suspicious += $results['suspicious'];
            }
        }

        return compact('changed', 'new', 'suspicious');
    }

    protected function snapshotFile(Project $project, string $fullPath, string $relativePath): void
    {
        $content = file_get_contents($fullPath);
        $hash    = hash('sha256', $content);
        $size    = filesize($fullPath);
        $mtime   = \Carbon\Carbon::createFromTimestamp(filemtime($fullPath));
        $flags   = $this->detectSuspiciousContent($content);

        FileSnapshot::updateOrCreate(
            ['project_id' => $project->id, 'file_path' => $relativePath],
            [
                'file_hash'       => $hash,
                'file_size'       => $size,
                'last_modified'   => $mtime,
                'status'          => $flags ? 'suspicious' : 'clean',
                'flagged_patterns' => $flags ?: null,
                'first_seen_at'   => now(),
            ]
        );
    }

    protected function checkFile(Project $project, string $fullPath, string $relativePath, $snapshots): string
    {
        $content = file_get_contents($fullPath);
        $hash    = hash('sha256', $content);
        $size    = filesize($fullPath);
        $mtime   = \Carbon\Carbon::createFromTimestamp(filemtime($fullPath));
        $flags   = $this->detectSuspiciousContent($content);

        if (!isset($snapshots[$relativePath])) {
            // New file
            $status = 'new';
            if ($flags || str_ends_with($relativePath, '.php') && str_contains($relativePath, 'public/')) {
                $status = 'suspicious';
                $this->createAutoIncident($project, "New suspicious file detected: {$relativePath}", 'p1');
            }

            FileSnapshot::create([
                'project_id'       => $project->id,
                'file_path'        => $relativePath,
                'file_hash'        => $hash,
                'file_size'        => $size,
                'last_modified'    => $mtime,
                'status'           => $status,
                'flagged_patterns' => $flags ?: null,
                'first_seen_at'    => now(),
                'changed_at'       => now(),
            ]);

            return $status;
        }

        $snapshot = $snapshots[$relativePath];

        if ($snapshot->file_hash !== $hash) {
            $status = $flags ? 'suspicious' : 'changed';

            if (in_array($relativePath, ['.env', 'public/index.php', 'index.php'])) {
                $this->createAutoIncident($project, "Critical file changed: {$relativePath}", 'p1');
            }

            $snapshot->update([
                'file_hash'        => $hash,
                'file_size'        => $size,
                'last_modified'    => $mtime,
                'status'           => $status,
                'flagged_patterns' => $flags ?: null,
                'changed_at'       => now(),
            ]);

            return $status;
        }

        if ($flags && $snapshot->status !== 'suspicious') {
            $snapshot->update(['status' => 'suspicious', 'flagged_patterns' => $flags]);
            return 'suspicious';
        }

        return 'clean';
    }

    protected function snapshotDirectory(Project $project, string $dir, string $relBase): int
    {
        $count = 0;
        try {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS));
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $rel = $relBase . '/' . str_replace($dir . '/', '', $file->getPathname());
                    $this->snapshotFile($project, $file->getPathname(), $rel);
                    $count++;
                }
            }
        } catch (\Exception $e) {}
        return $count;
    }

    protected function checkDirectory(Project $project, string $dir, string $relBase, $snapshots): array
    {
        $result = ['changed' => 0, 'new' => 0, 'suspicious' => 0];
        try {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS));
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $rel = $relBase . '/' . str_replace($dir . '/', '', $file->getPathname());
                    $status = $this->checkFile($project, $file->getPathname(), $rel, $snapshots);
                    if ($status === 'changed')    $result['changed']++;
                    if ($status === 'new')        $result['new']++;
                    if ($status === 'suspicious') $result['suspicious']++;
                }
            }
        } catch (\Exception $e) {}
        return $result;
    }

    protected function detectSuspiciousContent(string $content): array
    {
        $found = [];
        foreach ($this->suspiciousPatterns as $pattern) {
            if (str_contains($content, $pattern)) {
                $found[] = $pattern;
            }
        }
        return $found;
    }

    protected function createAutoIncident(Project $project, string $title, string $severity): void
    {
        $existing = Incident::where('project_id', $project->id)
            ->where('title', $title)
            ->whereNotIn('status', ['resolved', 'closed'])
            ->exists();

        if (!$existing) {
            Incident::create([
                'project_id'  => $project->id,
                'title'       => $title,
                'severity'    => $severity,
                'status'      => 'open',
                'source'      => 'auto_detected',
                'detected_at' => now(),
            ]);
        }
    }
}
