<div>
    <div class="breadcrumbs">
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span class="sep">›</span>
        <span class="current">Projects</span>
    </div>

    <x-page-header title="Projects" subtitle="Manage all monitored applications and domains" icon="folder">
        <a href="{{ route('projects.create') }}" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Project
        </a>
    </x-page-header>

    <div class="card mb-6" style="padding:16px 20px">
        <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
            <div style="flex:1;min-width:200px;position:relative">
                <svg style="position:absolute;left:10px;top:50%;transform:translateY(-50%);width:16px;height:16px;color:var(--color-text-dim)" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input wire:model.live="search" type="text" class="form-control" placeholder="Search domains or names..." style="padding-left:34px" />
            </div>
            <select wire:model.live="filterStatus" class="form-control" style="width:180px">
                <option value="">All Statuses</option>
                <option value="healthy">Healthy</option>
                <option value="warning">Warning</option>
                <option value="critical">Critical</option>
                <option value="unknown">Unknown</option>
            </select>
        </div>
    </div>

    <div wire:loading.class="opacity-50 pointer-events-none">
        @if($projects->isEmpty())
            <x-empty-state icon="folder" title="No projects found" message="No projects match your current filters or none have been added yet." />
        @else
            <div class="grid grid-auto">
                @foreach($projects as $project)
                    <div class="project-card" wire:key="project-{{ $project->id }}" style="{{ !$project->active ? 'opacity:0.6' : '' }}">
                        <div class="project-card-header">
                            <a href="{{ route('projects.show', $project) }}" class="project-domain" style="text-decoration:none;">{{ $project->domain }}</a>
                            <div style="display:flex;gap:8px">
                                <x-badge type="muted">{{ $project->stack }}</x-badge>
                                <span class="project-status-indicator {{ $project->active ? $project->status : 'unknown' }}" title="Status"></span>
                            </div>
                        </div>

                        <div class="project-meta" style="flex-wrap:wrap;gap:8px 16px">
                            <div class="project-meta-item">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                {{ $project->last_checked_at ? $project->last_checked_at->diffForHumans() : 'Never checked' }}
                            </div>
                            <div class="project-meta-item">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                {{ str_replace('_', ' ', $project->server_type) }}
                            </div>
                            <div class="project-meta-item">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                {{ $project->monitoring_interval_minutes }}m interval
                            </div>
                        </div>

                        <div style="display:flex;align-items:center;gap:16px;margin-top:4px">
                            <div>
                                <div style="font-size:0.65rem;text-transform:uppercase;color:var(--color-muted);letter-spacing:.05em">Open Incidents</div>
                                <div style="font-family:var(--font-display);font-size:1.2rem;font-weight:700;color:var(--color-{{ $project->open_incidents_count > 0 ? 'danger' : 'success' }})">{{ $project->open_incidents_count }}</div>
                            </div>
                            <div style="height:30px;width:1px;background:var(--color-border)"></div>
                            <div>
                                <div style="font-size:0.65rem;text-transform:uppercase;color:var(--color-muted);letter-spacing:.05em">Security Score</div>
                                <div style="font-family:var(--font-display);font-size:1.2rem;font-weight:700;color:var(--color-{{ $project->security_score >= 80 ? 'primary' : ($project->security_score >= 60 ? 'warning' : 'danger') }})">{{ $project->security_score }}</div>
                            </div>
                        </div>

                        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:12px;padding-top:12px;border-top:1px solid var(--color-border)">
                            <div style="display:flex;gap:6px">
                                <a href="{{ route('projects.show', $project) }}" class="btn btn-ghost btn-sm">View</a>
                                <a href="{{ route('projects.edit', $project) }}" class="btn btn-ghost btn-sm">Edit</a>
                            </div>
                            <div style="display:flex;gap:6px">
                                <button wire:click="toggleActive({{ $project->id }})" class="btn btn-ghost btn-sm" title="{{ $project->active ? 'Disable Monitoring' : 'Enable Monitoring' }}">
                                    @if($project->active) Pause @else Resume @endif
                                </button>
                                <button wire:click="deleteProject({{ $project->id }})" wire:confirm="Are you sure you want to delete {{ $project->domain }}? This will remove all logs and incidents and cannot be undone." class="btn btn-ghost btn-sm" style="color:var(--color-danger)">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
