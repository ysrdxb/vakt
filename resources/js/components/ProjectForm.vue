<template>
  <div class="page-container">
    <div class="page-title">{{ isEdit ? 'Modify Asset Configuration' : 'Configure Monitoring Target' }}</div>
    <div class="page-subtitle">Configure asset properties, surveillance scope, and connection parameters.</div>

    <!-- Validation Errors -->
    <div v-if="errors.length" class="error-banner">
      <strong>Please fix the following errors:</strong>
      <ul>
        <li v-for="err in errors" :key="err">{{ err }}</li>
      </ul>
    </div>

    <!-- Success Banner -->
    <div v-if="successMessage" class="success-banner">
      ✅ {{ successMessage }}
    </div>

    <form @submit.prevent="submitForm">

      <!-- Section 1: Basic Information -->
      <div class="card">
        <div class="card-header"><div class="card-title">1. Target Information</div></div>
        <div class="card-body">
          <div class="grid-2">
            <div class="form-group">
              <label class="form-label">Asset Name *</label>
              <input type="text" v-model="form.name" class="form-control" placeholder="e.g. Core Production System" required />
            </div>
            <div class="form-group">
              <label class="form-label">Domain Name *</label>
              <input type="text" v-model="form.domain" class="form-control" placeholder="e.g. system.domain.com" required />
              <div class="form-text">Domain or domain/folder without protocol prefix.</div>
            </div>
          </div>

          <div class="grid-3">
            <div class="form-group">
              <label class="form-label">Application Stack *</label>
              <select v-model="form.stack" class="form-control">
                <option value="laravel">Laravel Framework</option>
                <option value="wordpress">WordPress</option>
                <option value="nodejs">Node.js / Express</option>
                <option value="custom_php">Custom PHP App</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">PHP Environment</label>
              <input type="text" v-model="form.php_version" class="form-control" placeholder="8.3" />
            </div>
            <div class="form-group">
              <label class="form-label">Check Frequency</label>
              <select v-model="form.monitoring_interval_minutes" class="form-control">
                <option value="1">Every Minute</option>
                <option value="5">Every 5 Minutes</option>
                <option value="15">Every 15 Minutes</option>
                <option value="30">Every 30 Minutes</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Target Description</label>
            <textarea v-model="form.description" class="form-control" rows="2" placeholder="Brief notes on asset purpose..."></textarea>
          </div>
        </div>
      </div>

      <!-- Section 2: Connection -->
      <div class="card">
        <div class="card-header"><div class="card-title">2. Server Connection Architecture</div></div>
        <div class="card-body">
          <div class="option-cards">
            <label class="option-card" :class="{ selected: form.server_type === 'same_server' }">
              <input type="radio" v-model="form.server_type" value="same_server" />
              <div>
                <div style="font-weight:600;color:#fff;font-size:15px;">Same Server Direct Access</div>
                <div style="font-size:13px;color:#94a3b8;">Direct filesystem log reading. No agent required.</div>
              </div>
            </label>
            <label class="option-card" :class="{ selected: form.server_type === 'external_agent' }">
              <input type="radio" v-model="form.server_type" value="external_agent" />
              <div>
                <div style="font-weight:600;color:#fff;font-size:15px;">Remote SOC Agent</div>
                <div style="font-size:13px;color:#94a3b8;">Single-file agent PHP bridge over HTTPS.</div>
              </div>
            </label>
            <label class="option-card" :class="{ selected: form.server_type === 'ftp' }">
              <input type="radio" v-model="form.server_type" value="ftp" />
              <div>
                <div style="font-weight:600;color:#fff;font-size:15px;">FTP / SFTP Bridge</div>
                <div style="font-size:13px;color:#94a3b8;">Remote log monitoring via FTP credentials.</div>
              </div>
            </label>
          </div>

          <!-- Same Server Fields -->
          <div v-if="form.server_type === 'same_server'">
            <div class="grid-2">
              <div class="form-group">
                <label class="form-label">Absolute Root Path *</label>
                <input type="text" v-model="form.server_path" class="form-control" placeholder="/var/www/virtual/domain.com/htdocs" />
              </div>
              <div class="form-group">
                <label class="form-label">Relative Log Path</label>
                <div style="display:flex;gap:8px;">
                  <input type="text" v-model="form.log_path" class="form-control" placeholder="storage/logs/laravel.log" />
                  <button type="button" class="btn btn-secondary" @click="autoDetectLog" :disabled="autoDetecting" style="white-space:nowrap;">
                    {{ autoDetecting ? 'Detecting...' : 'Auto-Detect' }}
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Agent Fields -->
          <div v-if="form.server_type === 'external_agent'">
            <div class="grid-2">
              <div class="form-group">
                <label class="form-label">Agent Public URL *</label>
                <input type="url" v-model="form.agent_url" class="form-control" placeholder="https://target.com/soc-agent.php" />
              </div>
              <div class="form-group">
                <label class="form-label">Agent Secret Key</label>
                <div style="display:flex;gap:8px;">
                  <input type="text" v-model="form.agent_secret" class="form-control" readonly />
                  <button type="button" class="btn btn-secondary" @click="generateSecret">Regenerate</button>
                </div>
              </div>
            </div>
          </div>

          <!-- Diagnostics -->
          <div style="margin-top:20px;padding-top:20px;border-top:1px solid #334155;display:flex;align-items:center;justify-content:space-between;">
            <button type="button" class="btn btn-secondary" @click="runDiagnostics" :disabled="runningDiagnostics">
              {{ runningDiagnostics ? 'Testing...' : '⚡ Test Connection & Run Diagnostics' }}
            </button>
            <span v-if="diagnosticStatus" :style="{ color: diagnosticStatus === 'ready' ? '#10b981' : '#ef4444', fontWeight: 600 }">
              {{ diagnosticStatus === 'ready' ? '✅ Passed' : '❌ Issues Found' }}
            </span>
          </div>

          <div v-if="diagnosticResults.length" style="margin-top:16px;background:rgba(0,0,0,0.2);border:1px solid #334155;border-radius:6px;padding:12px;">
            <div v-for="res in diagnosticResults" :key="res.name" style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(255,255,255,0.05);">
              <div>
                <span>{{ res.icon }}</span>
                <strong style="margin-left:6px;color:#fff;">{{ res.name }}</strong>
                <span style="color:#94a3b8;font-size:12px;margin-left:8px;">{{ res.value }}</span>
              </div>
              <span style="font-size:12px;color:#ef4444;">{{ res.fix }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Section 3: Alerts -->
      <div class="card">
        <div class="card-header"><div class="card-title">3. Notification Channels</div></div>
        <div class="card-body">
          <div class="grid-3">
            <div class="form-group">
              <label class="form-label">Alert Email</label>
              <input type="email" v-model="form.alert_email" class="form-control" />
            </div>
            <div class="form-group">
              <label class="form-label">Slack Webhook URL</label>
              <input type="url" v-model="form.slack_webhook_url" class="form-control" placeholder="https://hooks.slack.com/..." />
            </div>
            <div class="form-group">
              <label class="form-label">Discord Webhook URL</label>
              <input type="url" v-model="form.discord_webhook_url" class="form-control" placeholder="https://discord.com/api/webhooks/..." />
            </div>
          </div>
        </div>
      </div>

      <!-- Submit -->
      <div style="display:flex;gap:12px;justify-content:flex-end;">
        <Link :href="route('projects.index')" class="btn btn-secondary">Cancel</Link>
        <button type="submit" class="btn btn-primary" :disabled="submitting">
          <span v-if="submitting"><span class="spinner-sm"></span> Saving...</span>
          <span v-else>{{ isEdit ? 'Save Asset Configuration' : 'Create & Register Asset Target' }}</span>
        </button>
      </div>

    </form>
  </div>
</template>

<script setup>
import { ref, reactive, toRaw } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
  project: { type: Object, default: () => ({}) },
  agent_secret: { type: String, required: true },
  isEdit: { type: Boolean, default: false },
});

