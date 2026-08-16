<div>
<style>
    /* Professional Clean SaaS Theme (Dark) */
    :root {
        --bg-color: #0f172a;
        --surface-color: #1e293b;
        --border-color: #334155;
        --text-main: #f8fafc;
        --text-muted: #94a3b8;
        --primary: #3b82f6;
        --primary-hover: #2563eb;
        --danger: #ef4444;
        --success: #10b981;
        --warning: #f59e0b;
        --radius: 8px;
    }

    body {
        background-color: var(--bg-color);
        color: var(--text-main);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }

    .page-container {
        width: 100%;
        padding: 24px;
    }

    .page-title {
        font-size: 24px;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .page-subtitle {
        color: var(--text-muted);
        font-size: 14px;
        margin-bottom: 32px;
    }

    .card {
        background: var(--surface-color);
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        margin-bottom: 24px;
        overflow: hidden;
    }

    .card-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-color);
        background: rgba(255, 255, 255, 0.02);
    }

    .card-title {
        font-size: 16px;
        font-weight: 600;
        margin: 0;
    }

    .card-body {
        padding: 24px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        font-size: 13px;
        font-weight: 500;
        color: var(--text-muted);
        margin-bottom: 8px;
    }

    .form-control, .form-select {
        width: 100%;
        background: var(--bg-color);
        border: 1px solid var(--border-color);
        color: var(--text-main);
        padding: 10px 12px;
        border-radius: 6px;
        font-size: 14px;
        transition: border-color 0.2s;
    }

    .form-control:focus, .form-select:focus {
        outline: none;
        border-color: var(--primary);
    }

    .form-control::placeholder {
        color: #475569;
    }

    .form-text {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 6px;
    }

    .text-danger {
        color: var(--danger);
        font-size: 12px;
        margin-top: 4px;
    }

    .grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
    }

    .grid-3 {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 24px;
    }

    /* Option Cards (for Server Connection) */
    .option-cards {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 24px;
    }

    .option-card {
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 16px;
        cursor: pointer;
        background: var(--bg-color);
        transition: all 0.2s;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .option-card:hover {
        border-color: #475569;
    }

    .option-card.selected {
        border-color: var(--primary);
        background: rgba(59, 130, 246, 0.1);
    }

    .option-card input[type="radio"] {
        margin-top: 4px;
        accent-color: var(--primary);
        width: 16px;
        height: 16px;
    }

    .option-title {
        font-size: 15px;
        font-weight: 600;
        margin-bottom: 4px;
        color: var(--text-main);
    }

    .option-desc {
        font-size: 13px;
        color: var(--text-muted);
    }

    /* Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 18px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        border: 1px solid transparent;
        transition: all 0.2s;
    }

    .btn-primary {
        background: var(--primary);
        color: #fff;
    }

    .btn-primary:hover {
        background: var(--primary-hover);
    }

    .btn-secondary {
        background: rgba(255, 255, 255, 0.05);
        border-color: var(--border-color);
        color: var(--text-main);
    }

    .btn-secondary:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: #475569;
    }

    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    /* Toggles & Checkboxes */
    .checkbox-label {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        font-size: 14px;
        cursor: pointer;
        padding: 8px 0;
    }

    .checkbox-label input {
        margin-top: 4px;
    }

    .module-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 16px;
    }

    .module-item {
        border: 1px solid var(--border-color);
        padding: 16px;
        border-radius: 8px;
        background: rgba(255,255,255,0.01);
        transition: border-color 0.2s;
    }
    
    .module-item:hover {
        border-color: #475569;
    }

    /* Action Bar */
    .action-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 40px;
        padding: 20px;
        background: var(--surface-color);
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
    }

    .alert {
        padding: 12px 16px;
        border-radius: 6px;
        font-size: 13px;
        margin-bottom: 20px;
    }
    .alert-info { background: rgba(59, 130, 246, 0.1); border: 1px solid var(--primary); color: #93c5fd; }
    .alert-success { background: rgba(16, 185, 129, 0.1); border: 1px solid var(--success); color: #6ee7b7; }
    .alert-danger { background: rgba(239, 68, 68, 0.1); border: 1px solid var(--danger); color: #fca5a5; }

</style>

<div class="page-container">
    <div style="margin-bottom: 20px;">
        <a href="{{ route('projects.index') }}" style="color:var(--text-muted); text-decoration:none; font-size:14px; font-weight: 500;">← Back to Projects</a>
    </div>

    <h1 class="page-title">{{ $isEdit ? 'Edit Project' : 'Add New Project' }}</h1>
    <p class="page-subtitle">Configure monitoring details and server connection settings.</p>

    <form wire:submit.prevent="saveProject">
        
        <!-- Project Details -->
        <div class="card">
            <div class="card-header"><h2 class="card-title">Project Details</h2></div>
            <div class="card-body">
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Project Name</label>
                        <input type="text" class="form-control" wire:model.blur="name" placeholder="e.g. Acme Corp Main Site">
                        @error('name') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Domain</label>
                        <input type="text" class="form-control" wire:model.blur="domain" placeholder="e.g. example.com">
                        @error('domain') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Description (Optional)</label>
                    <textarea class="form-control" wire:model.blur="description" rows="2" placeholder="Brief notes about this project..."></textarea>
                </div>

                <div class="grid-3">
                    <div class="form-group">
                        <label class="form-label">Technology Stack</label>
                        <select class="form-select" wire:model.live="stack">
                            <option value="laravel">Laravel</option>
                            <option value="php">Native PHP</option>
                            <option value="wordpress">WordPress</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">PHP Version</label>
                        <select class="form-select" wire:model.blur="php_version">
                            <option value="8.4">8.4</option>
                            <option value="8.3">8.3</option>
                            <option value="8.2">8.2</option>
                            <option value="8.1">8.1</option>
                            <option value="8.0">8.0</option>
                            <option value="7.4">7.4</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Monitoring Interval</label>
                        <select class="form-select" wire:model.blur="monitoring_interval_minutes">
                            <option value="1">Every 1 minute</option>
                            <option value="5">Every 5 minutes (Recommended)</option>
                            <option value="15">Every 15 minutes</option>
                            <option value="30">Every 30 minutes</option>
                            <option value="60">Every 1 hour</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Alert Email Override (Optional)</label>
                    <input type="email" class="form-control" wire:model.blur="alert_email" placeholder="alerts@example.com" style="max-width: 400px;">
                    <div class="form-text">Leave blank to use the system default email for alerts.</div>
                </div>
            </div>
        </div>

        <!-- Server Connection -->
        <div class="card">
            <div class="card-header"><h2 class="card-title">Server Connection</h2></div>
            <div class="card-body">
                <div class="option-cards">
                    <label class="option-card {{ $server_type === 'same_server' ? 'selected' : '' }}">
                        <input type="radio" wire:model.live="server_type" value="same_server">
                        <div>
                            <div class="option-title">Same Server</div>
                            <div class="option-desc">Direct filesystem access.</div>
                        </div>
                    </label>
                    <label class="option-card {{ $server_type === 'external_agent' ? 'selected' : '' }}">
                        <input type="radio" wire:model.live="server_type" value="external_agent">
                        <div>
                            <div class="option-title">External Agent</div>
                            <div class="option-desc">Remote server via agent script.</div>
                        </div>
                    </label>
                    <label class="option-card {{ $server_type === 'ftp' ? 'selected' : '' }}">
                        <input type="radio" wire:model.live="server_type" value="ftp">
                        <div>
                            <div class="option-title">FTP Pull</div>
                            <div class="option-desc">Remote server via FTP.</div>
                        </div>
                    </label>
                </div>

                <!-- Same Server -->
                @if($server_type === 'same_server')
                <div>
                    <div class="form-group">
                        <label class="form-label">Absolute Server Path</label>
                        <input type="text" class="form-control" wire:model.blur="server_path" placeholder="/var/www/example.com">
                        <div class="form-text">The full directory path to the project on this server.</div>
                        @error('server_path') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Relative Log Path</label>
                            <input type="text" class="form-control" wire:model.blur="log_path" placeholder="storage/logs/laravel.log">
                        </div>
                        <div class="form-group">
                            <label class="form-label">PHP Error Log Path (Optional)</label>
                            <input type="text" class="form-control" wire:model.blur="php_error_log_path" placeholder="/var/log/php_errors.log">
                        </div>
                    </div>
                </div>
                @endif

                <!-- External Agent -->
                @if($server_type === 'external_agent')
                <div>
                    <div class="form-group">
                        <label class="form-label">Agent Secret Key</label>
                        <div style="display:flex; gap:12px; max-width: 600px;">
                            <input type="text" class="form-control" wire:model="agent_secret" readonly style="background: rgba(255,255,255,0.02); color: var(--text-muted)">
                            <button type="button" class="btn btn-secondary" wire:click="generateSecretKey" style="white-space: nowrap;">Generate New</button>
                        </div>
                        <div class="form-text">Used to authenticate the remote agent script. Keep this secret.</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">IP Whitelist (Optional)</label>
                        <input type="text" class="form-control" wire:model.blur="agent_ip_whitelist" placeholder="192.168.1.1, 10.0.0.2" style="max-width: 600px;">
                        <div class="form-text">Comma-separated IP addresses allowed to push data.</div>
                    </div>
                </div>
                @endif

                <!-- FTP -->
                @if($server_type === 'ftp')
                <div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">FTP Host / IP</label>
                            <input type="text" class="form-control" wire:model.blur="ftp_host" placeholder="ftp.example.com">
                        </div>
                        <div class="form-group">
                            <label class="form-label">FTP Username</label>
                            <input type="text" class="form-control" wire:model.blur="ftp_user">
                        </div>
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">FTP Password</label>
                            <input type="password" class="form-control" wire:model.blur="ftp_password" placeholder="{{ $isEdit ? '(Leave blank to keep existing)' : '' }}" autocomplete="new-password">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Relative Log Path</label>
                            <input type="text" class="form-control" wire:model.blur="log_path" placeholder="storage/logs/laravel.log">
                        </div>
                    </div>
                </div>
                @endif
                
                <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid var(--border-color);">
                    <button type="button" class="btn btn-secondary" wire:click="runDiagnostics" wire:loading.attr="disabled" wire:target="runDiagnostics">
                        <span wire:loading.remove wire:target="runDiagnostics">Test Connection</span>
                        <span wire:loading wire:target="runDiagnostics">Testing...</span>
                    </button>
                    
                    @if(!empty($diagnosticResults))
                        <div style="margin-top: 16px; background: rgba(0,0,0,0.2); padding: 16px; border-radius: 8px;">
                            @foreach($diagnosticResults as $res)
                                <div style="font-size: 13px; margin-bottom: 8px; display:flex; align-items:center; gap:8px;">
                                    <span>{{ $res['icon'] }}</span> 
                                    <strong>{{ $res['name'] }}:</strong> 
                                    <span style="color:var(--text-muted)">{{ $res['value'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Monitoring Settings -->
        <div class="card">
            <div class="card-header"><h2 class="card-title">Monitoring Settings</h2></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Active Modules</label>
                    <div class="module-grid">
                        <label class="checkbox-label module-item">
                            <input type="checkbox" wire:model="modules.log_analysis">
                            <div>
                                <div style="font-weight: 500;">Log Analysis</div>
                                <div class="form-text" style="margin-top:2px">Scan error logs for issues.</div>
                            </div>
                        </label>
                        <label class="checkbox-label module-item">
                            <input type="checkbox" wire:model="modules.file_integrity">
                            <div>
                                <div style="font-weight: 500;">File Integrity</div>
                                <div class="form-text" style="margin-top:2px">Detect unauthorized file changes.</div>
                            </div>
                        </label>
                        <label class="checkbox-label module-item">
                            <input type="checkbox" wire:model="modules.vulnerability">
                            <div>
                                <div style="font-weight: 500;">Vulnerability Scan</div>
                                <div class="form-text" style="margin-top:2px">Check dependencies for CVEs.</div>
                            </div>
                        </label>
                        <label class="checkbox-label module-item">
                            <input type="checkbox" wire:model="modules.php_config">
                            <div>
                                <div style="font-weight: 500;">PHP Config Check</div>
                                <div class="form-text" style="margin-top:2px">Audit php.ini for insecure settings.</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 32px; border-top: 1px solid var(--border-color); padding-top: 24px;">
                    <label class="form-label" style="font-size: 15px; font-weight: 600;">Alert Rules</label>
                    <div class="form-text" style="margin-bottom: 16px; margin-top: 4px;">Automatically create incidents when these events occur:</div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <label class="checkbox-label"><input type="checkbox" wire:model="incident_rules.malicious_file"> <span style="font-weight: 500;">Malicious file detected</span> (Critical)</label>
                        <label class="checkbox-label"><input type="checkbox" wire:model="incident_rules.env_modified"> <span style="font-weight: 500;">.env file modified</span> (Critical)</label>
                        <label class="checkbox-label"><input type="checkbox" wire:model="incident_rules.api_key_in_logs"> <span style="font-weight: 500;">API key exposed in logs</span> (Critical)</label>
                        <label class="checkbox-label"><input type="checkbox" wire:model="incident_rules.brute_force"> <span style="font-weight: 500;">Brute force attempt detected</span> (High)</label>
                        <label class="checkbox-label"><input type="checkbox" wire:model="incident_rules.critical_log"> <span style="font-weight: 500;">Server errors in logs</span> (High)</label>
                        <label class="checkbox-label"><input type="checkbox" wire:model="incident_rules.php_fatal"> <span style="font-weight: 500;">PHP fatal errors</span> (Medium)</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="action-bar">
            <label class="checkbox-label" style="align-items:center; margin: 0;">
                <input type="checkbox" wire:model="active" style="width: 18px; height: 18px;">
                <strong style="margin-left: 4px; font-size: 15px;">Enable Active Monitoring</strong>
            </label>
            
            <div style="display:flex; gap:16px;">
                <a href="{{ route('projects.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="saveProject" style="min-width: 140px;">
                    <span wire:loading.remove wire:target="saveProject">Save Project</span>
                    <span wire:loading wire:target="saveProject">Saving...</span>
                </button>
            </div>
        </div>
    </form>
</div>
</div>
