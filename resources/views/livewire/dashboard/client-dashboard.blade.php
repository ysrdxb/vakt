<div>
    <x-page-header title="Security Dashboard" subtitle="Executive overview of your security posture" icon="shield" />

    @php
        $openIncidents = $projects->sum(fn($p) => $p->incidents->count());
        $avgScore = $projects->count() > 0 ? (int)round($projects->avg('security_score')) : 0;
        $scoreColor = $avgScore >= 80 ? 'primary' : ($avgScore >= 60 ? 'warning' : 'danger');
    @endphp

    <div class="grid grid-4 mb-6">
        <x-stat-card label="Projects Monitored" :value="$projects->count()" color="primary" />
        <x-stat-card label="Open Incidents" :value="$openIncidents" color="{{ $openIncidents > 0 ? 'danger' : 'success' }}" />
        <x-stat-card label="Avg Security Score" :value="$avgScore . '/100'" color="{{ $scoreColor }}" />
        <x-stat-card label="Last Check" :value="$projects->max('last_checked_at')?->diffForHumans() ?? 'Never'" color="muted" />
    </div>

    <div class="card mb-6">
        <div class="card-header">
            <div class="card-title">Project Health</div>
        </div>
        <div class="card-body">
            @if($projects->isEmpty())
                <x-empty-state icon="folder" title="No projects yet" message="Your projects will appear here once monitoring is setup." />
            @else
                <div class="grid grid-auto">
                    @foreach($projects as $project)
                        <div class="project-card">
                            <div class="project-card-header">
                                <div class="project-domain">{{ $project->domain }}</div>
                                <span class="project-status-indicator {{ $project->status }}"></span>
                            </div>
                            <div class="project-meta">
                                <div class="project-meta-item">Score: <strong style="color:var(--color-{{ $project->security_score >= 80 ? 'primary' : ($project->security_score >= 60 ? 'warning' : 'danger') }})">{{ $project->security_score }}</strong></div>
                                <div class="project-meta-item">Incidents: <strong>{{ $project->incidents->count() }}</strong></div>
                            </div>
                            <div style="font-size:0.75rem;color:var(--color-muted);margin-top:auto">Checked: {{ $project->last_checked_at?->diffForHumans() ?? 'Never' }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="grid grid-2 gap-6">
        <div class="card">
            <div class="card-header">
                <div class="card-title">Pending Your Approval</div>
            </div>
            <div class="card-body">
                @forelse($pendingApprovals as $item)
                    <div style="padding:16px;border:1px solid var(--color-border);border-radius:var(--radius-card);margin-bottom:12px;background:var(--color-surface-2)">
                        <div style="display:flex;justify-content:space-between;align-items:flex-start">
                            <div>
                                <h4 style="margin-bottom:4px;color:var(--color-text)">{{ $item->title }}</h4>
                                <div style="font-size:0.8rem;color:var(--color-muted);font-family:var(--font-mono);margin-bottom:8px">{{ $item->project->domain }}</div>
                                <p style="font-size:0.85rem;color:var(--color-text-dim)">{{ $item->description }}</p>
                            </div>
                            <x-badge :type="match($item->priority) { 'high' => 'danger', 'medium' => 'warning', default => 'primary' }">{{ ucfirst($item->priority) }} Priority</x-badge>
                        </div>
                        <div style="margin-top:16px;display:flex;gap:8px" x-data="{ declineReason: '' }">
                            <button wire:click="approve({{ $item->id }})" class="btn btn-success btn-sm">Approve</button>
                            <div x-data="{ open: false }" style="position:relative">
                                <button @click="open = !open" class="btn btn-ghost btn-sm">Decline</button>
                                <div x-show="open" @click.away="open = false" style="position:absolute;top:100%;left:0;margin-top:8px;background:var(--color-surface);border:1px solid var(--color-border);padding:12px;border-radius:8px;width:250px;z-index:10;box-shadow:0 10px 25px rgba(0,0,0,0.5)">
                                    <textarea x-model="declineReason" class="form-control mb-4" rows="2" placeholder="Reason for declining..."></textarea>
                                    <button @click="$wire.decline({{ $item->id }}, declineReason); open = false" class="btn btn-danger btn-sm" style="width:100%">Confirm Decline</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <x-empty-state icon="document" title="All caught up" message="No pending approvals." />
                @endforelse
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title">Latest SQA Reports</div>
            </div>
            <div class="card-body">
                @forelse($latestReports as $report)
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid var(--color-border)">
                        <div>
                            <div style="font-weight:600">{{ $report->title }}</div>
                            <div style="font-size:0.75rem;color:var(--color-muted);margin-top:2px">{{ $report->project->domain }} · {{ \Carbon\Carbon::parse($report->period_month)->format('F Y') }}</div>
                        </div>
                        <a href="{{ route('client.reports', ['download' => $report->id]) }}" class="btn btn-primary btn-sm">Download PDF</a>
                    </div>
                @empty
                    <x-empty-state icon="document" title="No reports yet" message="Monthly SQA reports will appear here." />
                @endforelse
                @if($latestReports->count() > 0)
                    <div style="margin-top:16px;text-align:center">
                        <a href="{{ route('client.reports') }}" class="btn btn-ghost btn-sm">View All Reports</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
