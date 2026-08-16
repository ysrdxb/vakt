<?php

namespace App\Jobs;

use App\Models\Project;
use App\Services\FileIntegrityService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ScanFileIntegrity implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 180;

    public function handle(FileIntegrityService $service): void
    {
        $projects = Project::where('active', true)
            ->where('server_type', 'same_server')
            ->whereNotNull('server_path')
            ->get();

        foreach ($projects as $project) {
            try {
                // If no snapshots exist yet, take an initial snapshot
                if ($project->fileSnapshots()->count() === 0) {
                    $service->takeSnapshot($project);
                } else {
                    $service->checkIntegrity($project);
                }
            } catch (\Exception $e) {
                \Log::error("ScanFileIntegrity failed for {$project->domain}: " . $e->getMessage());
            }
        }
    }
}
