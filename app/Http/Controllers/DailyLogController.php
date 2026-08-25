<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\DailyLog;

class DailyLogController extends Controller
{
    public function index(Request $request, ?Project $project = null)
    {
        $projectId = $request->input('project_id', $project?->id);
        $selectedDate = $request->input('selectedDate');

        $query = DailyLog::with('project')
            ->when($projectId, function ($q) use ($projectId) {
                $q->where('project_id', $projectId);
            })
            ->when($selectedDate, function ($q) use ($selectedDate) {
                $q->whereDate('checked_at', $selectedDate);
            })
            ->orderByDesc('checked_at');

        $logs = $query->paginate(20);

        if ($request->wantsJson()) {
            return response()->json([
                'data' => $logs->items(),
                'meta' => [
                    'current_page' => $logs->currentPage(),
                    'last_page' => $logs->lastPage(),
                    'total' => $logs->total(),
                    'per_page' => $logs->perPage()
                ]
            ]);
        }

        $projects = Project::orderBy('domain')->get();
        return \Inertia\Inertia::render('DailyLogCalendar', compact('logs', 'projects', 'projectId', 'selectedDate'));
    }

    public function addNote(Request $request, DailyLog $log)
    {
        $note = $request->input('note');
        $log->update(['actions_taken' => $note]);

        return response()->json([
            'success' => true,
            'message' => 'Note saved.',
            'log' => $log->refresh()
        ]);
    }
}
