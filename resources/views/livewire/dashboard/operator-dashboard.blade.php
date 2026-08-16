@php
use App\Models\Incident;
use App\Models\Project;
@endphp

<div>
<x-page-header
    title="Dashboard"
    subtitle="System Overview & Monitoring"
    icon="shield"
/>

{{-- Stats Row --}}
<div class="grid grid-4 mb-6">
    <x-stat-card
        label="Projects Monitored"
        :value="$projects->count()"
        color="primary"
    />
    <x-stat-card
        label="Open Incidents"
        :value="$openIncidents->count()"
        :trend="$p1Count > 0 ? '+' . $p1Count . ' P1' : null"
        color="{{ $openIncidents->count() > 0 ? 'danger' : 'success' }}"
    />
    <x-stat-card
        label="Security Score"
        :value="$overallScore . '/100'"
        color="{{ $scoreColor }}"
    />
    <x-stat-card
        label="Last Check"
        :value="$recentChecks->first() ? $recentChecks->first()->checked_at->diffForHumans() : 'Never'"
        color="muted"
    />
</div>

<div class="grid grid-3 gap-6 mb-6">

    {{-- ===== PULSE RING / SECURITY SCORE ===== --}}
    <div class="card" style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:40px;text-align:center;">
        <div class="pulse-ring-container" style="margin-bottom:20px">
            <div class="pulse-ring {{ $scoreColor }}"></div>
            <div class="pulse-ring {{ $scoreColor }}"></div>
            <div class="pulse-ring {{ $scoreColor }}"></div>
            <div class="score-ring {{ $scoreColor }}">
                <span class="score-number">{{ $overallScore }}</span>
                <span class="score-label">Security Score</span>
            </div>
        </div>
        <div style="font-family:var(--font-display);font-size:1rem;font-weight:700;color:var(--color-text)">
            @if($overallScore >= 80) All Systems Nominal
            @elseif($overallScore >= 60) Attention Required
            @else Active Threats Detected
            @endif
        </div>
        <div style="font-size:0.8rem;color:var(--color-muted);margin-top:6px">
            {{ $projects->count() }} projects monitored
        </div>
        @if($p1Count > 0)
        <div class="badge danger" style="margin-top:12px;font-size:0.78rem;padding:6px 14px;border-radius:999px;animation:blink 1.2s ease-in-out infinite">
            ⚠ {{ $p1Count }} P1 CRITICAL ACTIVE
        </div>
        @endif
    </div>

    {{-- ===== LIVE INCIDENT FEED ===== --}}
    <div class="card" style="grid-column: span 2">
        <div class="card-header">
            <div class="card-title">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                Live Incident Feed
            </div>
            <div style="display:flex;align-items:center;gap:8px">
                <span style="width:7px;height:7px;background:var(--color-success);border-radius:50%;box-shadow:0 0 6px var(--color-success);display:inline-block;animation:blink 2s ease-in-out infinite"></span>
                <span style="font-size:0.72rem;color:var(--color-muted)">Live</span>
                <a href="{{ route('incidents.index') }}" class="btn btn-ghost btn-sm">View All</a>
            </div>
        </div>
        <div class="card-body" style="padding:0">
            @forelse($openIncidents as $incident)
            <a href="{{ route('incidents.show', $incident) }}" style="text-decoration:none;color:inherit">
                <div style="display:flex;align-items:center;gap:14px;padding:14px 20px;border-bottom:1px solid var(--color-border);transition:background .15s;"
                     onmouseover="this.style.background='var(--color-surface-2)'"
                     onmouseout="this.style.background='transparent'">
                    <x-badge :type="$incident->severity">{{ $incident->severity_label }}</x-badge>
                    <div style="flex:1;min-width:0">
                        <div style="font-size:0.875rem;font-weight:600;color:var(--color-text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $incident->title }}</div>
                        <div style="font-size:0.75rem;color:var(--color-muted)">{{ $incident->project->domain }} · {{ $incident->detected_at?->diffForHumans() }}</div>
                    </div>
                    <x-badge type="muted">{{ str_replace('_', ' ', $incident->status) }}</x-badge>
                    @if($incident->sla_respond_breached)
                    <span class="sla-timer breach">SLA BREACH</span>
                    @endif
                </div>
            </a>
            @empty
            <div style="padding:40px;text-align:center;color:var(--color-success)">
                <div style="font-size:2rem;margin-bottom:8px">✓</div>
                <div style="font-family:var(--font-display);font-weight:600">No open incidents</div>
                <div style="font-size:0.8rem;color:var(--color-muted);margin-top:4px">All systems operational</div>
            </div>
            @endforelse
        </div>
    </div>
