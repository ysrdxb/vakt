<?php

namespace App\Livewire\Improvements;

use Livewire\Component;
use App\Models\Project;
use App\Models\Improvement;

class ImprovementKanban extends Component
{
    public ?int $projectId = null;
    public bool $showForm = false;
    
    public string $title = '';
    public string $description = '';
    public string $category = 'feature';
    public string $priority = 'medium';
    public string $effort = 'medium';

    public function getProjectsProperty()
    {
        return Project::orderBy('domain')->get();
    }

    public function getColumnedItemsProperty()
    {
        $cols = ['proposed', 'client_review', 'approved', 'in_progress', 'done', 'declined'];
        $result = [];
        
        foreach ($cols as $col) {
            $result[$col] = Improvement::with('project')
                ->when($this->projectId, function ($q) {
                    $q->where('project_id', $this->projectId);
                })
                ->where('status', $col)
                ->orderByDesc('created_at')
                ->get();
        }
        
        return $result;
    }

    public function moveCard(Improvement $item, string $status)
    {
        $item->update(['status' => $status]);
        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Moved',
            'message' => 'Card moved to ' . str_replace('_', ' ', $status)
        ]);
    }

    public function addImprovement()
    {
        $this->validate([
            'title' => 'required',
            'projectId' => 'required'
        ]);

        Improvement::create([
            'project_id' => $this->projectId,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'priority' => $this->priority,
            'effort' => $this->effort,
            'status' => 'proposed',
            'proposed_by' => auth()->id()
        ]);

        $this->reset(['title', 'description', 'showForm']);
        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Added',
            'message' => 'Improvement added.'
        ]);
    }

    public function render()
    {
        return view('livewire.improvements.improvement-kanban', [
            'projects' => $this->projects,
            'columns' => $this->columnedItems
        ])->layout('layouts.app', ['title' => 'Improvements']);
    }
}
