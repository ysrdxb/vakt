<div>
    <x-page-header title="File Integrity Monitor" subtitle="Detect unauthorized file changes on same-server projects" icon="shield-check" />

    <div class="grid grid-4 mb-6">
        <x-stat-card label="Total Files" :value="number_format($stats['total'])" color="primary" />
        <x-stat-card label="Suspicious" :value="number_format($stats['suspicious'])" color="{{ $stats['suspicious'] > 0 ? 'danger' : 'success' }}" />
        <x-stat-card label="Changed" :value="number_format($stats['changed'])" color="{{ $stats['changed'] > 0 ? 'warning' : 'success' }}" />
        <x-stat-card label="Clean" :value="number_format($stats['clean'])" color="success" />
    </div>

    <div class="card mb-6" style="padding:16px 20px">
        <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;justify-content:space-between">
            <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
                <select wire:model.live="projectId" class="form-control" style="width:200px">
                    <option value="">Select a Project...</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->domain }}</option>
                    @endforeach
                </select>
                <select wire:model.live="filterStatus" class="form-control" style="width:150px">
                    <option value="">All Statuses</option>
                    <option value="clean">Clean</option>
                    <option value="changed">Changed</option>
                    <option value="new">New</option>
                    <option value="suspicious">Suspicious</option>
                    <option value="deleted">Deleted</option>
                </select>
            </div>
            
            <x-btn variant="primary" wire:click="initScan" :disabled="!$projectId">
                Run Integrity Scan
            </x-btn>
        </div>
    </div>

    <div wire:loading.class="opacity-50 pointer-events-none">
        @if(!$projectId && empty($filterStatus))
            <x-empty-state icon="shield-check" title="Select a project" message="Choose a same-server project above to view file integrity." />
        @elseif($snapshots->isEmpty())
            <x-empty-state icon="shield-check" title="No files tracked" message="Try taking a snapshot to initialize baseline monitoring." />
        @else
            <x-table :headers="['File Path', 'Project', 'Status', 'Size', 'Last Modified', 'Changed At', 'Actions']">
                @foreach($snapshots as $file)
                    <x-table-row class="{{ $file->status === 'suspicious' ? 'row-critical' : ($file->status === 'changed' ? 'row-warning' : '') }}">
                        <td>
                            <div class="text-mono" style="font-size:0.82rem;word-break:break-all">{{ $file->file_path }}</div>
                            @if($file->flagged_patterns)
                                <div style="display:flex;gap:4px;flex-wrap:wrap;margin-top:4px">
                                    @foreach($file->flagged_patterns as $pattern)
                                        <x-badge type="danger" style="font-size:0.6rem;padding:2px 6px">{{ $pattern }}</x-badge>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td>
                            <span class="text-mono" style="font-size:0.8rem">{{ $file->project->domain }}</span>
                        </td>
                        <td>
                            <x-badge :type="match($file->status) { 'suspicious'=>'danger', 'changed'=>'warning', 'new'=>'info', 'deleted'=>'muted', 'clean'=>'success', default=>'muted' }">{{ strtoupper($file->status) }}</x-badge>
                        </td>
                        <td>
                            <span class="text-mono text-muted text-sm">{{ number_format($file->file_size / 1024, 2) }} KB</span>
                        </td>
                        <td>
                            <span class="text-mono text-muted text-sm">{{ $file->last_modified->format('M d, H:i') }}</span>
                        </td>
                        <td>
                            <span class="text-mono text-muted text-sm">{{ $file->changed_at ? $file->changed_at->diffForHumans() : '-' }}</span>
                        </td>
                        <td>
                            @if(in_array($file->status, ['suspicious', 'changed', 'new']))
                                <button wire:click="approveChange({{ $file->id }})" class="btn btn-success btn-sm">Approve</button>
                            @else
                                <span class="text-muted text-sm">—</span>
                            @endif
                        </td>
                    </x-table-row>
                @endforeach
            </x-table>
            <div style="padding:16px 20px">
                {{ $snapshots->links() }}
            </div>
        @endif
    </div>
</div>
