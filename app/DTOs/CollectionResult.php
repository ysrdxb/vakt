<?php

namespace App\DTOs;

use Carbon\Carbon;

class CollectionResult
{
    public function __construct(
        public readonly int     $projectId,
        public readonly Carbon  $collectedAt,
        public readonly array   $logEntries,
        public readonly array   $fileChanges,
        public readonly array   $envStatus,
        public readonly array   $phpErrors,
        public readonly array   $uploadScan,
        public readonly array   $systemMetrics,
        public readonly array   $backupStatus,
        public readonly array   $secretsExposure,
        public readonly string  $source,         // filesystem|agent|ftp
        public readonly bool    $skipped = false,
        public readonly bool    $failed  = false,
        public readonly ?string $error   = null,
    ) {}

    public static function skipped(int $projectId, string $reason): self
    {
        return new self(
            projectId:   $projectId,
            collectedAt: now(),
            logEntries:  [],
            fileChanges: [],
            envStatus:   [],
            phpErrors:   [],
            uploadScan:  [],
            systemMetrics: [],
            backupStatus: [],
            secretsExposure: [],
            source:      'none',
            skipped:     true,
            error:       $reason,
        );
    }

    public static function failed(int $projectId, string $reason): self
    {
        return new self(
            projectId:   $projectId,
            collectedAt: now(),
            logEntries:  [],
            fileChanges: [],
            envStatus:   [],
            phpErrors:   [],
            uploadScan:  [],
            systemMetrics: [],
            backupStatus: [],
            secretsExposure: [],
            source:      'none',
            failed:      true,
            error:       $reason,
        );
    }

    public function isSkipped(): bool { return $this->skipped; }
    public function isFailed(): bool  { return $this->failed; }
    public function isSuccess(): bool { return !$this->skipped && !$this->failed; }

    public function overallStatus(): string
    {
        // Determine project status from collected data
        // Returns: healthy | warning | critical | unknown
        if ($this->failed)  return 'unknown';
        if ($this->skipped) return 'unknown';

        foreach ($this->logEntries as $entry) {
            if (str_contains($entry['level'] ?? '', 'critical')) return 'critical';
        }

        if (!empty($this->uploadScan)) return 'critical'; // any suspicious file = critical

        foreach ($this->logEntries as $entry) {
            if (str_contains($entry['level'] ?? '', 'error')) return 'warning';
        }

        if (isset($this->backupStatus['healthy']) && !$this->backupStatus['healthy']) return 'critical';
        if (isset($this->secretsExposure['exposed']) && $this->secretsExposure['exposed']) return 'critical';

        return 'healthy';
    }
}
