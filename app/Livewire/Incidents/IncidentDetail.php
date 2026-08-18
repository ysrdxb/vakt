<?php

namespace App\Livewire\Incidents;

use Livewire\Component;
use App\Models\Incident;
use App\Models\IncidentTimeline;

class IncidentDetail extends Component
{
    public Incident $incident;
    public string $notes = '';
    public string $rootCause = '';
    public string $resolutionNotes = '';
    public string $preventionNotes = '';
    public bool $editMode = false;

    public function mount(Incident $incident): void
    {
        $this->incident       = $incident;
        $this->notes          = $incident->description ?? '';
        $this->rootCause      = $incident->root_cause ?? '';
        $this->resolutionNotes = $incident->resolution_notes ?? '';
        $this->preventionNotes = $incident->prevention_notes ?? '';
    }

    public function transitionStatus(string $status): void
    {
        $updates = ['status' => $status];

        if ($status === 'investigating' && !$this->incident->responded_at) {
            $updates['responded_at'] = now();
        }
        if ($status === 'contained' && !$this->incident->responded_at) {
            $updates['responded_at'] = now();
        }
        if ($status === 'resolved') {
            $updates['resolved_at'] = now();
        }
        if ($status === 'closed') {
            $updates['closed_at'] = now();
        }

        $this->incident->update($updates);

        IncidentTimeline::create([
            'incident_id'  => $this->incident->id,
            'action'       => 'Status changed to ' . ucfirst($status),
            'performed_by' => auth()->user()->name,
            'performed_at' => now(),
        ]);

        $this->incident->refresh();
        $this->dispatch('toast', ['type' => 'success', 'title' => 'Updated', 'message' => "Incident marked as {$status}."]);
    }

    public function saveNotes(): void
    {
        $this->incident->update([
            'description'      => $this->notes,
            'root_cause'       => $this->rootCause,
            'resolution_notes' => $this->resolutionNotes,
            'prevention_notes' => $this->preventionNotes,
        ]);

        IncidentTimeline::create([
            'incident_id'  => $this->incident->id,
            'action'       => 'Notes updated',
            'performed_by' => auth()->user()->name,
            'performed_at' => now(),
        ]);

        $this->editMode = false;
        $this->dispatch('toast', ['type' => 'success', 'title' => 'Saved', 'message' => 'Incident notes saved.']);
    }

    public function generatePdf()
    {
        return redirect()->route('incidents.show', $this->incident);
    }

    public function executeAgentCommand(string $commandName, string $ip = null): void
    {
        $service = app(\App\Services\AgentCommandService::class);
        $result = ['status' => 'error', 'message' => 'Invalid command'];

        if ($commandName === 'block_ip' && $ip) {
            $result = $service->blockIp($this->incident->project, $ip);
        } elseif ($commandName === 'fix_permissions') {
            $result = $service->fixPermissions($this->incident->project);
        } elseif ($commandName === 'clear_cache') {
            $result = $service->clearCache($this->incident->project);
        }

        $type = $result['status'] === 'success' ? 'success' : 'error';
        $this->dispatch('toast', ['type' => $type, 'title' => 'Command Executed', 'message' => $result['message']]);

        IncidentTimeline::create([
            'incident_id'  => $this->incident->id,
            'action'       => "Executed remote command: {$commandName}",
            'description'  => "Result: " . $result['message'],
            'performed_by' => auth()->user()->name,
            'performed_at' => now(),
        ]);
        
        $this->incident->refresh();
    }

    public function render()
    {
        $this->incident->load(['project', 'timeline']);
        return view('livewire.incidents.incident-detail')
            ->layout('layouts.app', ['title' => 'Incident: ' . $this->incident->title]);
    }
}
