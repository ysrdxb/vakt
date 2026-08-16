<div>
    <x-page-header title="SQA Reports" subtitle="Monthly Security Quality Assurance reports for clients" icon="document" />

    <div class="card mb-6" style="padding:16px 20px">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px">
            <div style="display:flex;gap:12px;align-items:center">
                <select wire:model.live="projectId" class="form-control" style="width:250px">
                    <option value="">All Projects</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->domain }}</option>
                    @endforeach
                </select>
            </div>
            
            <button wire:click="generateReport" class="btn btn-primary" {{ !$projectId ? 'disabled title="Select a project first"' : '' }}>
                Generate Monthly Report
            </button>
        </div>
    </div>

    <div wire:loading.class="opacity-50 pointer-events-none">
        @if($reports->isEmpty())
            <x-empty-state icon="document" title="No reports generated" message="Select a project and click Generate to create the first monthly report." />
        @else
            <x-table :headers="['Report Title', 'Project', 'Period', 'Score', 'Incidents', 'Status', 'Generated', 'Actions']">
                @foreach($reports as $report)
                    <x-table-row>
                        <td>
                            <div style="font-weight:600;color:var(--color-text)">{{ $report->title }}</div>
                        </td>
                        <td>
                            <a href="{{ route('projects.show', $report->project) }}" class="text-mono" style="color:var(--color-primary);text-decoration:none">{{ $report->project->domain }}</a>
                        </td>
                        <td>
                            <span class="text-mono" style="font-size:0.85rem">{{ \Carbon\Carbon::parse($report->period_month)->format('M Y') }}</span>
                        </td>
                        <td>
                            <div style="font-family:var(--font-display);font-size:1.1rem;font-weight:700;color:var(--color-{{ $report->security_score >= 80 ? 'primary' : ($report->security_score >= 60 ? 'warning' : 'danger') }})">
                                {{ $report->security_score }}
                            </div>
                        </td>
                        <td>
                            <div style="font-size:0.8rem">
                                <span style="color:var(--color-danger)">{{ $report->incidents_summary['total'] ?? 0 }} Total</span> · 
                                <span style="color:var(--color-success)">{{ $report->incidents_summary['resolved'] ?? 0 }} Resolved</span>
                            </div>
                        </td>
                        <td>
                            <x-badge :type="match($report->status) { 'draft'=>'warning', 'sent'=>'success', 'archived'=>'muted', default=>'muted' }">{{ ucfirst($report->status) }}</x-badge>
                        </td>
                        <td>
                            <span class="text-mono text-muted text-sm">{{ $report->created_at->format('M d, Y') }}</span>
                        </td>
                        <td>
                            <div style="display:flex;gap:6px">
                                <a href="{{ route('reports.show', $report) }}" class="btn btn-ghost btn-sm">Preview</a>
                                @if($report->status === 'draft')
                                    <button wire:click="markSent({{ $report->id }})" class="btn btn-primary btn-sm">Mark Sent</button>
                                @else
                                    <a href="{{ route('reports.show', ['report' => $report, 'download' => 1]) }}" class="btn btn-ghost btn-sm">PDF</a>
                                @endif
                            </div>
                        </td>
                    </x-table-row>
                @endforeach
            </x-table>
            <div style="padding:16px 20px">
                {{ $reports->links() }}
            </div>
        @endif
    </div>
</div>
