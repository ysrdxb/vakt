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

    public function sendTestReport(): void
    {
        try {
            // Re-use logic from SendDailySocReport for a single project
            $startDate = now()->subDays(1);
            $endDate = now();

            $uptimeLogs = $this->project->uptimeLogs()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            $totalPings = $uptimeLogs->count();
            $successfulPings = $uptimeLogs->where('status_code', 200)->count();
            $uptimePercentage = $totalPings > 0 ? round(($successfulPings / $totalPings) * 100, 2) : 100;

            $openIncidents = $this->project->incidents()
                ->whereNotIn('status', ['resolved', 'closed'])
                ->count();

            $backupFailed = $this->project->incidents()
                ->where('title', 'like', '%backup missing%')
                ->where('created_at', '>=', $startDate)
                ->exists();

            $stats = [
                'uptime_percentage' => $uptimePercentage,
                'open_incidents'    => $openIncidents,
                'backup_healthy'    => !$backupFailed,
            ];

            app(\App\Services\NotificationService::class)->notifyDailyReport($this->project, $stats);
            
            $this->dispatch('toast', ['type' => 'success', 'title' => 'Report Sent', 'message' => 'Daily SOC Report pushed to webhooks successfully.']);
        } catch (\Exception $e) {
            $this->dispatch('toast', ['type' => 'error', 'title' => 'Report Failed', 'message' => $e->getMessage()]);
        }
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
