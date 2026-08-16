<?php

namespace App\Jobs;

use App\Models\Project;
use App\Services\LogParserService;
use App\Services\IncidentAutoCreatorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ScanProjectLogs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;

    public function handle(LogParserService $parser, IncidentAutoCreatorService $autoCreator): void
    {
        $projects = Project::where('active', true)
            ->where('server_type', 'same_server')
            ->whereNotNull('server_path')
            ->get();

        foreach ($projects as $project) {
            try {
                $check = $parser->parseFile($project);

                if ($check->critical_patterns_found) {
                    $autoCreator->checkAndCreate($project, $check->critical_patterns_found);
                }
            } catch (\Exception $e) {
                \Log::error("ScanProjectLogs failed for {$project->domain}: " . $e->getMessage());
            }
        }
    }
}
