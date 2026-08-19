<div>
{{-- Breadcrumb --}}
<div class="breadcrumbs">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="sep">›</span>
    <a href="{{ route('incidents.index') }}">Incidents</a>
    <span class="sep">›</span>
    <span class="current">{{ $incident->title }}</span>
</div>

{{-- Incident Header --}}
<div class="card mb-6" style="border-left:4px solid var(--color-{{ $incident->severity_color }})">
    <div class="card-body">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap">
            <div style="flex:1">
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px">
                    <x-badge :type="$incident->severity">{{ $incident->severity_label }}</x-badge>
                    <x-badge :type="match($incident->status) {
                        'open' => 'danger',
                        'investigating' => 'warning',
                        'contained' => 'info',
                        'resolved','closed' => 'success',
                        default => 'muted'
                    }">{{ str_replace('_',' ',$incident->status) }}</x-badge>
                    @if($incident->source === 'auto_detected')
                    <x-badge type="muted">Auto-detected</x-badge>
                    @endif
                </div>
                <h1 style="font-size:1.4rem;margin-bottom:8px">{{ $incident->title }}</h1>
                <div style="display:flex;gap:20px;flex-wrap:wrap">
                    <div style="font-size:0.78rem;color:var(--color-muted)">
                        <strong>Project:</strong>
                        <a href="{{ route('projects.show', $incident->project) }}" class="text-mono" style="color:var(--color-primary)">{{ $incident->project->domain }}</a>
                    </div>
                    <div style="font-size:0.78rem;color:var(--color-muted)">
                        <strong>Detected:</strong> <span class="text-mono">{{ $incident->detected_at?->format('M d, Y H:i:s') }}</span>
                    </div>
                    @if($incident->responded_at)
                    <div style="font-size:0.78rem;color:var(--color-muted)">
                        <strong>Responded:</strong> <span class="text-mono">{{ $incident->responded_at->format('M d, Y H:i:s') }}</span>
                    </div>
                    @endif
                    @if($incident->resolved_at)
                    <div style="font-size:0.78rem;color:var(--color-muted)">
                        <strong>Resolved:</strong> <span class="text-mono">{{ $incident->resolved_at->format('M d, Y H:i:s') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- SLA Box --}}
            <div style="background:var(--color-surface-2);border:1px solid var(--color-border);border-radius:8px;padding:14px 20px;min-width:180px;text-align:center">
                <div style="font-size:0.7rem;text-transform:uppercase;letter-spacing:.08em;color:var(--color-muted);margin-bottom:6px">Response SLA</div>
                @if($incident->sla_respond_breached)
                <span class="sla-timer breach">⚠ BREACHED</span>
                @elseif($incident->responded_at)
                <span class="sla-timer ok">✓ Responded</span>
                @else
                @php
                    $deadline = $incident->detected_at?->addMinutes($incident->sla_respond_minutes);
                    $remaining = $deadline ? now()->diffInMinutes($deadline, false) : 0;
                @endphp
                <span class="sla-timer {{ $remaining < 15 ? 'warning' : 'ok' }}">
                    {{ $remaining > 0 ? $remaining . 'm left' : 'BREACH' }}
                </span>
                @endif
                <div style="font-size:0.68rem;color:var(--color-text-dim);margin-top:4px">Target: {{ $incident->sla_respond_minutes }}min</div>
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="card-footer" style="display:flex;gap:8px;flex-wrap:wrap">
        @if($incident->status === 'open')
        <x-btn variant="warning" wire:click="transitionStatus('investigating')" >Start Investigation</x-btn>
        @endif
        @if($incident->status === 'investigating')
        <x-btn variant="primary" wire:click="transitionStatus('contained')" >Mark Contained</x-btn>
        @endif
        @if(in_array($incident->status, ['investigating','contained']))
        <x-btn variant="success" wire:click="transitionStatus('resolved')" >Mark Resolved</x-btn>
        @endif
        @if($incident->status === 'resolved')
        <x-btn variant="ghost" wire:click="transitionStatus('closed')" >Close Incident</x-btn>
        @endif
        <x-btn variant="ghost" wire:click="$set('editMode', true)" >Edit Notes</x-btn>
    </div>
    </div>

