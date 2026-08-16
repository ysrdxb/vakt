<div>
<x-page-header title="Incidents" subtitle="Track and manage all security events" icon="exclamation-triangle">
    <a href="{{ route('dashboard') }}" class="btn btn-ghost btn-sm">← Dashboard</a>
</x-page-header>

{{-- Filter Bar --}}
<div class="card mb-6" style="padding:16px 20px">
    <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
        <div style="flex:1;min-width:200px;position:relative">
            <svg style="position:absolute;left:10px;top:50%;transform:translateY(-50%);width:16px;height:16px;color:var(--color-text-dim)" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input wire:model.live="search" type="text" class="form-control" placeholder="Search incidents..." style="padding-left:34px" />
        </div>
        <select wire:model.live="filterSeverity" class="form-control" style="width:150px">
            <option value="">All Severities</option>
            <option value="p1">P1 Critical</option>
            <option value="p2">P2 High</option>
            <option value="p3">P3 Medium</option>
            <option value="p4">P4 Low</option>
        </select>
        <select wire:model.live="filterStatus" class="form-control" style="width:150px">
            <option value="">All Statuses</option>
            <option value="open">Open</option>
            <option value="investigating">Investigating</option>
            <option value="contained">Contained</option>
            <option value="resolved">Resolved</option>
            <option value="closed">Closed</option>
        </select>
        <select wire:model.live="filterProject" class="form-control" style="width:180px">
            <option value="">All Projects</option>
            @foreach($projects as $project)
            <option value="{{ $project->id }}">{{ $project->domain }}</option>
            @endforeach
        </select>
    </div>
</div>

{{-- Incidents Table --}}
<div wire:loading.class="opacity-50 pointer-events-none">
    @if($incidents->isEmpty())
    <x-empty-state
        icon="shield-check"
        title="No incidents found"
        message="No incidents match your filters. All systems may be operating normally."
    />
    @else
    <x-table :headers="['Severity', 'Title', 'Project', 'Detected', 'Status', 'Response SLA', 'Actions']">
        @foreach($incidents as $incident)
        <x-table-row class="{{ in_array($incident->severity, ['p1']) ? 'row-critical' : ($incident->severity === 'p2' ? 'row-warning' : '') }}">
            <td>
                <x-badge :type="$incident->severity">{{ $incident->severity_label }}</x-badge>
            </td>
            <td>
                <a href="{{ route('incidents.show', $incident) }}" style="font-weight:600;color:var(--color-text)">
                    {{ $incident->title }}
                </a>
                @if($incident->source === 'auto_detected')
                <span style="font-size:0.68rem;color:var(--color-muted);display:block;margin-top:2px">Auto-detected</span>
                @endif
            </td>
            <td>
                <span class="text-mono" style="font-size:0.82rem">{{ $incident->project->domain }}</span>
            </td>
            <td>
                <span class="text-mono text-sm text-muted">
                    {{ $incident->detected_at?->format('M d, H:i') }}
                </span>
            </td>
            <td>
                <x-badge :type="match($incident->status) {
                    'open' => 'danger',
                    'investigating' => 'warning',
                    'contained' => 'info',
                    'resolved', 'closed' => 'success',
                    default => 'muted'
                }">{{ str_replace('_', ' ', $incident->status) }}</x-badge>
            </td>
            <td>
                @if($incident->sla_respond_breached)
                <span class="sla-timer breach">BREACHED</span>
                @elseif($incident->responded_at)
                <span class="sla-timer ok">Responded</span>
                @else
                @php
                    $deadline = $incident->detected_at?->addMinutes($incident->sla_respond_minutes);
                    $remaining = $deadline ? now()->diffInMinutes($deadline, false) : 0;
                @endphp
                <span class="sla-timer {{ $remaining < 15 ? 'warning' : 'ok' }}">
                    {{ $remaining > 0 ? $remaining . 'm left' : 'BREACH' }}
                </span>
                @endif
            </td>
            <td>
                <div style="display:flex;gap:6px">
                    <a href="{{ route('incidents.show', $incident) }}" class="btn btn-ghost btn-sm">View</a>
                    @if($incident->status === 'open')
                    <button wire:click="quickStatusUpdate({{ $incident->id }}, 'investigating')" class="btn btn-warning btn-sm" wire:loading.attr="disabled">
                        Investigate
                    </button>
                    @endif
                </div>
            </td>
        </x-table-row>
        @endforeach
    </x-table>
    <div style="padding:16px 20px">
        {{ $incidents->links() }}
    </div>
    @endif
</div>
</div>