</div>

<div class="grid grid-2 gap-6 mb-6">

    {{-- ===== PROJECT HEALTH GRID ===== --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                </svg>
                Project Health
            </div>
            <a href="{{ route('projects.index') }}" class="btn btn-ghost btn-sm">All Projects</a>
        </div>
        <div class="card-body" style="padding:0">
            @forelse($projects->take(8) as $project)
            <a href="{{ route('projects.show', $project) }}" style="text-decoration:none;color:inherit">
                <div style="display:flex;align-items:center;gap:12px;padding:12px 20px;border-bottom:1px solid var(--color-border);transition:background .15s"
                     onmouseover="this.style.background='var(--color-surface-2)'"
                     onmouseout="this.style.background='transparent'">
                    <span class="project-status-indicator {{ $project->status }}"></span>
                    <span style="font-family:var(--font-mono);font-size:0.85rem;color:var(--color-text);flex:1">{{ $project->domain }}</span>
                    @if($project->open_incidents_count > 0)
                    <span class="badge danger">{{ $project->open_incidents_count }} incidents</span>
                    @else
                    <span class="badge success">Clean</span>
                    @endif
                    <span style="font-size:0.78rem;color:var(--color-muted)">{{ $project->security_score }}/100</span>
                </div>
            </a>
            @empty
            <x-empty-state icon="folder" title="No projects" message="Add your first project to start monitoring." />
            @endforelse
        </div>
    </div>

    {{-- ===== ERROR TREND CHART ===== --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Error Trend (7 days)
            </div>
        </div>
        <div class="card-body">
            <div id="errorTrendChart"></div>
        </div>
    </div>
</div>

{{-- Recent Monitoring Activity --}}
<div class="card mb-6">
    <div class="card-header">
        <div class="card-title">Recent Monitoring Activity</div>
    </div>
    <div class="card-body" style="padding:0">
        @if($recentChecks->isEmpty())
        <x-empty-state icon="chart" title="No checks yet" message="Monitoring will begin automatically once projects are configured." />
        @else
        <x-table :headers="['Project', 'Status', 'Checked', 'Lines Scanned', 'Errors', 'Warnings']">
            @foreach($recentChecks as $check)
            <x-table-row>
                <td><span class="text-mono" style="font-size:0.82rem">{{ $check->project->domain }}</span></td>
                <td><x-badge :type="$check->status === 'ok' ? 'success' : ($check->status === 'warning' ? 'warning' : 'danger')">{{ $check->status }}</x-badge></td>
                <td><span class="text-mono text-sm text-muted">{{ $check->checked_at->diffForHumans() }}</span></td>
                <td>{{ number_format($check->log_lines_scanned) }}</td>
                <td><span style="color:{{ $check->errors_found > 0 ? 'var(--color-danger)' : 'var(--color-muted)' }}">{{ $check->errors_found }}</span></td>
                <td><span style="color:{{ $check->warnings_found > 0 ? 'var(--color-warning)' : 'var(--color-muted)' }}">{{ $check->warnings_found }}</span></td>
            </x-table-row>
            @endforeach
        </x-table>
        @endif
    </div>
</div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chartData = @json($chartData);

    if (document.getElementById('errorTrendChart')) {
        new ApexCharts(document.getElementById('errorTrendChart'), {
            chart: {
                type: 'area',
                height: 200,
                background: 'transparent',
                toolbar: { show: false },
                animations: { enabled: true, speed: 600 },
            },
            theme: { mode: 'dark' },
            colors: ['#ff4757'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0,
                    stops: [0, 100],
                }
            },
            series: [{ name: 'Errors', data: chartData.series }],
            xaxis: {
                categories: chartData.categories,
                labels: { style: { colors: '#94a3b8', fontFamily: 'Inter' } },
            },
            yaxis: {
                labels: { style: { colors: '#8899aa' } },
                min: 0,
            },
            grid: { borderColor: '#1f2d45', strokeDashArray: 4 },
            stroke: { width: 2, curve: 'smooth' },
            tooltip: { theme: 'dark' },
            dataLabels: { enabled: false },
        }).render();
    }
});
</script>
@endpush
