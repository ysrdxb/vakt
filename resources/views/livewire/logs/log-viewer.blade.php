<div wire:poll.30000ms>
    <x-page-header title="Log Viewer" subtitle="Real-time log monitoring across all projects" icon="document-text" />

    <div class="card mb-6" style="padding:16px 20px">
        <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
            <div style="flex:1;min-width:200px;position:relative">
                <svg style="position:absolute;left:10px;top:50%;transform:translateY(-50%);width:16px;height:16px;color:var(--color-text-dim)" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input wire:model.live="search" type="text" class="form-control" placeholder="Search logs..." style="padding-left:34px" />
            </div>
            <select wire:model.live="projectId" class="form-control" style="width:180px">
                <option value="">All Projects</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}">{{ $project->domain }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterLevel" class="form-control" style="width:150px">
                <option value="">All Levels</option>
                <option value="debug">Debug</option>
                <option value="info">Info</option>
                <option value="warning">Warning</option>
                <option value="error">Error</option>
                <option value="critical">Critical</option>
            </select>
            
            <div style="display:flex;align-items:center;gap:6px;font-size:0.75rem;color:var(--color-success);margin-left:auto;padding-left:12px">
                <div style="width:8px;height:8px;border-radius:50%;background:currentColor;animation:pulse 2s infinite"></div>
                Live — refreshes every 30s
            </div>
        </div>
    </div>

    <div wire:loading.class="opacity-50 pointer-events-none">
        @if($entries->isEmpty())
            <x-empty-state icon="document-text" title="No logs found" message="No logs match your filters." />
        @else
            <x-table :headers="['Timestamp', 'Level', 'Project', 'Message', 'Patterns', 'IP', 'Actions']">
                @foreach($entries as $entry)
                    <x-table-row class="{{ in_array($entry->level, ['critical', 'error']) ? 'row-critical' : ($entry->level === 'warning' ? 'row-warning' : '') }}">
                        <td>
                            <span class="text-mono" style="font-size:0.82rem">{{ $entry->occurred_at->format('M d, H:i:s') }}</span>
                        </td>
                        <td>
                            <x-badge :type="match($entry->level) { 'critical','error'=>'danger', 'warning'=>'warning', 'info'=>'info', default=>'muted' }">{{ strtoupper($entry->level) }}</x-badge>
                        </td>
                        <td>
                            <a href="{{ route('projects.show', $entry->project) }}" class="text-mono" style="color:var(--color-primary);text-decoration:none">{{ $entry->project->domain }}</a>
                        </td>
                        <td>
                            <div style="font-family:var(--font-mono);font-size:0.75rem;color:var(--color-text-dim);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:350px" title="{{ $entry->message }}">
                                {{ $entry->message }}
                            </div>
                        </td>
                        <td>
                            @if($entry->detected_patterns)
                                <div style="display:flex;gap:4px;flex-wrap:wrap">
                                    @foreach($entry->detected_patterns as $pattern)
                                        <x-badge type="danger" style="font-size:0.6rem;padding:2px 6px">{{ $pattern }}</x-badge>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-muted text-sm">—</span>
                            @endif
                        </td>
                        <td>
                            @if($entry->ip_address)
                                <span class="text-mono text-sm">{{ $entry->ip_address }}</span>
                            @else
                                <span class="text-muted text-sm">—</span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;gap:4px">
                                @if(!$entry->is_reviewed)
                                    <button wire:click="markReviewed({{ $entry->id }})" class="btn btn-ghost btn-sm" title="Mark as reviewed">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    </button>
                                @else
                                    <span class="text-muted text-sm">Reviewed</span>
                                @endif
                                <button wire:click="toggleExpand({{ $entry->id }})" class="btn btn-ghost btn-sm" title="View Details">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                </button>
                            </div>
                        </td>
                    </x-table-row>
                    
                    @if($expandedLog === $entry->id)
                        <tr style="background:var(--color-surface-2);">
                            <td colspan="7" style="padding:16px 20px;">
                                <div style="display:flex;gap:24px;flex-wrap:wrap">
                                    <div style="flex:1;min-width:300px;">
                                        <div style="font-size:0.75rem;text-transform:uppercase;color:var(--color-muted);margin-bottom:8px">Full Message</div>
                                        <div style="background:var(--color-background);padding:12px;border-radius:6px;font-family:var(--font-mono);font-size:0.85rem;color:var(--color-text);white-space:pre-wrap;max-height:300px;overflow-y:auto;border:1px solid var(--color-border)">
                                            {{ $entry->message }}
                                        </div>
                                    </div>
                                    <div style="flex:1;min-width:300px;">
                                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                                            <div style="font-size:0.75rem;text-transform:uppercase;color:var(--color-muted);">AI Diagnostics</div>
                                            @if(!$entry->ai_explanation)
                                                <button wire:click="analyzeWithAI({{ $entry->id }})" class="btn btn-primary btn-sm" wire:loading.attr="disabled">
                                                    <span wire:loading.remove wire:target="analyzeWithAI({{ $entry->id }})">Ask AI for Quick Fix</span>
                                                    <span wire:loading wire:target="analyzeWithAI({{ $entry->id }})">Analyzing...</span>
                                                </button>
                                            @endif
                                        </div>
                                        
                                        @if($entry->ai_explanation)
                                            <div style="background:rgba(139, 92, 246, 0.05);padding:16px;border-radius:6px;border:1px solid rgba(139, 92, 246, 0.2);color:var(--color-text);">
                                                <div style="display:flex;gap:8px;margin-bottom:12px;color:#a78bfa">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:20px;height:20px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                                    <strong style="font-size:0.9rem">AI Quick Fix Hint</strong>
                                                </div>
                                                <div style="font-size:0.9rem;line-height:1.6;white-space:pre-wrap">{{ $entry->ai_explanation }}</div>
                                            </div>
                                        @elseif(!$entry->ai_explanation)
                                            <div style="padding:24px;border:1px dashed var(--color-border);border-radius:6px;text-align:center;color:var(--color-text-dim);font-size:0.9rem">
                                                Not analyzed yet. Click the button to generate a human-readable explanation and solution hint.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </x-table>
            <div style="padding:16px 20px">
                {{ $entries->links() }}
            </div>
        @endif
    </div>
</div>
