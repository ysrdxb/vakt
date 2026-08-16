<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Project;
use App\Models\Incident;
use App\Models\MonitoringCheck;
use App\Models\LogEntry;

class OperatorDashboard extends Component
{
    public $refreshInterval = 30000; // 30 seconds

    public function getProjectsProperty()
    {
        return Project::with(['incidents' => function ($q) {
            $q->whereNotIn('status', ['resolved', 'closed']);
        }])->get();
    }

    public function getOpenIncidentsProperty()
    {
        return Incident::whereNotIn('status', ['resolved', 'closed'])
            ->orderByRaw("FIELD(severity, 'p1','p2','p3','p4')")
            ->with('project')
            ->limit(10)
            ->get();
    }

    public function getP1CountProperty(): int
    {
        return Incident::where('severity', 'p1')
            ->whereNotIn('status', ['resolved', 'closed'])
            ->count();
    }

    public function getOverallScoreProperty(): int
    {
        $projects = Project::all();
        if ($projects->isEmpty()) return 0;
        $scores = $projects->map(fn($p) => $p->security_score);
        return (int) round($scores->avg());
    }

    public function getScoreColorProperty(): string
    {
        $score = $this->overallScore;
        if ($score >= 80) return 'primary';
        if ($score >= 60) return 'warning';
        return 'danger';
    }

    public function getRecentChecksProperty()
    {
        return MonitoringCheck::with('project')
            ->orderByDesc('checked_at')
            ->limit(5)
            ->get();
    }

    public function getChartDataProperty(): array
    {
        // Last 7 days error counts per day
        $days = collect(range(6, 0))->map(function ($i) {
            $date = now()->subDays($i)->format('Y-m-d');
            $count = LogEntry::whereDate('occurred_at', $date)
                ->whereIn('level', ['error', 'critical'])
                ->count();
            return [
                'date'  => now()->subDays($i)->format('M d'),
                'count' => $count,
            ];
        });

        return [
            'categories' => $days->pluck('date')->toArray(),
            'series'     => $days->pluck('count')->toArray(),
        ];
    }

    public function getAgentStatusProperty()
    {
        return Project::where('server_type', 'external_agent')
            ->where('active', true)
            ->get()
            ->map(function ($p) {
                $lastReport = \App\Models\AgentReport::where('project_id', $p->id)
                    ->latest('received_at')
                    ->first();
                return [
                    'project'      => $p,
                    'last_report'  => $lastReport?->received_at,
                    'status'       => $lastReport && $lastReport->received_at->diffInMinutes(now()) < 10
                        ? 'online' : 'offline',
                ];
            });
    }

    public function render()
    {
        return view('livewire.dashboard.operator-dashboard', [
            'projects'       => $this->projects,
            'openIncidents'  => $this->openIncidents,
            'p1Count'        => $this->p1Count,
            'overallScore'   => $this->overallScore,
            'scoreColor'     => $this->scoreColor,
            'recentChecks'   => $this->recentChecks,
            'chartData'      => $this->chartData,
            'agentStatus'    => $this->agentStatus,
        ])->layout('layouts.app', ['title' => 'Dashboard']);
    }
}
