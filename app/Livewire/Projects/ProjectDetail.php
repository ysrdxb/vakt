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

    public function confirmWhitelist(): void
    {
        $this->project->update(['firewall_whitelist_confirmed' => true]);
        $this->dispatch('toast', ['type' => 'success', 'title' => 'Confirmed', 'message' => 'Firewall whitelist confirmed. You may now deploy the agent.']);
    }

    public function runScan(): void
    {
        try {
            // Run the exact same collection pipeline that the cron job uses, but synchronously.
            \App\Jobs\CollectProjectData::dispatchSync($this->project->id);
            $this->dispatch('toast', ['type' => 'success', 'title' => 'Scan Complete', 'message' => 'Data pulled successfully from agent.']);
        } catch (\Exception $e) {
            $this->dispatch('toast', ['type' => 'error', 'title' => 'Scan Failed', 'message' => $e->getMessage()]);
        }

        $this->project->refresh();
    }

    public function render()
    {
        $this->project->load(['incidents' => fn($q) => $q->orderByDesc('detected_at')->limit(10), 'monitoringChecks' => fn($q) => $q->orderByDesc('checked_at')->limit(5), 'logEntries' => fn($q) => $q->orderByDesc('occurred_at')->limit(10)]);

        $latestReport = \App\Models\AgentReport::where('project_id', $this->project->id)
                            ->orderByDesc('received_at')
                            ->first();

        $uptimeLogs = \App\Models\UptimeLog::where('project_id', $this->project->id)
                            ->orderByDesc('created_at')
                            ->limit(60)
                            ->get();

        return view('livewire.projects.project-detail', [
            'latestReport' => $latestReport,
            'uptimeLogs' => $uptimeLogs,
        ])->layout('layouts.app', ['title' => $this->project->domain]);
    }
}
