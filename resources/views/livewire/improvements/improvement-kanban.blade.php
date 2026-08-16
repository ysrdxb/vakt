<div>
    <x-page-header title="Improvement Pipeline" subtitle="Track proposed improvements through approval and implementation" icon="chart-bar" />

    <div class="card mb-6" style="padding:16px 20px">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px">
            <select wire:model.live="projectId" class="form-control" style="width:250px">
                <option value="">All Projects</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}">{{ $project->domain }}</option>
                @endforeach
            </select>
            
            <x-btn variant="primary" wire:click="$toggle('showForm')" >
                {{ $showForm ? 'Cancel' : 'Add Improvement' }}
            </x-btn>
        </div>
    </div>

    @if($showForm)
        <div class="card mb-6">
            <div class="card-header">
                <div class="card-title">Propose Improvement</div>
            </div>
            <form wire:submit="addImprovement">
                <div class="card-body">
                    <div class="grid grid-2 gap-6 mb-4">
                        <div class="form-group">
                            <label class="form-label">Project</label>
                            <select wire:model.live="projectId" class="form-control" required>
                                <option value="">-- Select Project --</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->domain }}</option>
                                @endforeach
                            </select>
                        </div>
                        <x-input name="title" label="Title" wire:model="title" required />
                    </div>
                    
                    <div class="mb-4">
                        <x-select name="category" label="Category" wire:model="category" :options="['security'=>'Security', 'performance'=>'Performance', 'ux'=>'UX/UI', 'feature'=>'New Feature', 'technical_debt'=>'Technical Debt', 'compliance'=>'Compliance']" required />
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Description</label>
                        <textarea wire:model="description" class="form-control" rows="3" placeholder="What needs to be improved and why?"></textarea>
                    </div>
                    
                    <div class="grid grid-2 gap-6">
                        <x-select name="priority" label="Priority" wire:model="priority" :options="['high'=>'High', 'medium'=>'Medium', 'low'=>'Low']" required />
                        <x-select name="effort" label="Estimated Effort" wire:model="effort" :options="['high'=>'High (Days)', 'medium'=>'Medium (Hours)', 'low'=>'Low (Minutes)']" required />
                    </div>
                </div>
                <div class="card-footer" style="display:flex;justify-content:flex-end;gap:8px">
                    <x-btn variant="ghost" type="button" wire:click="$toggle('showForm')" >Cancel</x-btn>
                    <x-btn variant="primary" type="submit" :disabled="!$projectId">Submit Proposal</x-btn>
                </div>
            </form>
        </div>
    @endif

    <div style="display:flex;gap:16px;overflow-x:auto;padding-bottom:16px;min-height:600px;width:100%">
        @foreach($columns as $status => $items)
            <div style="flex:0 0 320px;background:var(--color-surface-2);border-radius:12px;padding:16px;display:flex;flex-direction:column;gap:12px">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                    <div style="font-weight:600;color:var(--color-text)">
                        {{ match($status) { 'proposed'=>'Proposed', 'client_review'=>'Client Review', 'approved'=>'Approved', 'in_progress'=>'In Progress', 'done'=>'Done', 'declined'=>'Declined', default=>ucfirst($status) } }}
                    </div>
                    <span style="background:var(--color-surface);padding:2px 8px;border-radius:12px;font-size:0.75rem;color:var(--color-text-dim)">{{ $items->count() }}</span>
                </div>

                @forelse($items as $item)
                    <div class="card p-4" style="background:var(--color-surface);box-shadow:0 4px 6px rgba(0,0,0,0.2)">
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px">
                            <x-badge :type="match($item->priority) { 'high'=>'danger', 'medium'=>'warning', 'low'=>'info', default=>'muted' }" style="font-size:0.6rem">{{ ucfirst($item->priority) }}</x-badge>
                            <x-badge type="muted" style="font-size:0.6rem">{{ ucfirst($item->effort) }} Effort</x-badge>
                        </div>
                        
                        <div style="font-weight:600;margin-bottom:4px;color:var(--color-text)">{{ $item->title }}</div>
                        <a href="{{ route('projects.show', $item->project) }}" class="text-mono" style="font-size:0.75rem;color:var(--color-primary);text-decoration:none">{{ $item->project->domain }}</a>
                        
                        <div style="margin-top:12px;display:flex;gap:4px;flex-wrap:wrap">
                            @if($status === 'proposed')
                                <button wire:click="moveCard({{ $item->id }}, 'client_review')" class="btn btn-ghost btn-sm" style="flex:1">Send to Client</button>
                                <button wire:click="moveCard({{ $item->id }}, 'approved')" class="btn btn-ghost btn-sm" style="flex:1">Auto-Approve</button>
                            @elseif($status === 'client_review')
                                <button wire:click="moveCard({{ $item->id }}, 'proposed')" class="btn btn-ghost btn-sm" style="flex:1">Revoke</button>
                            @elseif($status === 'approved')
                                <button wire:click="moveCard({{ $item->id }}, 'in_progress')" class="btn btn-primary btn-sm" style="flex:1">Start Work</button>
                            @elseif($status === 'in_progress')
                                <button wire:click="moveCard({{ $item->id }}, 'done')" class="btn btn-success btn-sm" style="flex:1">Complete</button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div style="border:2px dashed var(--color-border);border-radius:8px;padding:24px;text-align:center;color:var(--color-muted);font-size:0.875rem">
                        No items
                    </div>
                @endforelse
            </div>
        @endforeach
    </div>
</div>
