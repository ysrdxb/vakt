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
                    <a href="{{ route('projects.report.monthly', $project) }}" target="_blank" class="btn" style="background: rgba(139, 92, 246, 0.1); color: #a78bfa; border: 1px solid rgba(139, 92, 246, 0.4);">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px;margin-right:6px">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        SOC2 Report
                    </a>
                    <x-btn variant="primary" wire:click="runScan">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px;margin-right:6px">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Run Scan Now
                    </x-btn>
                    <x-btn variant="ghost" wire:click="sendTestReport">
                        Test Daily Report
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

    @if($project->server_type === 'external_agent' && !$project->firewall_whitelist_confirmed)
    <div class="alert alert-warning mb-6" style="background: rgba(245, 158, 11, 0.1); border: 1px solid var(--warning); color: #fcd34d;">
        <div style="width:100%">
            <div style="margin-bottom:8px">
                <strong>FIREWALL WHITELIST REQUIRED:</strong> To prevent this SOC server from being blocked, you MUST whitelist our IP (`{{ request()->ip() }}`) on the target server's firewall (e.g. CSF) BEFORE deploying the agent.
            </div>
            <div style="display: flex; gap: 12px; margin-top: 12px;">
                <button wire:click="confirmWhitelist" class="btn btn-primary btn-sm">I have whitelisted the IP</button>
            </div>
        </div>
    </div>
    @endif

    @if($project->server_type === 'external_agent' && !$project->last_checked_at && $project->firewall_whitelist_confirmed)
    <div class="alert alert-info mb-6">
        <div style="width:100%">
            <div style="margin-bottom:8px">
                <strong>Agent Setup Required:</strong> Download the <a href="{{ route('projects.agent-download', $project) }}" class="text-primary" style="text-decoration:underline">Agent Script Template</a>, upload it to the root of <b>{{ $project->domain }}</b>. Ensure it is accessible at the URL you provided.
            </div>
        </div>
    </div>
    @endif

    {{-- Uptime & System Metrics --}}
    @if($uptimeLogs->isNotEmpty() || ($latestReport && isset($latestReport->payload['system_metrics'])))
    <div class="card mb-6">
        <div class="card-header">
            <div class="card-title">System Health & Uptime</div>
        </div>
        <div class="card-body" style="display:flex; gap: 32px; flex-wrap: wrap;">
            
            <div style="flex: 1; min-width: 250px;">
                <div style="font-size:0.75rem; text-transform:uppercase; color:var(--color-muted); margin-bottom:8px;">Recent Uptime (Last Hour)</div>
                <div style="display: flex; gap: 4px; align-items: flex-end; height: 40px;">
                    @foreach($uptimeLogs->take(30)->reverse() as $log)
                        @php
                            $color = $log->status_code == 200 ? 'var(--color-success)' : 'var(--color-danger)';
                            $height = $log->status_code == 200 ? '100%' : '30%';
                        @endphp
                        <div style="flex: 1; background: {{ $color }}; height: {{ $height }}; border-radius: 2px;" title="{{ $log->created_at->format('H:i') }} - HTTP {{ $log->status_code ?? 'Error' }}"></div>
                    @endforeach
                </div>
                <div style="display: flex; justify-content: space-between; margin-top: 4px; font-size: 0.7rem; color: var(--color-muted);">
                    <span>30m ago</span>
                    <span>Now</span>
                </div>
            </div>

            @if($latestReport && isset($latestReport->payload['system_metrics']))
                @php
                    $metrics = $latestReport->payload['system_metrics'];
                    $diskFree = $metrics['disk_free_bytes'] ?? 0;
                    $diskTotal = $metrics['disk_total_bytes'] ?? 1;
                    $diskUsedPct = $diskTotal > 0 ? (($diskTotal - $diskFree) / $diskTotal) * 100 : 0;
                    
                    $memFree = $metrics['memory_free_mb'] ?? 0;
                    $memTotal = $metrics['memory_total_mb'] ?? 1;
                    $memUsedPct = $memTotal > 0 ? (($memTotal - $memFree) / $memTotal) * 100 : 0;
                @endphp
                
                <div style="flex: 1; min-width: 250px;">
                    <div style="font-size:0.75rem; text-transform:uppercase; color:var(--color-muted); margin-bottom:8px;">Server Resources</div>
                    
                    <div style="margin-bottom: 12px;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 4px;">
                            <span>Disk Usage</span>
                            <span>{{ number_format($diskUsedPct, 1) }}%</span>
                        </div>
                        <div style="height: 6px; background: var(--color-surface-2); border-radius: 3px; overflow: hidden;">
                            <div style="height: 100%; width: {{ min($diskUsedPct, 100) }}%; background: {{ $diskUsedPct > 90 ? 'var(--color-danger)' : 'var(--color-primary)' }};"></div>
                        </div>
                    </div>

                    @if(isset($metrics['memory_total_mb']))
                    <div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 4px;">
                            <span>Memory Usage</span>
                            <span>{{ number_format($memUsedPct, 1) }}% ({{ number_format($memTotal) }} MB)</span>
                        </div>
                        <div style="height: 6px; background: var(--color-surface-2); border-radius: 3px; overflow: hidden;">
                            <div style="height: 100%; width: {{ min($memUsedPct, 100) }}%; background: {{ $memUsedPct > 85 ? 'var(--color-warning)' : 'var(--color-success)' }};"></div>
                        </div>
                    </div>
                    @endif
                </div>
            @endif

            @if($latestReport && isset($latestReport->payload['backup_status']))
                @php
                    $backupStatus = $latestReport->payload['backup_status'];
                    $backupHealthy = $backupStatus['healthy'] ?? false;
                    $backupTime = isset($backupStatus['latest_time']) && $backupStatus['latest_time'] > 0 
                        ? \Carbon\Carbon::createFromTimestamp($backupStatus['latest_time'])->diffForHumans() 
                        : 'No recent backup';
                @endphp
                <div style="flex: 1; min-width: 200px;">
                    <div style="font-size:0.75rem; text-transform:uppercase; color:var(--color-muted); margin-bottom:8px;">Backup Validation</div>
                    <div style="display:flex;align-items:center;gap:12px;">
                        @if($backupHealthy)
                            <div style="color:var(--color-success);">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:32px;height:32px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <div style="font-weight:600;font-size:0.9rem;">Verified</div>
                                <div style="font-size:0.7rem;color:var(--color-muted)">{{ $backupTime }}</div>
                            </div>
                        @else
                            <div style="color:var(--color-danger);">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:32px;height:32px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div>
                                <div style="font-weight:600;font-size:0.9rem;">Missing / Failed</div>
                                <div style="font-size:0.7rem;color:var(--color-muted)">Over 24h old</div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
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
                    <div class="table-wrapper" style="border:none; border-radius:0;">
                    <table class="table vakt-table">
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
                    </div>
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
                        <div class="table-wrapper" style="border:none; border-radius:0;">
                        <table class="table vakt-table">
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
                        </div>
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
                        <div class="table-wrapper" style="border:none; border-radius:0;">
                        <table class="table vakt-table">
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
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
