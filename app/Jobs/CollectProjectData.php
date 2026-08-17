<?php

namespace App\Jobs;

use App\Models\Project;
use App\Services\Collectors\CollectorFactory;
use App\Services\AnalyzerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class CollectProjectData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;        // NEVER retry failed collection jobs automatically
    public int $timeout = 30;     // hard 30s limit per job

    public function __construct(private int $projectId) {}

    public function handle(AnalyzerService $analyzer): void
    {
        $project = Project::find($this->projectId);

        if (!$project || !$project->active) return;

        // Mutex — prevents two jobs for same project running simultaneously
        $lockKey = "collecting_project_{$this->projectId}";
        if (Cache::has($lockKey)) return;
        Cache::put($lockKey, true, 60);

        try {
            $result = CollectorFactory::collect($project);

            if ($result->isSkipped()) return;

            if ($result->isFailed()) {
                $project->update([
                    'status'          => 'unknown',
                    'last_checked_at' => now(),
                    'last_error'      => $result->error,
                ]);
                return;
            }

            // Analyze and store
            $analyzer->process($result);

            $project->update([
                'status'          => $result->overallStatus(),
                'last_checked_at' => now(),
                'last_error'      => null,
            ]);

        } finally {
            Cache::forget($lockKey);
        }
    }
}
