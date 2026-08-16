<?php

namespace App\Livewire\Incidents;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Incident;
use App\Models\Project;

class IncidentList extends Component
{
    use WithPagination;

    public string $search      = '';
    public string $filterSeverity = '';
    public string $filterStatus   = '';
    public string $filterProject  = '';
    public string $sortBy         = 'severity';

    public function getIncidentsProperty()
    {
        return Incident::with('project')
            ->when($this->search, fn($q) => $q->where('title', 'like', '%' . $this->search . '%'))
            ->when($this->filterSeverity, fn($q) => $q->where('severity', $this->filterSeverity))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterProject, fn($q) => $q->where('project_id', $this->filterProject))
            ->orderByRaw("FIELD(severity, 'p1','p2','p3','p4')")
            ->orderByDesc('detected_at')
            ->paginate(25);
    }

    public function getProjectsProperty()
    {
        return Project::orderBy('domain')->get();
    }

    public function quickStatusUpdate(Incident $incident, string $status): void
    {
        $incident->update(['status' => $status]);

        if ($status === 'investigating' && !$incident->responded_at) {
            $incident->update(['responded_at' => now()]);
        }

        if ($status === 'resolved') {
            $incident->update(['resolved_at' => now()]);
        }

        \App\Models\IncidentTimeline::create([
            'incident_id'  => $incident->id,
            'action'       => 'Status changed to ' . $status,
            'performed_by' => auth()->user()->name,
            'performed_at' => now(),
        ]);

        $this->dispatch('toast', ['type' => 'success', 'title' => 'Updated', 'message' => 'Incident status updated.']);
    }

    public function render()
    {
        return view('livewire.incidents.incident-list', [
            'incidents' => $this->incidents,
            'projects'  => $this->projects,
        ])->layout('layouts.app', ['title' => 'Incidents']);
    }
}
