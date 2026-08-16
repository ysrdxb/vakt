<div>
    <x-page-header title="Security Audit" subtitle="OWASP-based security checklist and compliance tracking" icon="document-text" />

    <div class="card mb-6" style="padding:16px 20px">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px">
            <div style="display:flex;gap:12px;align-items:center">
                <select wire:model.live="projectId" class="form-control" style="width:250px">
                    <option value="">Select a Project...</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->domain }}</option>
                    @endforeach
                </select>
                
                @if($projectId)
                    <x-btn variant="ghost" wire:click="seedAuditItems"  onclick="return confirm('This will load the default checklist items if they don\'t exist. Continue?')">
                        Load Default Checklist
                    </x-btn>
                @endif
            </div>
            
            <x-btn variant="ghost"  disabled>Export PDF (Coming Soon)</x-btn>
        </div>
    </div>

    @if(!$projectId)
        <x-empty-state icon="document-text" title="Select a project" message="Choose a project to view or manage its security audit checklist." />
    @elseif(empty($itemsByCategory))
        <x-empty-state icon="document-text" title="Checklist empty" message="Click 'Load Default Checklist' to populate the OWASP security items for this project." />
    @else
        <div class="grid grid-2 gap-6 mb-6" style="align-items:start">
            <div class="card" style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:32px">
                <div style="font-size:0.875rem;text-transform:uppercase;letter-spacing:.1em;color:var(--color-muted);margin-bottom:16px">Compliance Score</div>
                
                @php $scoreColor = $score >= 80 ? 'success' : ($score >= 60 ? 'warning' : 'danger'); @endphp
                <div style="position:relative;width:160px;height:160px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:conic-gradient(var(--color-{{ $scoreColor }}) {{ $score }}%, var(--color-surface-2) 0)">
                    <div style="position:absolute;width:140px;height:140px;border-radius:50%;background:var(--color-surface);display:flex;flex-direction:column;align-items:center;justify-content:center">
                        <div style="font-family:var(--font-display);font-size:2.5rem;font-weight:700;color:var(--color-{{ $scoreColor }})">{{ $score }}</div>
                        <div style="font-size:0.875rem;color:var(--color-muted)">/ 100</div>
                    </div>
                </div>
            </div>
            
            <div>
                <div class="alert alert-info mb-4">
                    <strong>How it works:</strong> Pass items to increase score. Failed critical items subtract 20 points, high subtract 10, medium 5, and low 2.
                </div>
                
                <div class="grid grid-2 gap-4">
                    <div class="card p-4">
                        <div class="text-muted text-sm uppercase mb-1">Passed Items</div>
                        @php $passCount = collect($itemsByCategory)->flatten(1)->where('status', 'pass')->count(); @endphp
                        <div class="font-display text-2xl font-bold text-success">{{ $passCount }}</div>
                    </div>
                    <div class="card p-4">
                        <div class="text-muted text-sm uppercase mb-1">Failed Items</div>
                        @php $failCount = collect($itemsByCategory)->flatten(1)->where('status', 'fail')->count(); @endphp
                        <div class="font-display text-2xl font-bold {{ $failCount > 0 ? 'text-danger' : 'text-success' }}">{{ $failCount }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:24px">
            @foreach($itemsByCategory as $category => $items)
                <div class="card">
                    <div class="card-header" style="background:var(--color-surface-2)">
                        <div class="card-title">{{ $category }}</div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th style="width:35%">Audit Item</th>
                                    <th>Severity</th>
                                    <th style="width:15%">Status</th>
                                    <th style="width:30%">Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $item)
                                    <tr style="background:var(--color-{{ match($item['status']) { 'fail'=>'danger', 'pass'=>'success', 'partial'=>'warning', default=>'surface' } }}-alpha-10)">
                                        <td style="font-weight:500">{{ $item['item_name'] }}</td>
                                        <td>
                                            <x-badge :type="match($item['severity']) { 'critical'=>'danger', 'high'=>'warning', 'medium'=>'info', default=>'muted' }">{{ ucfirst($item['severity']) }}</x-badge>
                                        </td>
                                        <td>
                                            <select wire:change="updateStatus({{ $item['id'] }}, $event.target.value)" class="form-control form-control-sm" style="min-width:120px">
                                                <option value="unchecked" {{ $item['status'] === 'unchecked' ? 'selected' : '' }}>Unchecked</option>
                                                <option value="pass" {{ $item['status'] === 'pass' ? 'selected' : '' }}>Pass</option>
                                                <option value="fail" {{ $item['status'] === 'fail' ? 'selected' : '' }}>Fail</option>
                                                <option value="partial" {{ $item['status'] === 'partial' ? 'selected' : '' }}>Partial</option>
                                                <option value="na" {{ $item['status'] === 'na' ? 'selected' : '' }}>N/A</option>
                                            </select>
                                        </td>
                                        <td>
                                            <div x-data="{ notes: '{{ addslashes($item['notes'] ?? '') }}' }" style="display:flex;gap:4px">
                                                <input x-model="notes" @blur="$wire.updateNotes({{ $item['id'] }}, notes)" type="text" class="form-control form-control-sm" placeholder="Add notes..." />
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
