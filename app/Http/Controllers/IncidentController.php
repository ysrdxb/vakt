<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Incident;
use App\Models\Project;
use App\Models\IncidentTimeline;
use App\Services\AgentCommandService;

class IncidentController extends Controller
{
    public function index(Request $request)
    {
        $query = Incident::with('project')
            ->when($request->search, fn($q) => $q->where('title', 'like', '%' . $request->search . '%'))
            ->when($request->filterSeverity, fn($q) => $q->where('severity', $request->filterSeverity))
            ->when($request->filterStatus, fn($q) => $q->where('status', $request->filterStatus))
            ->when($request->filterProject, fn($q) => $q->where('project_id', $request->filterProject))
            ->orderByRaw("FIELD(severity, 'p1','p2','p3','p4')")
            ->orderByDesc('detected_at');

        $incidents = $query->paginate(25);

        if ($request->wantsJson()) {
            return response()->json([
                'data' => $incidents->items(),
                'meta' => [
                    'current_page' => $incidents->currentPage(),
                    'last_page' => $incidents->lastPage(),
                    'total' => $incidents->total(),
                    'per_page' => $incidents->perPage()
                ]
            ]);
        }

        $projects = Project::orderBy('domain')->get();
        return Inertia::render('IncidentList', compact('incidents', 'projects'));
    }

    public function show(Incident $incident)
    {
        $incident->load(['project', 'timeline' => function($q) {
            $q->orderBy('performed_at', 'desc');
        }]);
        
        return Inertia::render('IncidentDetail', compact('incident'));
    }

    public function transitionStatus(Request $request, Incident $incident)
    {
        $status = $request->input('status');
        $updates = ['status' => $status];

        if (in_array($status, ['investigating', 'contained']) && !$incident->responded_at) {
            $updates['responded_at'] = now();
        }
        if ($status === 'resolved') {
            $updates['resolved_at'] = now();
        }
        if ($status === 'closed') {
            $updates['closed_at'] = now();
        }

        $incident->update($updates);

        $timeline = IncidentTimeline::create([
            'incident_id'  => $incident->id,
            'action'       => 'Status changed to ' . ucfirst($status),
            'performed_by' => auth()->user()->name,
            'performed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'incident' => $incident->refresh(),
            'timeline' => $timeline,
            'message' => "Incident marked as {$status}."
        ]);
    }

    public function saveNotes(Request $request, Incident $incident)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
            'rootCause' => 'nullable|string',
            'resolutionNotes' => 'nullable|string',
            'preventionNotes' => 'nullable|string',
        ]);

        $incident->update([
            'description'      => $validated['notes'] ?? '',
            'root_cause'       => $validated['rootCause'] ?? '',
            'resolution_notes' => $validated['resolutionNotes'] ?? '',
            'prevention_notes' => $validated['preventionNotes'] ?? '',
        ]);

        $timeline = IncidentTimeline::create([
            'incident_id'  => $incident->id,
            'action'       => 'Notes updated',
            'performed_by' => auth()->user()->name,
            'performed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'incident' => $incident->refresh(),
            'timeline' => $timeline,
            'message' => 'Incident notes saved.'
        ]);
    }

    public function executeCommand(Request $request, Incident $incident, AgentCommandService $service)
    {
        $commandName = $request->input('command');
        $ip = $request->input('ip');
        
        $result = ['status' => 'error', 'message' => 'Invalid command'];

        if ($commandName === 'block_ip' && $ip) {
            $result = $service->blockIp($incident->project, $ip);
        } elseif ($commandName === 'fix_permissions') {
            $result = $service->fixPermissions($incident->project);
        } elseif ($commandName === 'clear_cache') {
            $result = $service->clearCache($incident->project);
        }

        $timeline = IncidentTimeline::create([
            'incident_id'  => $incident->id,
            'action'       => "Executed remote command: {$commandName}",
            'description'  => "Result: " . $result['message'],
            'performed_by' => auth()->user()->name,
            'performed_at' => now(),
        ]);
        
        return response()->json([
            'status' => $result['status'],
            'message' => $result['message'],
            'timeline' => $timeline
        ]);
    }
}
