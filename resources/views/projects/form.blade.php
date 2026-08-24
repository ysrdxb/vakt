@extends('layouts.app')

@section('content')
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

    .page-container {
        width: 100%;
        padding: 24px;
    }

    .page-title {
        font-size: 24px;
        font-weight: 600;
        margin-bottom: 8px;
        color: #f8fafc;
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
        color: #f8fafc;
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
        text-transform: uppercase;
        letter-spacing: 0.05em;
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

    .form-text {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 6px;
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
        text-decoration: none;
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

    .spinner-sm {
        display: inline-block;
        width: 14px;
        height: 14px;
        border: 2px solid rgba(255,255,255,0.3);
        border-radius: 50%;
        border-top-color: #fff;
        animation: spin 0.8s linear infinite;
        margin-right: 8px;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>

<div class="page-container" x-data="{
    serverType: '{{ old('server_type', $project->server_type ?? 'same_server') }}',
    runningDiagnostics: false,
    diagnosticStatus: null,
    diagnosticResults: [],
    secretKey: '{{ old('agent_secret', $agent_secret) }}',
    logPath: '{{ old('log_path', $project->log_path ?? 'storage/logs/laravel.log') }}',
    autoDetecting: false,
    submitting: false,
    formErrors: [],

    generateSecret() {
        let array = new Uint8Array(32);
        window.crypto.getRandomValues(array);
        this.secretKey = Array.from(array, byte => byte.toString(16).padStart(2, '0')).join('');
    },

    async runDiagnostics() {
        this.runningDiagnostics = true;
        this.diagnosticResults = [];
        
        try {
            const domainEl = document.getElementById('domain');
            const serverPathEl = document.getElementById('server_path');
            const agentUrlEl = document.getElementById('agent_url');

            const res = await fetch('{{ route("projects.test-connection") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    server_type: this.serverType,
                    server_path: serverPathEl ? serverPathEl.value : '',
                    domain: domainEl ? domainEl.value : '',
                    log_path: this.logPath,
                    agent_url: agentUrlEl ? agentUrlEl.value : '',
                    agent_secret: this.secretKey
                })
            });
            const data = await res.json();
            this.diagnosticStatus = data.status;
            this.diagnosticResults = data.results || [];
        } catch (e) {
            this.diagnosticStatus = 'failed';
            this.diagnosticResults = [{ icon: '❌', name: 'Connection Error', value: e.message, pass: false, fix: 'Check server network' }];
        } finally {
            this.runningDiagnostics = false;
        }
    },

    async autoDetectLog() {
        this.autoDetecting = true;
        try {
            const domainEl = document.getElementById('domain');
            const serverPathEl = document.getElementById('server_path');
            
            const res = await fetch('{{ route("projects.auto-detect-log") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    server_path: serverPathEl ? serverPathEl.value : '',
                    domain: domainEl ? domainEl.value : ''
                })
            });
            const data = await res.json();
            if (data.success && data.log_path) {
                this.logPath = data.log_path;
                alert('✅ Log path auto-detected: ' + data.log_path);
            } else {
                alert('⚠️ ' + (data.message || 'Log path not found automatically.'));
            }
        } catch (e) {
            alert('❌ Auto-detect failed: ' + e.message);
        } finally {
            this.autoDetecting = false;
        }
    },

    async submitForm() {
        this.submitting = true;
        this.formErrors = [];

        const formEl = this.$refs.projectForm;
        const formData = new FormData(formEl);

        try {
            const res = await fetch(formEl.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            });

            const data = await res.json();

            if (res.status === 422) {
                let errs = [];
                if (data.errors) {
                    Object.values(data.errors).forEach(errArray => {
                        errs.push(...errArray);
                    });
                } else if (data.message) {
                    errs.push(data.message);
                }
                this.formErrors = errs;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else if (res.ok && data.redirect_url) {
                window.location.href = data.redirect_url;
            } else if (!res.ok) {
                this.formErrors = [data.message || 'An error occurred while saving.'];
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        } catch (e) {
            this.formErrors = ['Network error: ' + e.message];
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } finally {
            this.submitting = false;
        }
    }
}">
    <div class="page-title">{{ $isEdit ? 'Modify Asset Configuration' : 'Configure Monitoring Target' }}</div>
    <div class="page-subtitle">Configure asset properties, surveillance scope, and connection parameters.</div>

    {{-- Dynamic AJAX Error Banner (No Page Refresh) --}}
    <template x-if="formErrors.length > 0">
        <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid var(--danger); color: #f87171; padding: 16px; border-radius: var(--radius); margin-bottom: 24px;">
            <strong style="display: block; margin-bottom: 8px;">Please fix the following validation errors:</strong>
            <ul style="margin: 0; padding-left: 20px;">
                <template x-for="err in formErrors" :key="err">
                    <li x-text="err"></li>
                </template>
            </ul>
        </div>
    </template>

    <form x-ref="projectForm" method="POST" action="{{ $isEdit ? route('projects.update', $project) : route('projects.store') }}" @submit.prevent="submitForm">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        {{-- Hidden Server Type Input to guarantee POST data --}}
        <input type="hidden" name="server_type" :value="serverType" />

        {{-- Section 1: Basic Information --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">1. Target Information</div>
            </div>
            <div class="card-body">
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" for="name">Asset Name *</label>
                        <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $project->name ?? '') }}" placeholder="e.g. Core Production System" required />
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="domain">Domain Name *</label>
                        <input type="text" id="domain" name="domain" class="form-control" value="{{ old('domain', $project->domain ?? '') }}" placeholder="e.g. system.domain.com" required />
                        <div class="form-text">Domain or domain/folder without protocol prefix.</div>
                    </div>
                </div>

                <div class="grid-3">
                    <div class="form-group">
                        <label class="form-label" for="stack">Application Stack *</label>
                        <select id="stack" name="stack" class="form-select">
                            <option value="laravel" {{ old('stack', $project->stack ?? '') === 'laravel' ? 'selected' : '' }}>Laravel Framework</option>
                            <option value="wordpress" {{ old('stack', $project->stack ?? '') === 'wordpress' ? 'selected' : '' }}>WordPress</option>
                            <option value="nodejs" {{ old('stack', $project->stack ?? '') === 'nodejs' ? 'selected' : '' }}>Node.js / Express</option>
                            <option value="custom_php" {{ old('stack', $project->stack ?? '') === 'custom_php' ? 'selected' : '' }}>Custom PHP App</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="php_version">PHP Environment</label>
                        <input type="text" id="php_version" name="php_version" class="form-control" value="{{ old('php_version', $project->php_version ?? '8.3') }}" placeholder="8.3" />
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="monitoring_interval_minutes">Check Frequency</label>
                        <select id="monitoring_interval_minutes" name="monitoring_interval_minutes" class="form-select">
                            <option value="1" {{ old('monitoring_interval_minutes', $project->monitoring_interval_minutes ?? 5) == 1 ? 'selected' : '' }}>Every Minute</option>
                            <option value="5" {{ old('monitoring_interval_minutes', $project->monitoring_interval_minutes ?? 5) == 5 ? 'selected' : '' }}>Every 5 Minutes</option>
                            <option value="15" {{ old('monitoring_interval_minutes', $project->monitoring_interval_minutes ?? 5) == 15 ? 'selected' : '' }}>Every 15 Minutes</option>
                            <option value="30" {{ old('monitoring_interval_minutes', $project->monitoring_interval_minutes ?? 5) == 30 ? 'selected' : '' }}>Every 30 Minutes</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Target Description</label>
                    <textarea id="description" name="description" class="form-control" rows="2" placeholder="Brief notes on asset purpose...">{{ old('description', $project->description ?? '') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Section 2: Connection Strategy --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">2. Server Connection Architecture</div>
            </div>
            <div class="card-body">
                <div class="option-cards">
                    <label class="option-card" :class="{ 'selected': serverType === 'same_server' }" @click="serverType = 'same_server'">
                        <input type="radio" value="same_server" x-model="serverType" />
                        <div>
                            <div style="font-weight: 600; color: #fff; font-size: 15px;">Same Server Direct Access</div>
                            <div style="font-size: 13px; color: var(--text-muted);">Direct filesystem log reading (No Agent required).</div>
                        </div>
                    </label>

                    <label class="option-card" :class="{ 'selected': serverType === 'external_agent' }" @click="serverType = 'external_agent'">
                        <input type="radio" value="external_agent" x-model="serverType" />
                        <div>
                            <div style="font-weight: 600; color: #fff; font-size: 15px;">Remote SOC Agent</div>
                            <div style="font-size: 13px; color: var(--text-muted);">Single-file agent PHP bridge over HTTPS.</div>
                        </div>
                    </label>

                    <label class="option-card" :class="{ 'selected': serverType === 'ftp' }" @click="serverType = 'ftp'">
                        <input type="radio" value="ftp" x-model="serverType" />
                        <div>
                            <div style="font-weight: 600; color: #fff; font-size: 15px;">FTP / SFTP Bridge</div>
                            <div style="font-size: 13px; color: var(--text-muted);">Remote log monitoring via FTP credentials.</div>
                        </div>
                    </label>
                </div>

                {{-- Same Server Fields --}}
                <div x-show="serverType === 'same_server'">
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label" for="server_path">Absolute Root Path *</label>
                            <input type="text" id="server_path" name="server_path" class="form-control" value="{{ old('server_path', $project->server_path ?? '') }}" placeholder="/var/www/virtual/domain.com/htdocs" />
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="log_path">Relative Log Path</label>
                            <div style="display: flex; gap: 8px;">
                                <input type="text" id="log_path" name="log_path" class="form-control" x-model="logPath" placeholder="storage/logs/laravel.log" />
                                <button type="button" class="btn btn-secondary" style="white-space: nowrap;" @click="autoDetectLog" :disabled="autoDetecting">
                                    <span x-text="autoDetecting ? 'Detecting...' : 'Auto-Detect'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- External Agent Fields --}}
                <div x-show="serverType === 'external_agent'">
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label" for="agent_url">Agent Public URL *</label>
                            <input type="url" id="agent_url" name="agent_url" class="form-control" value="{{ old('agent_url', $project->agent_url ?? '') }}" placeholder="https://target.com/soc-agent.php" />
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="agent_secret">Agent Secret Key</label>
                            <div style="display: flex; gap: 8px;">
                                <input type="text" id="agent_secret" name="agent_secret" class="form-control" x-model="secretKey" readonly />
                                <button type="button" class="btn btn-secondary" @click="generateSecret">Regenerate</button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Diagnostics Button --}}
                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between;">
                    <button type="button" class="btn btn-secondary" @click="runDiagnostics" :disabled="runningDiagnostics">
                        <span x-text="runningDiagnostics ? 'Testing Connection...' : '⚡ Test Connection & Run Diagnostics'"></span>
                    </button>

                    <template x-if="diagnosticStatus">
                        <span style="font-weight: 600; font-size: 13px;" :style="{ color: diagnosticStatus === 'ready' ? 'var(--success)' : 'var(--danger)' }" x-text="diagnosticStatus === 'ready' ? '✅ Connection Verification Passed' : '❌ Connection Issues Found'"></span>
                    </template>
                </div>

                {{-- Diagnostic Results Table --}}
                <template x-if="diagnosticResults.length > 0">
                    <div style="margin-top: 16px; background: rgba(0,0,0,0.2); border: 1px solid var(--border-color); border-radius: 6px; padding: 12px;">
                        <template x-for="res in diagnosticResults" :key="res.name">
                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,0.05);">
                                <div>
                                    <span x-text="res.icon"></span>
                                    <strong x-text="res.name" style="margin-left: 6px; color: #fff;"></strong>
                                    <span style="color: var(--text-muted); font-size: 12px; margin-left: 8px;" x-text="res.value"></span>
                                </div>
                                <span style="font-size: 12px; color: var(--danger);" x-text="res.fix"></span>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        {{-- Section 3: Alert Configuration --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">3. Notification Channels</div>
            </div>
            <div class="card-body">
                <div class="grid-3">
                    <div class="form-group">
                        <label class="form-label" for="alert_email">Alert Dispatch Email</label>
                        <input type="email" id="alert_email" name="alert_email" class="form-control" value="{{ old('alert_email', $project->alert_email ?? auth()->user()->email) }}" />
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="slack_webhook_url">Slack Webhook URL</label>
                        <input type="url" id="slack_webhook_url" name="slack_webhook_url" class="form-control" value="{{ old('slack_webhook_url', $project->slack_webhook_url ?? '') }}" placeholder="https://hooks.slack.com/services/..." />
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="discord_webhook_url">Discord Webhook URL</label>
                        <input type="url" id="discord_webhook_url" name="discord_webhook_url" class="form-control" value="{{ old('discord_webhook_url', $project->discord_webhook_url ?? '') }}" placeholder="https://discord.com/api/webhooks/..." />
                    </div>
                </div>
            </div>
        </div>

        {{-- Submit Buttons --}}
        <div style="display: flex; gap: 12px; justify-content: flex-end;">
            <a href="{{ route('projects.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary" :disabled="submitting">
                <template x-if="submitting">
                    <span><span class="spinner-sm"></span> Saving...</span>
                </template>
                <template x-if="!submitting">
                    <span>{{ $isEdit ? 'Save Asset Configuration' : 'Create & Register Asset Target' }}</span>
                </template>
            </button>
        </div>
    </form>
</div>
@endsection
