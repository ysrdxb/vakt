<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Project;
use App\Models\SqaReport as SqaReportModel;
use App\Models\Incident;
use App\Models\MonitoringCheck;

class SqaReport extends Component
{
    use WithPagination;

    public ?int $projectId = null;
    public bool $generating = false;

    public function mount(?Project $project = null)
    {
        if ($project && $project->exists) {
            $this->projectId = $project->id;
        }
    }

    public function getProjectsProperty()
    {
        return Project::orderBy('domain')->get();
    }

    public function getReportsProperty()
    {
        return SqaReportModel::with('project')
            ->when($this->projectId, function ($q) {
                $q->where('project_id', $this->projectId);
            })
            ->orderByDesc('created_at')
            ->paginate(10);
    }

    public function generateReport()
    {
        if (!$this->projectId) {
            $this->dispatch('toast', ['type' => 'error', 'title' => 'Error', 'message' => 'Please select a project first.']);
            return;
        }

        $project = Project::find($this->projectId);
        $month = now()->format('Y-m');

        // Check if report already exists for this month
        $existing = SqaReportModel::where('project_id', $this->projectId)->where('period_month', $month)->first();
        if ($existing) {
            $this->dispatch('toast', ['type' => 'warning', 'title' => 'Exists', 'message' => 'Report for this month already exists.']);
            return;
        }

        $incidents = Incident::where('project_id', $this->projectId)
            ->whereYear('detected_at', now()->year)
            ->whereMonth('detected_at', now()->month)
            ->get();

        $checks = MonitoringCheck::where('project_id', $this->projectId)
            ->whereMonth('checked_at', now()->month)
            ->get();

        SqaReportModel::create([
            'project_id' => $this->projectId,
            'title' => $project->domain . ' — ' . now()->format('F Y') . ' Security Report',
            'period_month' => $month,
            'security_score' => $project->security_score,
            'incidents_summary' => [
                'total' => $incidents->count(),
                'p1' => $incidents->where('severity', 'p1')->count(),
                'p2' => $incidents->where('severity', 'p2')->count(),
                'p3' => $incidents->where('severity', 'p3')->count(),
                'resolved' => $incidents->whereIn('status', ['resolved', 'closed'])->count()
            ],
            'monitoring_summary' => [
                'checks_run' => $checks->count(),
                'errors_found' => (int) $checks->sum('errors_found')
            ],
            'status' => 'draft'
        ]);

        $this->dispatch('toast', ['type' => 'success', 'title' => 'Generated', 'message' => 'Monthly report generated successfully.']);
    }

    public function markSent(SqaReportModel $report)
    {
        $report->update(['status' => 'sent', 'sent_at' => now()]);
        $this->dispatch('toast', ['type' => 'success', 'title' => 'Sent', 'message' => 'Report marked as sent to client.']);
    }

    public function render()
    {
        return view('livewire.reports.sqa-report', [
            'projects' => $this->projects,
            'reports' => $this->reports
        ])->layout('layouts.app', ['title' => 'SQA Reports']);
    }
}
