<div>
    <div class="breadcrumbs">
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span class="sep">›</span>
        <a href="{{ route('projects.index') }}">Projects</a>
        <span class="sep">›</span>
        <span class="current">{{ $project->domain }}</span>
    </div>

    <div class="card mb-6" style="border-top:4px solid var(--color-{{ $project->security_score >= 80 ? 'primary' : ($project->security_score >= 60 ? 'warning' : 'danger') }})">
        <div class="card-body">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px">
                <div>
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px">
                        <h1 style="margin:0;font-size:1.5rem">{{ $project->name }}</h1>
                        <x-badge type="muted">{{ $project->stack }}</x-badge>
                        <span class="project-status-indicator {{ $project->active ? $project->status : 'unknown' }}" title="Status"></span>
                    </div>
                    <div style="font-family:var(--font-mono);color:var(--color-primary);margin-bottom:12px">{{ $project->domain }}</div>
                    <div style="color:var(--color-text-dim);font-size:0.875rem">{{ $project->description ?: 'No description provided.' }}</div>
                </div>
                
                <div style="display:flex;gap:12px">
                    <x-btn variant="primary" wire:click="runScan" :disabled="$project->server_type !== 'same_server'">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px;margin-right:6px">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Run Scan Now
                    </x-btn>
                    <a href="{{ route('projects.edit', $project) }}" class="btn btn-ghost">Edit Settings</a>
                </div>
            </div>
        </div>
        <div class="card-footer" style="background:var(--color-surface-2);display:flex;gap:32px;flex-wrap:wrap">
            <div>
                <div style="font-size:0.7rem;text-transform:uppercase;color:var(--color-muted);margin-bottom:4px">Security Score</div>
                <div style="font-family:var(--font-display);font-size:1.4rem;font-weight:700;color:var(--color-{{ $project->security_score >= 80 ? 'primary' : ($project->security_score >= 60 ? 'warning' : 'danger') }})">{{ $project->security_score }}</div>
            </div>
            <div>
                <div style="font-size:0.7rem;text-transform:uppercase;color:var(--color-muted);margin-bottom:4px">Open Incidents</div>
                <div style="font-family:var(--font-display);font-size:1.4rem;font-weight:700;color:var(--color-{{ $project->incidents->whereNotIn('status',['resolved','closed'])->count() > 0 ? 'danger' : 'success' }})">{{ $project->incidents->whereNotIn('status',['resolved','closed'])->count() }}</div>
            </div>
            <div>
                <div style="font-size:0.7rem;text-transform:uppercase;color:var(--color-muted);margin-bottom:4px">Server Type</div>
                <div style="font-size:1rem;font-weight:500">{{ str_replace('_', ' ', $project->server_type) }}</div>
            </div>
            <div>
                <div style="font-size:0.7rem;text-transform:uppercase;color:var(--color-muted);margin-bottom:4px">Last Checked</div>
                <div style="font-size:1rem;font-weight:500">{{ $project->last_checked_at ? $project->last_checked_at->diffForHumans() : 'Never' }}</div>
            </div>
        </div>
    </div>

    @if($project->server_type === 'external_agent' && !$project->last_checked_at)
    <div class="alert alert-info mb-6">
        <strong>Agent Setup Required:</strong> This project uses external agent monitoring. Download the <a href="{{ route('projects.agent-download', $project) }}" class="text-primary" style="text-decoration:underline">Agent Script Template</a> and configure a cron job to run it every 5 minutes. (e.g. <code>*/5 * * * * php /path/to/soc-agent.php</code>)
    </div>
    @endif

    <div class="grid grid-2 gap-6">
        {{-- Recent Incidents --}}
        <div class="card">
            <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
                <div class="card-title">Recent Incidents</div>
                <a href="{{ route('incidents.index', ['project' => $project->id]) }}" class="btn btn-ghost btn-sm">View All</a>
            </div>
            <div class="card-body p-0">
                @if($project->incidents->isEmpty())
                    <div style="padding:24px"><x-empty-state icon="shield-check" title="No incidents" message="Clean record." /></div>
                @else
                    <table class="table">
                        <tbody>
                            @foreach($project->incidents as $incident)
                                <tr>
                                    <td style="width:1%"><x-badge :type="$incident->severity">{{ $incident->severity_label }}</x-badge></td>
                                    <td>
                                        <a href="{{ route('incidents.show', $incident) }}" style="color:var(--color-text);text-decoration:none">{{ $incident->title }}</a>
                                        <div style="font-size:0.75rem;color:var(--color-muted)">{{ $incident->detected_at->diffForHumans() }}</div>
                                    </td>
                                    <td class="text-right">
                                        <x-badge :type="match($incident->status) { 'open'=>'danger', 'investigating'=>'warning', 'contained'=>'info', 'resolved','closed'=>'success', default=>'muted' }">
                                            {{ str_replace('_',' ',$incident->status) }}
                                        </x-badge>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        <div>
            {{-- Monitoring History --}}
            <div class="card mb-6">
                <div class="card-header">
                    <div class="card-title">Recent Health Checks</div>
                </div>
                <div class="card-body p-0">
                    @if($project->monitoringChecks->isEmpty())
                        <div style="padding:24px"><x-empty-state icon="clock" title="No checks yet" message="Monitoring history will appear here." /></div>
                    @else
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Status</th>
                                    <th>Scanned</th>
                                    <th>Errors</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($project->monitoringChecks as $check)
                                    <tr>
                                        <td class="text-mono text-sm text-muted">{{ $check->checked_at->format('M d, H:i:s') }}</td>
                                        <td>
                                            <span class="project-status-indicator {{ $check->status }}"></span>
                                            {{ ucfirst($check->status) }}
                                        </td>
                                        <td class="text-mono">{{ number_format($check->log_lines_scanned) }}</td>
                                        <td class="{{ $check->errors_found > 0 ? 'text-danger font-bold' : '' }}">{{ $check->errors_found }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
            
            {{-- Latest Logs --}}
            <div class="card">
                <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
                    <div class="card-title">Latest Logs</div>
                    <a href="{{ route('logs.index', ['project' => $project->id]) }}" class="btn btn-ghost btn-sm">View Full Log</a>
                </div>
                <div class="card-body p-0">
                    @if($project->logEntries->isEmpty())
                        <div style="padding:24px"><x-empty-state icon="document-text" title="No logs captured" /></div>
                    @else
                        <table class="table">
                            <tbody>
                                @foreach($project->logEntries as $log)
                                    <tr>
                                        <td style="width:1%"><x-badge :type="$log->level === 'error' || $log->level === 'critical' ? 'danger' : ($log->level === 'warning' ? 'warning' : 'info')">{{ strtoupper($log->level) }}</x-badge></td>
                                        <td>
                                            <div style="font-family:var(--font-mono);font-size:0.75rem;color:var(--color-text-dim);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:250px" title="{{ $log->message }}">
                                                {{ $log->message }}
                                            </div>
                                        </td>
                                        <td class="text-right text-mono text-sm text-muted">{{ $log->occurred_at->format('H:i:s') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
