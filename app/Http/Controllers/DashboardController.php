<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Incident;
use App\Models\MonitoringCheck;
use App\Models\LogEntry;
use App\Models\AgentReport;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::with(['incidents' => function ($q) {
            $q->whereNotIn('status', ['resolved', 'closed']);
        }])->get();

        $openIncidents = Incident::whereNotIn('status', ['resolved', 'closed'])
            ->orderByRaw("CASE severity WHEN 'p1' THEN 1 WHEN 'p2' THEN 2 WHEN 'p3' THEN 3 WHEN 'p4' THEN 4 ELSE 5 END")
            ->with('project')
            ->limit(10)
            ->get();

        $p1Count = Incident::where('severity', 'p1')
            ->whereNotIn('status', ['resolved', 'closed'])
            ->count();

        $overallScore = 0;
        if ($projects->isNotEmpty()) {
            $scores = $projects->map(fn($p) => $p->security_score);
            $overallScore = (int) round($scores->avg());
        }

        $scoreColor = 'danger';
        if ($overallScore >= 80) $scoreColor = 'primary';
        elseif ($overallScore >= 60) $scoreColor = 'warning';

        $recentChecks = MonitoringCheck::with('project')
            ->orderByDesc('checked_at')
            ->limit(5)
            ->get();

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

        $chartData = [
            'categories' => $days->pluck('date')->toArray(),
            'series'     => $days->pluck('count')->toArray(),
        ];

        $agentStatus = Project::where('server_type', 'external_agent')
            ->where('active', true)
            ->get()
            ->map(function ($p) {
                $lastReport = AgentReport::where('project_id', $p->id)
                    ->latest('received_at')
                    ->first();
                return [
                    'project'      => $p,
                    'last_report'  => $lastReport?->received_at,
                    'status'       => $lastReport && $lastReport->received_at->diffInMinutes(now()) < 10
                        ? 'online' : 'offline',
                ];
            });

        return view('dashboard.index', compact(
            'projects',
            'openIncidents',
            'p1Count',
            'overallScore',
            'scoreColor',
            'recentChecks',
            'chartData',
            'agentStatus'
        ));
    }
}
