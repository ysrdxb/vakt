<div>
    <x-page-header title="Alert Log" subtitle="History of all automated alerts sent by the SOC" icon="bell" />

    <div class="card mb-6" style="padding:16px 20px">
        <div style="display:flex;gap:12px;align-items:center">
            <select wire:model.live="filterType" class="form-control" style="width:200px">
                <option value="">All Alert Types</option>
                <option value="incident_created">Incident Created</option>
                <option value="daily_digest">Daily Digest</option>
                <option value="weekly_summary">Weekly Summary</option>
                <option value="sla_breach">SLA Breach</option>
            </select>
        </div>
    </div>

    <div wire:loading.class="opacity-50 pointer-events-none">
        @if($logs->isEmpty())
            <x-empty-state icon="bell" title="No alerts sent" message="No alerts match your filter criteria." />
        @else
            <x-table :headers="['Type', 'Recipient', 'Subject', 'Project / Related', 'Status', 'Sent At']">
                @foreach($logs as $log)
                    <x-table-row>
                        <td>
                            <x-badge type="muted">{{ str_replace('_', ' ', $log->alert_type) }}</x-badge>
                        </td>
                        <td>
                            <span class="text-mono" style="font-size:0.8rem">{{ $log->recipient }}</span>
                        </td>
                        <td>
                            <div style="font-weight:500;color:var(--color-text);max-width:300px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="{{ $log->subject }}">
                                {{ $log->subject }}
                            </div>
                        </td>
                        <td>
                            @if($log->project)
                                <a href="{{ route('projects.show', $log->project) }}" class="text-mono" style="font-size:0.8rem;color:var(--color-primary);text-decoration:none">{{ $log->project->domain }}</a>
                            @endif
                            @if($log->incident)
                                <div style="font-size:0.75rem;color:var(--color-muted);margin-top:2px">
                                    Incident: <a href="{{ route('incidents.show', $log->incident) }}" style="color:inherit">#{{ $log->incident->id }}</a>
                                </div>
                            @endif
                        </td>
                        <td>
                            <x-badge :type="$log->status === 'sent' ? 'success' : 'danger'">{{ ucfirst($log->status) }}</x-badge>
                        </td>
                        <td>
                            <span class="text-mono text-muted text-sm">{{ $log->created_at->format('M d, H:i:s') }}</span>
                        </td>
                    </x-table-row>
                @endforeach
            </x-table>
            <div style="padding:16px 20px">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
