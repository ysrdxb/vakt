<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Improvement;

class ImprovementController extends Controller
{
    public function index(Request $request)
    {
        $projectId = $request->input('project_id');

        $cols = ['proposed', 'client_review', 'approved', 'in_progress', 'done', 'declined'];
        $columnedItems = [];
        
        foreach ($cols as $col) {
            $columnedItems[$col] = Improvement::with('project')
                ->when($projectId, function ($q) use ($projectId) {
                    $q->where('project_id', $projectId);
                })
                ->where('status', $col)
                ->orderByDesc('created_at')
                ->get();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'columnedItems' => $columnedItems
            ]);
        }

        $projects = Project::orderBy('domain')->get();
        return view('improvements.index', compact('columnedItems', 'projects', 'projectId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string',
            'priority' => 'required|string',
            'effort' => 'required|string',
        ]);

        $improvement = Improvement::create(array_merge($validated, [
            'status' => 'proposed',
            'proposed_by' => auth()->id()
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Improvement added.',
            'improvement' => $improvement->load('project')
        ]);
    }

    public function updateStatus(Request $request, Improvement $improvement)
    {
        $status = $request->input('status');
        $improvement->update(['status' => $status]);
        
        return response()->json([
            'success' => true,
            'message' => 'Card moved to ' . str_replace('_', ' ', $status),
            'improvement' => $improvement->refresh()->load('project')
        ]);
    }
}
