<?php

namespace App\Livewire\DailyLogs;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Project;
use App\Models\DailyLog;

class DailyLogCalendar extends Component
{
    use WithPagination;

    public ?int $projectId = null;
    public string $selectedDate = '';

    public function getProjectsProperty()
    {
        return Project::orderBy('domain')->get();
    }

    public function getRecentLogsProperty()
    {
        return DailyLog::with('project')
            ->when($this->projectId, function ($q) {
                $q->where('project_id', $this->projectId);
            })
            ->when($this->selectedDate, function ($q) {
                $q->whereDate('checked_at', $this->selectedDate);
            })
            ->orderByDesc('checked_at')
            ->paginate(20);
    }

    public function addNote(DailyLog $log, string $note)
    {
        $log->update(['actions_taken' => $note]);
        $this->dispatch('toast', ['type' => 'success', 'title' => 'Saved', 'message' => 'Note saved.']);
    }

    public function render()
    {
        return view('livewire.daily-logs.daily-log-calendar', [
            'projects' => $this->projects,
            'logs' => $this->recentLogs
        ])->layout('layouts.app', ['title' => 'Daily Logs']);
    }
}
