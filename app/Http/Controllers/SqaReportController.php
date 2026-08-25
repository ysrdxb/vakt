<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\SqaReport;
use App\Models\Incident;
use App\Models\MonitoringCheck;

class SqaReportController extends Controller
{
    public function index(Request $request, ?Project $project = null)
    {
        $projectId = $request->input('project_id', $project?->id);

        $query = SqaReport::with('project')
            ->when($projectId, function ($q) use ($projectId) {
                $q->where('project_id', $projectId);
            })
            ->orderByDesc('created_at');

        $reports = $query->paginate(10);

        if ($request->wantsJson()) {
            return response()->json([
                'data' => $reports->items(),
                'meta' => [
                    'current_page' => $reports->currentPage(),
                    'last_page' => $reports->lastPage(),
                    'total' => $reports->total(),
                    'per_page' => $reports->perPage()
                ]
            ]);
        }

        $projects = Project::orderBy('domain')->get();
        return view('reports.index', compact('reports', 'projects', 'projectId'));
    }

    public function store(Request $request)
    {
        $projectId = $request->input('project_id');
        if (!$projectId) {
            return response()->json(['success' => false, 'message' => 'Please select a project first.'], 400);
        }

        $project = Project::findOrFail($projectId);
        $month = now()->format('Y-m');

        // Check if report already exists for this month
        $existing = SqaReport::where('project_id', $projectId)->where('period_month', $month)->first();
        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Report for this month already exists.'], 409);
        }

        $incidents = Incident::where('project_id', $projectId)
            ->whereYear('detected_at', now()->year)
            ->whereMonth('detected_at', now()->month)
            ->get();

        $checks = MonitoringCheck::where('project_id', $projectId)
            ->whereMonth('checked_at', now()->month)
            ->get();

        SqaReport::create([
            'project_id' => $projectId,
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

        return response()->json([
            'success' => true,
            'message' => 'Monthly report generated successfully.'
        ]);
    }

    public function show(SqaReport $report, Request $request)
    {
        // For the sake of this migration, we'll just return a placeholder or the actual view if it exists.
        // Assuming there's a view `reports.show`
        if ($request->has('download')) {
            // PDF download logic (mocked)
            return response("PDF generation for {$report->title} not fully implemented.", 200)
                ->header('Content-Type', 'application/pdf');
        }

        return view('reports.show', compact('report'));
    }

    public function markSent(Request $request, SqaReport $report)
    {
        $report->update(['status' => 'sent', 'sent_at' => now()]);
        
        return response()->json([
            'success' => true,
            'message' => 'Report marked as sent to client.',
            'report' => $report->refresh()
        ]);
    }
}
