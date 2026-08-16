<?php

namespace App\Livewire\Projects;

use Livewire\Component;
use App\Models\Project;

class ProjectList extends Component
{
    public string $search = '';
    public string $filterStatus = '';

    public function getProjectsProperty()
    {
        return Project::when($this->search, function ($q) {
                $q->where('domain', 'like', '%' . $this->search . '%')
                  ->orWhere('name', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterStatus, function ($q) {
                $q->where('status', $this->filterStatus);
            })
            ->orderBy('status')
            ->orderBy('domain')
            ->get();
    }

    public function toggleActive(Project $project)
    {
        $project->update(['active' => !$project->active]);
        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Updated',
            'message' => $project->domain . ' monitoring ' . ($project->active ? 'enabled' : 'disabled')
        ]);
    }

    public function delete(Project $project)
    {
        $project->delete();
        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Deleted',
            'message' => 'Project removed.'
        ]);
    }

    public function render()
    {
        return view('livewire.projects.project-list', [
            'projects' => $this->projects
        ])->layout('layouts.app', ['title' => 'Projects']);
    }
}
