<div>
    <x-page-header title="Daily Monitoring Logs" subtitle="Formal audit trail of daily SOC monitoring and health checks" icon="document-text" />

    <div class="card mb-6" style="padding:16px 20px">
        <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
            <select wire:model.live="projectId" class="form-control" style="width:250px">
                <option value="">All Projects</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}">{{ $project->domain }}</option>
                @endforeach
            </select>
            
            <input wire:model.live="selectedDate" type="date" class="form-control" style="width:180px" />
        </div>
    </div>

    <div wire:loading.class="opacity-50 pointer-events-none">
        @if($logs->isEmpty())
            <x-empty-state icon="document-text" title="No daily logs found" message="No monitoring logs match your filters." />
        @else
            <x-table :headers="['Date', 'Project', 'Status', 'Summary', 'Source', 'Findings', 'Actions Taken']">
                @foreach($logs as $log)
                    <x-table-row class="{{ $log->status === 'critical' ? 'row-critical' : ($log->status === 'warning' ? 'row-warning' : '') }}">
                        <td>
                            <span class="text-mono" style="font-size:0.82rem">{{ $log->checked_at->format('M d, Y') }}</span>
                        </td>
                        <td>
                            <a href="{{ route('projects.show', $log->project) }}" class="text-mono" style="color:var(--color-primary);text-decoration:none">{{ $log->project->domain }}</a>
                        </td>
                        <td>
                            <x-badge :type="match($log->status) { 'critical'=>'danger', 'warning'=>'warning', 'ok'=>'success', default=>'muted' }">{{ strtoupper($log->status) }}</x-badge>
                        </td>
                        <td>
                            <div style="font-size:0.85rem;color:var(--color-text);max-width:250px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="{{ $log->summary }}">
                                {{ $log->summary }}
                            </div>
                        </td>
                        <td>
                            <x-badge type="muted" style="font-size:0.65rem">{{ $log->auto_generated ? 'Auto' : 'Manual' }}</x-badge>
                        </td>
                        <td>
                            <div style="font-family:var(--font-mono);font-size:0.75rem">
                                <div>Checks: {{ $log->findings['checks_run'] ?? 0 }}</div>
                                <div class="{{ ($log->findings['errors_found'] ?? 0) > 0 ? 'text-danger' : 'text-success' }}">Errors: {{ $log->findings['errors_found'] ?? 0 }}</div>
                                <div class="{{ ($log->findings['incidents'] ?? 0) > 0 ? 'text-warning' : 'text-success' }}">Incidents: {{ $log->findings['incidents'] ?? 0 }}</div>
                            </div>
                        </td>
                        <td>
                            <div x-data="{ editing: false, note: '{{ addslashes($log->actions_taken ?? '') }}' }" style="min-width:200px">
                                <div x-show="!editing" style="display:flex;justify-content:space-between;align-items:center;gap:8px">
                                    <div style="font-size:0.8rem;color:var(--color-text-dim);flex:1">
                                        {{ $log->actions_taken ?: 'No notes.' }}
                                    </div>
                                    <button @click="editing = true" class="btn btn-ghost btn-sm" title="Edit Note">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                </div>
                                <div x-show="editing" style="display:flex;gap:4px">
                                    <textarea x-model="note" class="form-control form-control-sm" rows="2" style="flex:1"></textarea>
                                    <div style="display:flex;flex-direction:column;gap:4px">
                                        <button @click="$wire.addNote({{ $log->id }}, note); editing = false" class="btn btn-primary btn-sm" style="padding:2px 8px">Save</button>
                                        <button @click="editing = false" class="btn btn-ghost btn-sm" style="padding:2px 8px">X</button>
                                    </div>
                                </div>
                            </div>
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
