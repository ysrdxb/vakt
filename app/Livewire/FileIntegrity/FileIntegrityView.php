<?php

namespace App\Livewire\FileIntegrity;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Project;
use App\Models\FileSnapshot;
use App\Services\FileIntegrityService;

class FileIntegrityView extends Component
{
    use WithPagination;

    public ?int $projectId = null;
    public string $filterStatus = '';
    public bool $scanLoading = false;

    public function mount(?Project $project = null)
    {
        if ($project && $project->exists) {
            $this->projectId = $project->id;
        }
    }

    public function getProjectsProperty()
    {
        return Project::where('server_type', 'same_server')->orderBy('domain')->get();
    }

    public function getSnapshotsProperty()
    {
        return FileSnapshot::with('project')
            ->when($this->projectId, function ($q) {
                $q->where('project_id', $this->projectId);
            })
            ->when($this->filterStatus, function ($q) {
                $q->where('status', $this->filterStatus);
            })
            ->orderByRaw("FIELD(status,'suspicious','changed','new','deleted','clean')")
            ->paginate(50);
    }

    public function getStatsProperty()
    {
        $q = FileSnapshot::when($this->projectId, function ($q) {
            $q->where('project_id', $this->projectId);
        });

        return [
            'total' => $q->count(),
            'suspicious' => (clone $q)->where('status', 'suspicious')->count(),
            'changed' => (clone $q)->where('status', 'changed')->count(),
            'clean' => (clone $q)->where('status', 'clean')->count(),
        ];
    }

    public function approveChange(FileSnapshot $snapshot)
    {
        $snapshot->update([
            'status' => 'clean',
            'changed_at' => null
        ]);
        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Approved',
            'message' => 'File change marked as approved.'
        ]);
    }

    public function initScan()
    {
        if (!$this->projectId) {
            $this->dispatch('toast', ['type' => 'error', 'title' => 'Error', 'message' => 'Select a project first.']);
            return;
        }

        $project = Project::find($this->projectId);
        app(FileIntegrityService::class)->takeSnapshot($project);

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Scanned',
            'message' => 'File snapshot taken.'
        ]);
    }

    public function render()
    {
        return view('livewire.file-integrity.file-integrity-view', [
            'projects' => $this->projects,
            'snapshots' => $this->snapshots,
            'stats' => $this->stats
        ])->layout('layouts.app', ['title' => 'File Integrity']);
    }
}
