<?php

namespace App\Livewire\Projects;

use Livewire\Component;
use App\Models\Project;

class ProjectDetail extends Component
{
    public Project $project;

    public function mount(Project $project): void
    {
        $this->project = $project;
    }

    public function runScan(): void
    {
        if ($this->project->server_type !== 'same_server') {
            $this->dispatch('toast', ['type' => 'warning', 'title' => 'N/A', 'message' => 'Manual scan is only for same-server projects.']);
            return;
        }

        try {
            $parser = app(\App\Services\LogParserService::class);
            $check  = $parser->parseFile($this->project);

            $this->dispatch('toast', ['type' => 'success', 'title' => 'Scan Complete',
                'message' => "{$check->errors_found} errors, {$check->warnings_found} warnings found."]);
        } catch (\Exception $e) {
            $this->dispatch('toast', ['type' => 'error', 'title' => 'Scan Failed', 'message' => $e->getMessage()]);
        }

        $this->project->refresh();
    }

    public function render()
    {
        $this->project->load(['incidents' => fn($q) => $q->orderByDesc('detected_at')->limit(10), 'monitoringChecks' => fn($q) => $q->orderByDesc('checked_at')->limit(5), 'logEntries' => fn($q) => $q->orderByDesc('occurred_at')->limit(10)]);

        return view('livewire.projects.project-detail')
            ->layout('layouts.app', ['title' => $this->project->domain]);
    }
}
