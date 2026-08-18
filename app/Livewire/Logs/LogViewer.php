<?php

namespace App\Livewire\Logs;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Project;
use App\Models\LogEntry;

class LogViewer extends Component
{
    use WithPagination;

    public ?int $projectId = null;
    public string $filterLevel = '';
    public string $search = '';
    public int $perPage = 50;

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

    public function getEntriesProperty()
    {
        return LogEntry::with('project')
            ->when($this->projectId, function ($q) {
                $q->where('project_id', $this->projectId);
            })
            ->when($this->filterLevel, function ($q) {
                $q->where('level', $this->filterLevel);
            })
            ->when($this->search, function ($q) {
                $q->where('message', 'like', '%' . $this->search . '%');
            })
            ->orderByDesc('occurred_at')
            ->paginate($this->perPage);
    }

    public ?int $expandedLog = null;

    public function toggleExpand(int $logId)
    {
        if ($this->expandedLog === $logId) {
            $this->expandedLog = null;
        } else {
            $this->expandedLog = $logId;
        }
    }

    public function markReviewed(LogEntry $entry)
    {
        $entry->update([
            'is_reviewed' => true,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now()
        ]);
        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Marked',
            'message' => 'Entry marked as reviewed'
        ]);
    }

    public function analyzeWithAI(LogEntry $entry)
    {
        if ($entry->ai_explanation && !str_starts_with($entry->ai_explanation, 'System Error') && !str_starts_with($entry->ai_explanation, 'AI Analysis Failed')) {
            return; // Already analyzed successfully
        }

        if (empty(env('GEMINI_API_KEY'))) {
            $entry->update(['ai_explanation' => "System Error: GEMINI_API_KEY is missing. Please configure it in the .env file."]);
            return;
        }

        try {
            $prompt = "You are an elite Security Operations Center (SOC) AI analyst. Your job is to explain server errors in simple terms and provide a quick fix. \n\nExplain this application log message in simple English and provide a quick fix hint or solution. Be concise. \n\nLog Level: {$entry->level}\nMessage: {$entry->message}";
            
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . env('GEMINI_API_KEY'), [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);

            if ($response->failed()) {
                throw new \Exception('Gemini API Error: ' . $response->body());
            }

            $explanation = $response->json('candidates.0.content.parts.0.text');

            if (!$explanation) {
                throw new \Exception('Unexpected Gemini API response format: ' . $response->body());
            }

            $entry->update(['ai_explanation' => $explanation]);

            $this->dispatch('toast', [
                'type' => 'success',
                'title' => 'Analysis Complete',
                'message' => 'AI explanation generated successfully.'
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Log AI Analysis failed: " . $e->getMessage());
            $entry->update(['ai_explanation' => "AI Analysis Failed: " . $e->getMessage()]);
        }
    }

    public function render()
    {
        return view('livewire.logs.log-viewer', [
            'projects' => $this->projects,
            'entries' => $this->entries
        ])->layout('layouts.app', ['title' => 'Log Viewer']);
    }
}
