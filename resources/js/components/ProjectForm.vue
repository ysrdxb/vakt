<template>
  <div class="page-container" style="margin: 0 auto;">
    <h2 style="font-size: 24px; font-weight: 700; color: #f8fafc; margin-bottom: 24px;">
      {{ isEdit ? 'Edit Project' : 'Create Project' }}
    </h2>

    <!-- Validation Errors -->
    <div v-if="errors.length" class="error-banner" style="margin-bottom: 20px;">
      <div style="font-weight: 600; margin-bottom: 8px;">⚠️ Please fix the following errors:</div>
      <ul style="margin: 0; padding-left: 20px;">
        <li v-for="err in errors" :key="err">{{ err }}</li>
      </ul>
    </div>

    <form @submit.prevent="submitForm">

      <!-- Section 1: Basic Information -->
      <div class="card mb-6" style="border-radius: 12px; overflow: hidden; border: 1px solid rgba(255,255,255,0.1);">
        <div class="card-header" style="background: rgba(0,0,0,0.2); padding: 16px 24px; border-bottom: 1px solid rgba(255,255,255,0.05);">
          <div class="card-title" style="margin: 0; font-size: 18px; display: flex; align-items: center; gap: 8px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            1. Project Information
          </div>
        </div>
        <div class="card-body" style="padding: 24px;">
          <div class="grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
            <div class="form-group">
              <label class="form-label">Project Name *</label>
              <input type="text" v-model="form.name" class="form-control" placeholder="e.g. Core Production System" required />
              <div class="form-text" style="font-size: 12px; color: #94a3b8; margin-top: 4px;">A friendly name for your project dashboard.</div>
            </div>
            <div class="form-group">
              <label class="form-label">Domain *</label>
              <input type="text" v-model="form.domain" class="form-control" placeholder="e.g. system.domain.com" required />
              <div class="form-text" style="font-size: 12px; color: #94a3b8; margin-top: 4px;">e.g. domain.com or sub.domain.com/folder</div>
            </div>
          </div>

          <div class="grid-3" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 24px; margin-top: 24px;">
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
              <select v-model="form.php_version" class="form-control">
                <option value="8.4">PHP 8.4</option>
                <option value="8.3">PHP 8.3</option>
                <option value="8.2">PHP 8.2</option>
                <option value="8.1">PHP 8.1</option>
                <option value="8.0">PHP 8.0</option>
                <option value="7.4">PHP 7.4 (Legacy)</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Monitoring Interval</label>
              <select v-model="form.monitoring_interval_minutes" class="form-control">
                <option value="1">Every Minute (Aggressive)</option>
                <option value="5">Every 5 Minutes (Standard)</option>
                <option value="15">Every 15 Minutes (Relaxed)</option>
                <option value="30">Every 30 Minutes</option>
              </select>
            </div>
          </div>

          <div class="form-group" style="margin-top: 24px;">
            <label class="form-label">Description (Optional)</label>
            <textarea v-model="form.description" class="form-control" rows="2" placeholder="Brief notes on project purpose, specific requirements, or contacts..."></textarea>
          </div>
        </div>
      </div>

      <!-- Section 2: Connection -->
      <div class="card mb-6" style="border-radius: 12px; overflow: hidden; border: 1px solid rgba(255,255,255,0.1);">
        <div class="card-header" style="background: rgba(0,0,0,0.2); padding: 16px 24px; border-bottom: 1px solid rgba(255,255,255,0.05);">
          <div class="card-title" style="margin: 0; font-size: 18px; display: flex; align-items: center; gap: 8px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7" /></svg>
            2. Server Connection
          </div>
        </div>
        <div class="card-body" style="padding: 24px;">
          <div class="option-cards" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 24px; margin-bottom: 24px;">
            <label class="option-card" :class="{ selected: form.server_type === 'same_server' }" style="cursor: pointer; padding: 16px; border-radius: 8px; border: 2px solid transparent; background: rgba(255,255,255,0.03); transition: all 0.2s;">
              <input type="radio" v-model="form.server_type" value="same_server" style="display: none;" />
              <div>
                <div style="font-weight: 600; color: #fff; font-size: 15px; margin-bottom: 4px;">Same Server Access</div>
                <div style="font-size: 13px; color: #94a3b8; line-height: 1.4;">Direct filesystem read access. Fastest and most secure. No agent required.</div>
              </div>
            </label>
            <label class="option-card" :class="{ selected: form.server_type === 'external_agent' }" style="cursor: pointer; padding: 16px; border-radius: 8px; border: 2px solid transparent; background: rgba(255,255,255,0.03); transition: all 0.2s;">
              <input type="radio" v-model="form.server_type" value="external_agent" style="display: none;" />
              <div>
                <div style="font-weight: 600; color: #fff; font-size: 15px; margin-bottom: 4px;">Remote Agent</div>
                <div style="font-size: 13px; color: #94a3b8; line-height: 1.4;">Single-file agent PHP bridge over HTTPS. Good for remote servers.</div>
              </div>
            </label>
            <label class="option-card" :class="{ selected: form.server_type === 'ftp' }" style="cursor: pointer; padding: 16px; border-radius: 8px; border: 2px solid transparent; background: rgba(255,255,255,0.03); transition: all 0.2s;">
              <input type="radio" v-model="form.server_type" value="ftp" style="display: none;" />
              <div>
                <div style="font-weight: 600; color: #fff; font-size: 15px; margin-bottom: 4px;">FTP / SFTP Bridge</div>
                <div style="font-size: 13px; color: #94a3b8; line-height: 1.4;">Remote log monitoring via FTP credentials. Useful for shared hosting.</div>
              </div>
            </label>
          </div>

          <!-- Same Server Fields -->
          <div v-if="form.server_type === 'same_server'" style="background: rgba(255,255,255,0.02); padding: 24px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.05);">
            <div style="margin-bottom: 20px; font-weight: 600; color: #e2e8f0; font-size: 15px;">Local File Access Configuration</div>
            <div class="grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
              <div class="form-group">
                <label class="form-label">Absolute Root Path *</label>
                <input type="text" v-model="form.server_path" class="form-control" placeholder="/var/www/virtual/domain.com/htdocs" />
                <div class="form-text" style="font-size: 12px; color: #94a3b8; margin-top: 4px;">The absolute system path to your project's root folder.</div>
              </div>
              <div class="form-group">
                <label class="form-label">Relative Log Path</label>
                <div style="display:flex;gap:12px; align-items: flex-start;">
                  <div style="flex: 1;">
                    <input type="text" v-model="form.log_path" class="form-control" placeholder="storage/logs/laravel.log" />
                    <div class="form-text" style="font-size: 12px; color: #94a3b8; margin-top: 4px;">Relative to the Root Path above.</div>
                  </div>
                  <button type="button" class="btn btn-secondary" @click="autoDetectLogPath" :disabled="autoDetecting || !form.server_path" style="white-space:nowrap; padding: 10px 20px; height: 42px;">
                    <span v-if="autoDetecting" class="spinner-sm" style="margin-right:6px;"></span>
                    <span>🔍 Auto-Detect</span>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Agent Fields -->
          <div v-if="form.server_type === 'external_agent'" style="background: rgba(255,255,255,0.02); padding: 24px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.05);">
            <div style="margin-bottom: 20px; font-weight: 600; color: #e2e8f0; font-size: 15px;">Remote Agent Configuration</div>
            <div class="grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
              <div class="form-group">
                <label class="form-label">Agent Public URL *</label>
                <input type="url" v-model="form.agent_url" class="form-control" placeholder="https://example.com/agent.php" />
                <div class="form-text" style="font-size: 12px; color: #94a3b8; margin-top: 4px;">The public URL where the agent script is accessible.</div>
              </div>
              <div class="form-group">
                <label class="form-label">Agent Secret Key</label>
                <div style="display:flex;gap:12px;">
                  <input type="text" v-model="form.agent_secret" class="form-control" readonly />
                  <button type="button" class="btn btn-secondary" @click="generateSecret">Regenerate</button>
                </div>
                <div class="form-text" style="font-size: 12px; color: #94a3b8; margin-top: 4px;">Used to authenticate requests to the agent.</div>
              </div>
            </div>

            <div class="form-group" style="margin-top: 20px;">
              <label class="form-label">Target Log File Path</label>
              <input type="text" v-model="form.log_path" class="form-control" placeholder="/var/www/virtual/kunnatta.is/logs/verk.kunnatta.is/error.log" />
              <div class="form-text" style="font-size: 12px; color: #94a3b8; margin-top: 4px;">Specify the absolute path (e.g. /var/www/virtual/kunnatta.is/logs/verk.kunnatta.is/error.log) or relative path to the log file on the target server.</div>
            </div>

            <div style="margin-top: 24px; padding: 16px; background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.2); border-radius: 8px;">
              <h4 style="color: #60a5fa; margin-bottom: 8px; font-size: 14px; font-weight: 600;">📥 Need the Agent File?</h4>
              <p style="color: #cbd5e1; font-size: 13px; line-height: 1.5; margin-bottom: 16px;">
                Download the pre-configured <code style="background: rgba(0,0,0,0.3); padding: 2px 6px; border-radius: 4px; color: #fff;">agent.php</code> script. Upload this file to the <strong>public</strong> directory of your target application (e.g., <code>public/agent.php</code>). It securely streams your logs directly to Vakt.
              </p>
              <button type="button" @click="downloadAgentScript" class="btn btn-primary" style="background: #3b82f6; color: white; border: none;">
                Download agent.php
              </button>
            </div>
          </div>

          <!-- FTP Fields -->
          <div v-if="form.server_type === 'ftp'" style="background: rgba(255,255,255,0.02); padding: 24px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.05);">
            <div style="margin-bottom: 20px; font-weight: 600; color: #e2e8f0; font-size: 15px;">FTP Connection Credentials</div>
            <div class="grid-3" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 24px;">
              <div class="form-group">
                <label class="form-label">FTP Host *</label>
                <input type="text" v-model="form.ftp_host" class="form-control" placeholder="ftp.example.com" />
              </div>
              <div class="form-group">
                <label class="form-label">FTP Username *</label>
                <input type="text" v-model="form.ftp_user" class="form-control" placeholder="username" />
              </div>
              <div class="form-group">
                <label class="form-label">FTP Password</label>
                <input type="password" v-model="form.ftp_password" class="form-control" placeholder="Leave blank to keep current" />
              </div>
            </div>
            <div class="grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-top: 24px;">
              <div class="form-group">
                <label class="form-label">Absolute Root Path (FTP) *</label>
                <input type="text" v-model="form.server_path" class="form-control" placeholder="/htdocs" />
                <div class="form-text" style="font-size: 12px; color: #94a3b8; margin-top: 4px;">The path from the FTP user's root directory.</div>
              </div>
              <div class="form-group">
                <label class="form-label">Relative Log Path</label>
                <input type="text" v-model="form.log_path" class="form-control" placeholder="storage/logs/laravel.log" />
              </div>
            </div>
          </div>

          <!-- Diagnostics -->
          <div style="margin-top:30px; padding-top:24px; border-top:1px dashed rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:space-between;">
            <div>
              <button type="button" class="btn btn-secondary" @click="testConnection" :disabled="runningDiagnostics" style="background: rgba(59, 130, 246, 0.1); border-color: rgba(59, 130, 246, 0.3); color: #60a5fa; padding: 10px 20px;">
                <span v-if="runningDiagnostics" class="spinner-sm" style="margin-right:8px;"></span>
                <span v-else style="margin-right:8px;">⚡</span>
                {{ runningDiagnostics ? 'Testing Connection...' : 'Test Connection & Run Diagnostics' }}
              </button>
              <div class="form-text" style="font-size: 12px; color: #94a3b8; margin-top: 8px;">Run a real-time connection check to verify your server settings before saving.</div>
            </div>
            <span v-if="diagnosticStatus" style="padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: 600;" :style="{ background: diagnosticStatus === 'ready' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)', color: diagnosticStatus === 'ready' ? '#34d399' : '#f87171' }">
              {{ diagnosticStatus === 'ready' ? '✅ Connection Passed' : '❌ Issues Found' }}
            </span>
          </div>

          <div v-if="diagnosticResults.length" style="margin-top:24px; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.05); border-radius: 8px; overflow: hidden;">
            <div v-for="(res, index) in diagnosticResults" :key="index" style="display:flex; align-items:center; justify-content:space-between; padding: 14px 20px; border-bottom: 1px solid rgba(255,255,255,0.05);">
              <div style="display: flex; align-items: center;">
                <span style="font-size: 16px;">{{ res.icon }}</span>
                <strong style="margin-left: 12px; color: #fff; font-size: 14px;">{{ res.name }}</strong>
                <span style="color: #94a3b8; font-size: 13px; margin-left: 16px;">{{ res.value }}</span>
              </div>
              <div v-if="!res.pass" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); padding: 4px 10px; border-radius: 4px;">
                <span style="font-size: 12px; color: #fca5a5;">{{ res.fix }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Section 3: Alerts -->
      <div class="card mb-6" style="border-radius: 12px; overflow: hidden; border: 1px solid rgba(255,255,255,0.1);">
        <div class="card-header" style="background: rgba(0,0,0,0.2); padding: 16px 24px; border-bottom: 1px solid rgba(255,255,255,0.05);">
          <div class="card-title" style="margin: 0; font-size: 18px; display: flex; align-items: center; gap: 8px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
            3. Notifications
          </div>
        </div>
        <div class="card-body" style="padding: 24px;">
          <div class="grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
            <div class="form-group">
              <label class="form-label">Alert Email</label>
              <input type="email" v-model="form.alert_email" class="form-control" placeholder="admin@example.com" />
              <div class="form-text" style="font-size: 12px; color: #94a3b8; margin-top: 4px;">Receive critical downtime alerts and daily security briefings.</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Submit -->
      <div style="display:flex; gap: 16px; justify-content: flex-end; margin-top: 30px; margin-bottom: 50px;">
        <Link :href="route('projects.index')" class="btn btn-secondary" style="padding: 12px 24px; font-weight: 500;">Cancel</Link>
        <button type="submit" class="btn btn-primary" :disabled="submitting" style="padding: 12px 32px; font-weight: 600; font-size: 16px;">
          <span v-if="submitting" class="spinner-sm" style="margin-right: 8px;"></span>
          <span>{{ isEdit ? 'Save Project' : 'Create Project' }}</span>
        </button>
      </div>

    </form>
  </div>
</template>

<script setup>
import { ref, reactive, toRaw, watch } from 'vue';
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
  ftp_host: props.project.ftp_host || '',
  ftp_user: props.project.ftp_user || '',
  ftp_password: '', // Kept empty for security, only sent if changed
  alert_email: props.project.alert_email || '',
});

