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
                            @if(!$entry->is_reviewed)
                                <button wire:click="markReviewed({{ $entry->id }})" class="btn btn-ghost btn-sm" title="Mark as reviewed">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                </button>
                            @else
                                <span class="text-muted text-sm">Reviewed</span>
                            @endif
                        </td>
                    </x-table-row>
                @endforeach
            </x-table>
            <div style="padding:16px 20px">
                {{ $entries->links() }}
            </div>
        @endif
    </div>
</div>
