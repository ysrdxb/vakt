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
        if ($entry->ai_explanation) {
            return; // Already analyzed
        }

        if (empty(config('openai.api_key'))) {
            $entry->update(['ai_explanation' => "System Error: OpenAI API Key is missing on this server. Please configure OPENAI_API_KEY in the .env file."]);
            return;
        }

        try {
            $prompt = "Explain this application log message in simple English and provide a quick fix hint or solution. Be concise. \n\nLog Level: {$entry->level}\nMessage: {$entry->message}";
            
            $response = \OpenAI\Laravel\Facades\OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are an elite Security Operations Center (SOC) AI analyst. Your job is to explain server errors in simple terms and provide a quick fix.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            $explanation = $response->choices[0]->message->content;

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