@if($incident->ai_summary || $incident->ai_diagnosis)
<div class="card mb-6" style="border: 1px solid rgba(139, 92, 246, 0.3); background: linear-gradient(145deg, rgba(17, 24, 39, 1) 0%, rgba(30, 27, 75, 0.4) 100%); position: relative; overflow: hidden;">
    <div style="position: absolute; top: -10px; right: -10px; opacity: 0.05;">
        <svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
    </div>
    <div class="card-header" style="border-bottom: 1px solid rgba(139, 92, 246, 0.1);">
        <div class="card-title" style="display: flex; align-items: center; gap: 8px; color: #a78bfa;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 18px; height: 18px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            AI Executive Summary
        </div>
    </div>
    <div class="card-body">
        @if($incident->ai_summary)
        <div style="margin-bottom: 20px;">
            <p style="font-size: 1.05rem; line-height: 1.6; color: #e2e8f0;">{{ $incident->ai_summary }}</p>
        </div>
        @endif
        
        @if($incident->ai_diagnosis)
        <div style="background: rgba(0, 0, 0, 0.2); border-radius: 6px; padding: 16px; border-left: 3px solid #8b5cf6;">
            <div style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: .06em; color: #a78bfa; margin-bottom: 8px;">Technical Diagnosis</div>
            <div style="font-size: 0.875rem; color: #cbd5e1; line-height: 1.5; white-space: pre-wrap;">{{ $incident->ai_diagnosis }}</div>
        </div>
        @endif
    </div>

    @if($incident->project->server_type === 'external_agent')
    <div class="card-footer" style="background: rgba(139, 92, 246, 0.05); border-top: 1px solid rgba(139, 92, 246, 0.1); display: flex; gap: 8px; flex-wrap: wrap;" x-data>
        <div style="font-size: 0.75rem; color: #a78bfa; width: 100%; margin-bottom: 4px;"><strong>Auto-Remediation:</strong> Push commands directly to the agent.</div>
        <button class="btn btn-sm" style="background: rgba(220, 38, 38, 0.2); color: #fca5a5; border: 1px solid rgba(220, 38, 38, 0.4);"
            @click="let ip = prompt('Enter IP address to block:'); if(ip) $wire.executeAgentCommand('block_ip', ip)">
            Block IP in Firewall
        </button>
        <button class="btn btn-sm" style="background: rgba(59, 130, 246, 0.2); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.4);"
            wire:click="executeAgentCommand('fix_permissions')">
            Fix Storage Permissions
        </button>
        <button class="btn btn-sm" style="background: rgba(16, 185, 129, 0.2); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.4);"
            wire:click="executeAgentCommand('clear_cache')">
            Clear App Cache
        </button>
    </div>
    @endif
</div>
@endif

<div class="grid grid-2 gap-6">

    {{-- Notes --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">Investigation Notes</div>
            @if($editMode)
            <div style="display:flex;gap:8px">
                <x-btn variant="primary" wire:click="saveNotes"  class="btn-sm">Save</x-btn>
                <x-btn variant="ghost" wire:click="$set('editMode', false)"  class="btn-sm">Cancel</x-btn>
            </div>
            @else
            <x-btn variant="ghost" wire:click="$set('editMode', true)"  class="btn-sm">Edit</x-btn>
            @endif
        </div>
        <div class="card-body">
            @if($editMode)
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea wire:model="notes" class="form-control" rows="4" placeholder="Incident description and impact..."></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Root Cause</label>
                <textarea wire:model="rootCause" class="form-control" rows="3" placeholder="Root cause analysis..."></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Resolution Notes</label>
                <textarea wire:model="resolutionNotes" class="form-control" rows="3" placeholder="Steps taken to resolve..."></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Prevention Notes</label>
                <textarea wire:model="preventionNotes" class="form-control" rows="3" placeholder="How to prevent recurrence..."></textarea>
            </div>
            @else
            @if($incident->description)
            <div style="margin-bottom:16px">
                <div style="font-size:0.72rem;text-transform:uppercase;letter-spacing:.06em;color:var(--color-muted);margin-bottom:6px">Description</div>
                <p style="font-size:0.875rem;color:var(--color-text)">{{ $incident->description }}</p>
            </div>
            @endif
            @if($incident->root_cause)
            <div style="margin-bottom:16px">
                <div style="font-size:0.72rem;text-transform:uppercase;letter-spacing:.06em;color:var(--color-muted);margin-bottom:6px">Root Cause</div>
                <p style="font-size:0.875rem;color:var(--color-text)">{{ $incident->root_cause }}</p>
            </div>
            @endif
            @if($incident->resolution_notes)
            <div style="margin-bottom:16px">
                <div style="font-size:0.72rem;text-transform:uppercase;letter-spacing:.06em;color:var(--color-muted);margin-bottom:6px">Resolution</div>
                <p style="font-size:0.875rem;color:var(--color-text)">{{ $incident->resolution_notes }}</p>
            </div>
            @endif
            @if(!$incident->description && !$incident->root_cause)
            <p class="text-muted text-sm">No notes yet. Click "Edit" to add investigation notes.</p>
            @endif
            @endif
        </div>
    </div>

    {{-- Timeline --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">Activity Timeline</div>
        </div>
        <div class="card-body">
            @if($incident->timeline->isEmpty())
            <x-empty-state icon="document" title="No timeline events" message="Actions will be logged here." />
            @else
            <div class="timeline">
                @foreach($incident->timeline as $event)
                <div class="timeline-item">
                    <div class="timeline-dot">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-action">{{ $event->action }}</div>
                        <div class="timeline-meta">{{ $event->performed_by }} · {{ $event->performed_at->format('M d, H:i:s') }}</div>
                        @if($event->description)
                        <div class="timeline-description">{{ $event->description }}</div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

</div>
</div>