const form = reactive({
  name: props.project.name || '',
  domain: props.project.domain || '',
  description: props.project.description || '',
  stack: props.project.stack || 'laravel',
  php_version: props.project.php_version || '8.3',
  monitoring_interval_minutes: props.project.monitoring_interval_minutes || '5',
  server_type: props.project.server_type || 'same_server',
  server_path: props.project.server_path || '',
  log_path: props.project.log_path || 'storage/logs/laravel.log',
  agent_url: props.project.agent_url || '',
  agent_secret: props.isEdit ? props.agent_secret : (props.project.agent_secret || props.agent_secret),
  alert_email: props.project.alert_email || '',
  slack_webhook_url: props.project.slack_webhook_url || '',
  discord_webhook_url: props.project.discord_webhook_url || '',
});

const errors = ref([]);
const successMessage = ref('');
const submitting = ref(false);
const autoDetecting = ref(false);
const runningDiagnostics = ref(false);
const diagnosticStatus = ref(null);
const diagnosticResults = ref([]);

function generateSecretKey() {
  if (typeof window !== 'undefined' && window.crypto) {
    const arr = new Uint8Array(32);
    window.crypto.getRandomValues(arr);
    return Array.from(arr, b => b.toString(16).padStart(2, '0')).join('');
  }
  return '';
}

function generateSecret() {
  form.agent_secret = generateSecretKey();
}

async function submitForm() {
  submitting.value = true;
  errors.value = [];
  successMessage.value = '';

  // Use toRaw + JSON parse/stringify to get a clean plain object from Vue Proxy
  const raw = JSON.parse(JSON.stringify(toRaw(form)));

  // Cast numeric fields so Laravel integer validation passes
  raw.monitoring_interval_minutes = parseInt(raw.monitoring_interval_minutes, 10);
  if (props.isEdit) {
    raw._method = 'PUT'; // Laravel form spoofing for PUT
  }

  const url = props.isEdit ? route('projects.update', props.project.id) : route('projects.store');

  try {
    const res = await axios.post(url, raw, {
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      }
    });

    const data = res.data;
    if (data.success) {
      successMessage.value = data.message;
      if (window.dispatchEvent) {
        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', title: 'Saved', message: data.message } }));
      }
      setTimeout(() => {
        router.visit(data.redirect_url);
      }, 1000);
    }
  } catch (err) {
    if (err.response && err.response.status === 422) {
      const msgs = [];
      const errData = err.response.data.errors;
      for (const k in errData) {
        msgs.push(errData[k].join(' '));
      }
      errors.value = msgs;
    } else {
      errors.value = [err.message || 'An unexpected error occurred.'];
    }
  } finally {
    submitting.value = false;
  }
}

