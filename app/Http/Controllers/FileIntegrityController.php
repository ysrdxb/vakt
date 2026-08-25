<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\FileSnapshot;
use App\Services\FileIntegrityService;

class FileIntegrityController extends Controller
{
    public function index(Request $request, ?Project $project = null)
    {
        $projectId = $request->input('project_id', $project?->id);
        $filterStatus = $request->input('filterStatus');
        
        $q = FileSnapshot::when($projectId, function ($query) use ($projectId) {
            $query->where('project_id', $projectId);
        });

        $stats = [
            'total' => (clone $q)->count(),
            'suspicious' => (clone $q)->where('status', 'suspicious')->count(),
            'changed' => (clone $q)->where('status', 'changed')->count(),
            'clean' => (clone $q)->where('status', 'clean')->count(),
        ];

        $snapshots = FileSnapshot::with('project')
            ->when($projectId, function ($query) use ($projectId) {
                $query->where('project_id', $projectId);
            })
            ->when($filterStatus, function ($query) use ($filterStatus) {
                $query->where('status', $filterStatus);
            })
            ->orderByRaw("FIELD(status,'suspicious','changed','new','deleted','clean')")
            ->paginate(50);

        if ($request->wantsJson()) {
            return response()->json([
                'data' => $snapshots->items(),
                'stats' => $stats,
                'meta' => [
                    'current_page' => $snapshots->currentPage(),
                    'last_page' => $snapshots->lastPage(),
                    'total' => $snapshots->total(),
                    'per_page' => $snapshots->perPage()
                ]
            ]);
        }

        $projects = Project::where('server_type', 'same_server')->orderBy('domain')->get();
        return \Inertia\Inertia::render('FileIntegrityView', compact('snapshots', 'projects', 'projectId', 'stats'));
    }

    public function initScan(Request $request, FileIntegrityService $service)
    {
        $projectId = $request->input('project_id');
        if (!$projectId) {
            return response()->json(['success' => false, 'message' => 'Select a project first.'], 400);
        }

        $project = Project::find($projectId);
        if (!$project) {
            return response()->json(['success' => false, 'message' => 'Project not found.'], 404);
        }

        $service->takeSnapshot($project);

        return response()->json([
            'success' => true,
            'message' => 'File snapshot taken.'
        ]);
    }

    public function approveChange(Request $request, FileSnapshot $snapshot)
    {
        $snapshot->update([
            'status' => 'clean',
            'changed_at' => null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'File change marked as approved.',
            'snapshot' => $snapshot->refresh()
        ]);
    }
}
