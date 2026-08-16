<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Project;
use App\Models\Incident;
use App\Models\Improvement;
use App\Models\SqaReport;

class ClientDashboard extends Component
{
    public function getProjectsProperty()
    {
        return Project::where('active', true)->with(['incidents' => function($q) {
            $q->whereNotIn('status',['resolved','closed']);
        }])->get();
    }

    public function getPendingApprovalsProperty()
    {
        return Improvement::where('status','client_review')->with('project')->get();
    }

    public function getLatestReportsProperty()
    {
        return SqaReport::where('status','sent')->with('project')->orderByDesc('created_at')->limit(3)->get();
    }

    public function approve(Improvement $improvement)
    {
        $improvement->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now()
        ]);
        $this->dispatch('toast', ['type' => 'success', 'title' => 'Approved', 'message' => 'Improvement approved.']);
    }

    public function decline(Improvement $improvement, string $reason)
    {
        $improvement->update([
            'status'         => 'declined',
            'decline_reason' => $reason
        ]);
        $this->dispatch('toast', ['type' => 'info', 'title' => 'Declined', 'message' => 'Improvement declined.']);
    }

    public function render()
    {
        return view('livewire.dashboard.client-dashboard', [
            'projects'         => $this->projects,
            'pendingApprovals' => $this->pendingApprovals,
            'latestReports'    => $this->latestReports
        ])->layout('layouts.client', ['title' => 'Dashboard']);
    }
}
