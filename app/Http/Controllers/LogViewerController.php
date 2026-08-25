<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LogEntry;
use App\Models\Project;
use Illuminate\Support\Facades\Http;

class LogViewerController extends Controller
{
    public function index(Request $request, ?Project $project = null)
    {
        $projectId = $request->input('project_id', $request->input('project', $project?->id));
        $filterLevel = $request->input('filterLevel');
        $search = $request->input('search');
        $perPage = $request->input('perPage', 50);

        $query = LogEntry::with('project')
            ->when($projectId, function ($q) use ($projectId) {
                $q->where('project_id', $projectId);
            })
            ->when($filterLevel, function ($q) use ($filterLevel) {
                $q->where('level', $filterLevel);
            })
            ->when($search, function ($q) use ($search) {
                $q->where('message', 'like', '%' . $search . '%');
            })
            ->orderByDesc('occurred_at');

        $entries = $query->paginate($perPage);

        if ($request->wantsJson()) {
            return response()->json([
                'data' => $entries->items(),
                'meta' => [
                    'current_page' => $entries->currentPage(),
                    'last_page' => $entries->lastPage(),
                    'total' => $entries->total(),
                    'per_page' => $entries->perPage()
                ]
            ]);
        }

        $projects = Project::orderBy('domain')->get();
        return \Inertia\Inertia::render('LogViewer', [
            'initialEntries' => $entries->items(),
            'meta' => [
                'current_page' => $entries->currentPage(),
                'last_page' => $entries->lastPage(),
                'total' => $entries->total(),
                'per_page' => $entries->perPage()
            ],
            'projects' => $projects,
            'initialProjectId' => $projectId,
            'csrf' => csrf_token(),
            'endpoints' => [
                'index' => route('logs.index')
            ]
        ]);
    }

    public function markReviewed(Request $request, LogEntry $entry)
    {
        $entry->update([
            'is_reviewed' => true,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Entry marked as reviewed',
            'entry' => $entry->refresh()
        ]);
    }

    public function analyzeWithAI(Request $request, LogEntry $entry)
    {
        // Clear any previously saved error strings
        if ($entry->ai_explanation && (str_starts_with($entry->ai_explanation, 'System Error') || str_starts_with($entry->ai_explanation, 'AI Analysis Failed'))) {
            $entry->update(['ai_explanation' => null]);
            $entry->refresh();
        }

        if ($entry->ai_explanation) {
            return response()->json(['success' => true, 'entry' => $entry]);
        }

        if (empty(env('GEMINI_API_KEY'))) {
            return response()->json(['success' => false, 'message' => "System Error: GEMINI_API_KEY is missing. Please configure it in the .env file."], 500);
        }

        try {
            $prompt = "You are an elite Security Operations Center (SOC) AI analyst. Analyze the following log message. Provide a concise, professional explanation of what the error means, its potential impact, and a recommended quick fix. Do not use labels like '(Simple English)' in your response. Structure it cleanly.\n\nLog Level: {$entry->level}\nMessage: {$entry->message}";
            
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-3.6-flash:generateContent?key=' . env('GEMINI_API_KEY'), [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $explanation = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                
                if ($explanation) {
                    $entry->update(['ai_explanation' => trim($explanation)]);
                    return response()->json(['success' => true, 'entry' => $entry->refresh()]);
                }
            }
            
            return response()->json(['success' => false, 'message' => 'AI Analysis Failed: Invalid response from Gemini API'], 500);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'AI Analysis Failed: ' . $e->getMessage()], 500);
        }
    }
}