function generateServerPath(domainStr) {
    if (!domainStr) return '';
    let parts = domainStr.split('/');
    let host = parts.shift();
    let path = `/var/www/virtual/${host}/htdocs`;
    if (parts.length > 0) {
        path += '/' + parts.join('/');
    }
    return path;
}

watch(() => form.domain, (newDomain, oldDomain) => {
  if (typeof newDomain !== 'string') return;
  
  // Clean domain of protocols and www
  let clean = newDomain.replace(/^https?:\/\//i, '').replace(/^www\./i, '');
  if (clean !== newDomain) {
    form.domain = clean;
    return; // Watcher will re-trigger with cleaned value
  }
  
  // Predict old path to know if the user kept the auto-generated one
  let oldPath = '';
  if (oldDomain) {
    oldPath = generateServerPath(oldDomain.replace(/^https?:\/\//i, '').replace(/^www\./i, ''));
  }

  // If path is empty, or it perfectly matched the OLD domain, we can safely overwrite it with the NEW domain
  if (!form.server_path || form.server_path === oldPath) {
    if (form.server_type === 'same_server' || form.server_type === 'ftp') {
        form.server_path = generateServerPath(clean);
    }
  }
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

function downloadAgentScript() {
  const secret = form.agent_secret || 'MISSING_SECRET_KEY';
  const customLogPath = form.log_path || '../storage/logs/laravel.log';
  
  const content = `<?php
// agent.php - Vakt SOC External Agent
// Place this in your project's public/ folder

$secret = '${secret}';
$configuredPath = '${customLogPath}';

// Resolve primary configured path
$primaryPath = (strpos($configuredPath, '/') === 0 || strpos($configuredPath, ':\\\\') === 1)
    ? $configuredPath
    : __DIR__ . '/' . ltrim($configuredPath, '/');

// Fallback search order (security: all paths resolved locally, no HTTP header overrides)
$candidatePaths = [
    $primaryPath,
    __DIR__ . '/vakt-logs/error.log',
    __DIR__ . '/logs/php-error.log',
];

header('Content-Type: application/json');

$providedKey = $_SERVER['HTTP_X_SOC_KEY'] ?? '';
if ($providedKey !== $secret) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$logPath = null;
foreach ($candidatePaths as $candidate) {
    if (@file_exists($candidate) && @is_readable($candidate)) {
        $logPath = $candidate;
        break;
    }
}

if (!$logPath) {
    http_response_code(404);
    $ob = ini_get('open_basedir');
    $errMsg = "Log file not found. Tried: " . implode(' | ', $candidatePaths);
    if ($ob) {
        $errMsg .= " | open_basedir active ({$ob})";
    }
    echo json_encode(['error' => $errMsg]);
    exit;
}

$logTail = [];
if (file_exists($logPath)) {
    $size = filesize($logPath);
    $maxBytes = 524288; // Read max 500KB from end
    $offset = max(0, $size - $maxBytes);
    
    $content = file_get_contents($logPath, false, null, $offset);
    if ($content) {
        $lines = preg_split('/\\r\\n|\\r|\\n/', trim($content));
        $logTail = array_values(array_filter(array_slice($lines, -1000)));
    }
}

echo json_encode([
    'status' => 'success',
    'log_tail' => $logTail,
    'system_metrics' => [
        'disk_free' => @disk_free_space(__DIR__),
        'disk_total' => @disk_total_space(__DIR__)
    ]
]);
`;
  const blob = new Blob([content], { type: 'application/x-httpd-php' });
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = 'agent.php';
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  window.URL.revokeObjectURL(url);
}

async function submitForm() {
  submitting.value = true;
  errors.value = [];
  successMessage.value = '';

  // Clean domain right before submitting just in case
  form.domain = form.domain.replace(/^https?:\/\//i, '').replace(/^www\./i, '');

  const raw = JSON.parse(JSON.stringify(toRaw(form)));
  raw.monitoring_interval_minutes = parseInt(raw.monitoring_interval_minutes, 10);
  
  if (raw.server_type === 'ftp' && !raw.ftp_password) {
    delete raw.ftp_password;
  }
  
  if (props.isEdit) {
    raw._method = 'PUT';
  }

  const url = props.isEdit ? window.route('projects.update', props.project.id) : window.route('projects.store');

  router.post(url, raw, {
    preserveScroll: true,
    onError: (errs) => {
      errors.value = Object.values(errs);
    },
    onFinish: () => {
      submitting.value = false;
    }
  });
}

async function autoDetectLogPath() {
  if (!form.server_path) {
    alert("Please enter the Absolute Root Path first.");
    return;
  }
  autoDetecting.value = true;
  errors.value = [];
  try {
    const res = await axios.post(window.route('projects.auto-detect-log'), {
      server_path: form.server_path,
      domain: form.domain
    }, {
      headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' }
    });
    
    const data = res.data;
    if (data.success) {
      form.log_path = data.log_path;
      if (window.dispatchEvent) {
        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', title: 'Auto-Detect', message: 'Log path found successfully.' } }));
      }
    } else {
      if (window.dispatchEvent) {
        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', title: 'Auto-Detect', message: data.message } }));
      }
    }
  } catch (err) {
    console.error('Auto detect error', err);
    if (window.dispatchEvent) {
      window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', title: 'Error', message: 'Failed to run auto-detect.' } }));
    }
  } finally {
    autoDetecting.value = false;
  }
}

async function testConnection() {
  runningDiagnostics.value = true;
  diagnosticStatus.value = null;
  diagnosticResults.value = [];
  
  try {
    const payload = {
      server_type: form.server_type,
      server_path: form.server_path,
      log_path: form.log_path,
      agent_url: form.agent_url,
      agent_secret: form.agent_secret,
      ftp_host: form.ftp_host,
      ftp_user: form.ftp_user,
      ftp_password: form.ftp_password
    };

    const res = await axios.post(window.route('projects.test-connection'), payload, {
      headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' }
    });
    
    const data = res.data;
    diagnosticStatus.value = data.status;
    diagnosticResults.value = data.results || [];
    
    if (data.status === 'ready') {
      window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', title: 'Diagnostics Passed', message: 'Connection verified.' } }));
    } else {
      window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'warning', title: 'Issues Found', message: 'Check the diagnostic results.' } }));
    }
  } catch (err) {
    diagnosticStatus.value = 'failed';
    if (err.response && err.response.data && err.response.data.results) {
        diagnosticResults.value = err.response.data.results;
    } else {
        diagnosticResults.value = [{ icon: '❌', name: 'Internal Server Error', value: 'API Error', pass: false, fix: 'Check your server or network connection' }];
    }
    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', title: 'Diagnostics Failed', message: 'An internal error occurred.' } }));
  } finally {
    runningDiagnostics.value = false;
  }
}
</script>

<style scoped>
.option-card.selected {
  border-color: #3b82f6 !important;
  background: rgba(59, 130, 246, 0.1) !important;
}
.option-card:hover {
  background: rgba(255, 255, 255, 0.05);
}
</style>
