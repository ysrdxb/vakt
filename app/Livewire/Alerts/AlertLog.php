<?php

namespace App\Livewire\Alerts;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AlertLog as AlertLogModel;

class AlertLog extends Component
{
    use WithPagination;

    public string $filterType = '';

    public function getLogsProperty()
    {
        return AlertLogModel::with(['project', 'incident'])
            ->when($this->filterType, function ($q) {
                $q->where('alert_type', $this->filterType);
            })
            ->orderByDesc('created_at')
            ->paginate(25);
    }

    public function render()
    {
        return view('livewire.alerts.alert-log', [
            'logs' => $this->logs
        ])->layout('layouts.app', ['title' => 'Alerts']);
    }
}