async function autoDetectLogPath() {
  autoDetecting.value = true;
  errors.value = [];
  try {
    const res = await axios.post(route('projects.auto-detect-log'), {
      server_path: form.server_path,
      domain: form.domain
    }, {
      headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' }
    });
    
    const data = res.data;
    if (data.success) {
      form.log_path = data.log_path;
      if (window.dispatchEvent) {
        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', title: 'Auto-Detect', message: 'Log path found.' } }));
      }
    } else {
      alert(data.message);
    }
  } catch (err) {
    console.error('Auto detect error', err);
    alert('Failed to run auto-detect.');
  } finally {
    autoDetecting.value = false;
  }
}

async function testConnection() {
  runningDiagnostics.value = true;
  diagnosticStatus.value = null;
  diagnosticResults.value = [];
  
  try {
    const res = await axios.post(route('projects.test-connection'), {
      server_type: form.server_type,
      server_path: form.server_path,
      agent_url: form.agent_url,
      agent_secret: form.agent_secret
    }, {
      headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' }
    });
    
    const data = res.data;
    diagnosticStatus.value = data.status;
    diagnosticResults.value = data.results || [];
  } catch (err) {
    diagnosticStatus.value = 'failed';
    if (err.response && err.response.data && err.response.data.results) {
        diagnosticResults.value = err.response.data.results;
    } else {
        diagnosticResults.value = [{ icon: '❌', name: 'Internal Server Error', value: 'API Error', pass: false, fix: 'Check network tab' }];
    }
  } finally {
    runningDiagnostics.value = false;
  }
}

</script>

<style scoped>
.page-container { width: 100%; padding: 24px; }
.page-title { font-size: 24px; font-weight: 600; margin-bottom: 8px; color: #f8fafc; }
.page-subtitle { color: #94a3b8; font-size: 14px; margin-bottom: 32px; }

.error-banner {
  background: rgba(239, 68, 68, 0.15);
  border: 1px solid #ef4444;
  color: #f87171;
  padding: 16px;
  border-radius: 8px;
  margin-bottom: 24px;
}
.error-banner ul { margin: 8px 0 0; padding-left: 20px; }

.success-banner {
  background: rgba(16, 185, 129, 0.15);
  border: 1px solid #10b981;
  color: #34d399;
  padding: 16px;
  border-radius: 8px;
  margin-bottom: 24px;
}

.card { background: #1e293b; border: 1px solid #334155; border-radius: 8px; margin-bottom: 24px; overflow: hidden; }
.card-header { padding: 20px 24px; border-bottom: 1px solid #334155; background: rgba(255,255,255,0.02); }
.card-title { font-size: 16px; font-weight: 600; margin: 0; color: #f8fafc; }
.card-body { padding: 24px; }

.form-group { margin-bottom: 20px; }
.form-label { display: block; font-size: 13px; font-weight: 500; color: #94a3b8; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.05em; }
.form-control { width: 100%; background: #0f172a; border: 1px solid #334155; color: #f8fafc; padding: 10px 12px; border-radius: 6px; font-size: 14px; transition: border-color 0.2s; }
.form-control:focus { outline: none; border-color: #3b82f6; }
.form-text { font-size: 12px; color: #94a3b8; margin-top: 6px; }

.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
.grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 24px; }

.option-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 24px; }
.option-card { border: 1px solid #334155; border-radius: 8px; padding: 16px; cursor: pointer; background: #0f172a; transition: all 0.2s; display: flex; align-items: flex-start; gap: 12px; }
.option-card:hover { border-color: #475569; }
.option-card.selected { border-color: #3b82f6; background: rgba(59,130,246,0.1); }
.option-card input[type="radio"] { margin-top: 4px; accent-color: #3b82f6; width: 16px; height: 16px; }

.btn { display: inline-flex; align-items: center; justify-content: center; padding: 10px 18px; border-radius: 6px; font-size: 14px; font-weight: 500; cursor: pointer; border: 1px solid transparent; transition: all 0.2s; text-decoration: none; }
.btn-primary { background: #3b82f6; color: #fff; }
.btn-primary:hover { background: #2563eb; }
.btn-primary:disabled { opacity: 0.7; cursor: not-allowed; }
.btn-secondary { background: rgba(255,255,255,0.05); border-color: #334155; color: #f8fafc; }
.btn-secondary:hover { background: rgba(255,255,255,0.1); border-color: #475569; }

.spinner-sm { display: inline-block; width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.3); border-radius: 50%; border-top-color: #fff; animation: spin 0.8s linear infinite; margin-right: 8px; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>
