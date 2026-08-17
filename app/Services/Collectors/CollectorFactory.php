<?php

namespace App\Services\Collectors;

use App\Models\Project;
use App\DTOs\CollectionResult;

class CollectorFactory
{
    public static function make(Project $project): SameServerCollector|ExternalAgentCollector|FtpCollector
    {
        return match ($project->server_type) {
            'same_server'    => new SameServerCollector($project),
            'external_agent' => new ExternalAgentCollector($project),
            'ftp'            => new FtpCollector($project),
            default          => throw new \InvalidArgumentException("Unknown server type: {$project->server_type}"),
        };
    }

    public static function collect(Project $project): CollectionResult
    {
        return static::make($project)->collect();
    }
}
