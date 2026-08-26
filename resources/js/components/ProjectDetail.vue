<template>
  <div>
    <div class="breadcrumbs">
      <a href="/dashboard">Dashboard</a>
      <span class="sep">›</span>
      <a href="/projects">Projects</a>
      <span class="sep">›</span>
      <span class="current">{{ project.domain }}</span>
    </div>

    <div class="card mb-6" :style="{ borderTop: '4px solid var(--color-' + getScoreColor(project.security_score) + ')' }">
      <div class="card-body">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px">
          <div>
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px">
              <h1 style="margin:0;font-size:1.5rem">{{ project.name }}</h1>
              <span class="badge muted">{{ project.stack }}</span>
              <span class="project-status-indicator" :class="project.active ? project.status : 'unknown'" title="Status"></span>
            </div>
            <div style="font-family:var(--font-mono);color:var(--color-primary);margin-bottom:12px">{{ project.domain }}</div>
            <div style="color:var(--color-text-dim);font-size:0.875rem">{{ project.description || 'No description provided.' }}</div>
          </div>
          
          <div style="display:flex;gap:12px">
            <a :href="'/projects/' + project.id + '/report/monthly'" target="_blank" class="btn" style="background: rgba(139, 92, 246, 0.1); color: #a78bfa; border: 1px solid rgba(139, 92, 246, 0.4);">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px;margin-right:6px">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              Monthly Report
            </a>
            <button @click="runScan" class="btn btn-primary" :disabled="isScanning">
              <span v-if="isScanning" class="spinner-sm"></span>
              <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px;margin-right:6px">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              {{ isScanning ? 'Scanning...' : 'Run Scan Now' }}
            </button>
            <button @click="sendTestReport" class="btn btn-ghost" :disabled="isTestingReport">
              <span v-if="isTestingReport" class="spinner-sm"></span>
              <span v-else>Test Daily Report</span>
            </button>
            <Link :href="route('projects.edit', project.id)" class="btn btn-ghost">Edit Settings</Link>
          </div>
        </div>
      </div>
      <div class="card-footer" style="background:var(--color-surface-2);display:flex;gap:32px;flex-wrap:wrap">
        <div>
          <div style="font-size:0.7rem;text-transform:uppercase;color:var(--color-muted);margin-bottom:4px">Security Score</div>
          <div style="font-family:var(--font-display);font-size:1.4rem;font-weight:700;" :style="{ color: 'var(--color-' + getScoreColor(project.security_score) + ')' }">{{ project.security_score }}</div>
        </div>
        <div>
          <div style="font-size:0.7rem;text-transform:uppercase;color:var(--color-muted);margin-bottom:4px">Open Incidents</div>
          <div style="font-family:var(--font-display);font-size:1.4rem;font-weight:700;" :style="{ color: openIncidentsCount > 0 ? 'var(--color-danger)' : 'var(--color-success)' }">{{ openIncidentsCount }}</div>
        </div>
        <div>
          <div style="font-size:0.7rem;text-transform:uppercase;color:var(--color-muted);margin-bottom:4px">Server Type</div>
          <div style="font-size:1rem;font-weight:500;text-transform:capitalize;">{{ project.server_type.replace('_', ' ') }}</div>
        </div>
        <div>
          <div style="font-size:0.7rem;text-transform:uppercase;color:var(--color-muted);margin-bottom:4px">Last Checked</div>
          <div style="font-size:1rem;font-weight:500">{{ formatDiffForHumans(project.last_checked_at) }}</div>
        </div>
      </div>
    </div>

    <!-- Firewall Alert -->
    <div v-if="project.server_type === 'external_agent' && !project.firewall_whitelist_confirmed" class="alert alert-warning mb-6" style="background: rgba(245, 158, 11, 0.1); border: 1px solid var(--color-warning); color: #fcd34d;">
      <div style="width:100%">
        <div style="margin-bottom:8px">
          <strong>FIREWALL WHITELIST REQUIRED:</strong> To prevent this SOC server from being blocked, you MUST whitelist our IP on the server's firewall (e.g. CSF) BEFORE deploying the agent.
        </div>
        <div style="display: flex; gap: 12px; margin-top: 12px;">
          <button @click="confirmWhitelist" class="btn btn-primary btn-sm" :disabled="isConfirmingWhitelist">
            <span v-if="isConfirmingWhitelist" class="spinner-sm"></span>
            <span v-else>I have whitelisted the IP</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Agent Setup Alert -->
    <div v-if="project.server_type === 'external_agent' && !project.last_checked_at && project.firewall_whitelist_confirmed" class="alert alert-info mb-6">
      <div style="width:100%">
        <div style="margin-bottom:8px">
          <strong>Agent Setup Required:</strong> Download the <a :href="'/projects/' + project.id + '/agent-download'" class="text-primary" style="text-decoration:underline">Agent Script Template</a>, upload it to the root of <b>{{ project.domain }}</b>. Ensure it is accessible at the URL you provided.
        </div>
      </div>
    </div>

    <!-- Uptime & System Metrics -->
    <div v-if="uptimeLogs.length > 0 || (latestReport && latestReport.payload && latestReport.payload.system_metrics)" class="card mb-6">
      <div class="card-header">
        <div class="card-title">System Health & Uptime</div>
      </div>
      <div class="card-body" style="display:flex; gap: 32px; flex-wrap: wrap;">
        
        <div style="flex: 1; min-width: 250px;">
          <div style="font-size:0.75rem; text-transform:uppercase; color:var(--color-muted); margin-bottom:8px;">Recent Uptime (Last Hour)</div>
          <div style="display: flex; gap: 4px; align-items: flex-end; height: 40px;">
            <div v-for="log in recentUptimeLogs" :key="log.id" style="flex: 1; border-radius: 2px;" :style="{ background: log.status_code == 200 ? 'var(--color-success)' : 'var(--color-danger)', height: log.status_code == 200 ? '100%' : '30%' }" :title="log.created_at + ' - HTTP ' + (log.status_code || 'Error')"></div>
          </div>
          <div style="display: flex; justify-content: space-between; margin-top: 4px; font-size: 0.7rem; color: var(--color-muted);">
            <span>30m ago</span>
            <span>Now</span>
          </div>
        </div>

        <div v-if="latestReport && latestReport.payload && latestReport.payload.system_metrics" style="flex: 1; min-width: 250px;">
          <div style="font-size:0.75rem; text-transform:uppercase; color:var(--color-muted); margin-bottom:8px;">Server Resources</div>
          
          <div style="margin-bottom: 12px;">
            <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 4px;">
              <span>Disk Usage</span>
              <span>{{ diskUsedPct.toFixed(1) }}%</span>
            </div>
            <div style="height: 6px; background: var(--color-surface-2); border-radius: 3px; overflow: hidden;">
              <div style="height: 100%;" :style="{ width: Math.min(diskUsedPct, 100) + '%', background: diskUsedPct > 90 ? 'var(--color-danger)' : 'var(--color-primary)' }"></div>
            </div>
          </div>

          <div v-if="latestReport.payload.system_metrics.memory_total_mb">
            <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 4px;">
              <span>Memory Usage</span>
              <span>{{ memUsedPct.toFixed(1) }}% ({{ latestReport.payload.system_metrics.memory_total_mb.toLocaleString() }} MB)</span>
            </div>
            <div style="height: 6px; background: var(--color-surface-2); border-radius: 3px; overflow: hidden;">
              <div style="height: 100%;" :style="{ width: Math.min(memUsedPct, 100) + '%', background: memUsedPct > 85 ? 'var(--color-warning)' : 'var(--color-success)' }"></div>
            </div>
          </div>
        </div>

        <div v-if="latestReport && latestReport.payload && latestReport.payload.backup_status" style="flex: 1; min-width: 200px;">
          <div style="font-size:0.75rem; text-transform:uppercase; color:var(--color-muted); margin-bottom:8px;">Backup Validation</div>
          <div style="display:flex;align-items:center;gap:12px;">
            <template v-if="latestReport.payload.backup_status.healthy">
              <div style="color:var(--color-success);">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:32px;height:32px;">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div>
                <div style="font-weight:600;font-size:0.9rem;">Verified</div>
                <div style="font-size:0.7rem;color:var(--color-muted)">{{ formatTimestampForHumans(latestReport.payload.backup_status.latest_time) }}</div>
              </div>
            </template>
            <template v-else>
              <div style="color:var(--color-danger);">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:32px;height:32px;">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
              </div>
              <div>
                <div style="font-weight:600;font-size:0.9rem;">Missing / Failed</div>
                <div style="font-size:0.7rem;color:var(--color-muted)">Over 24h old</div>
              </div>
            </template>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-2 gap-6">
      <!-- Recent Incidents -->
      <div class="card">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
          <div class="card-title">Recent Incidents</div>
          <a :href="'/incidents?project=' + project.id" class="btn btn-ghost btn-sm">View All</a>
        </div>
        <div class="card-body p-0">
          <div v-if="!project.incidents || project.incidents.length === 0" style="padding:24px;text-align:center;color:var(--color-muted)">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:40px;height:40px;margin:0 auto 12px;opacity:0.5">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <div style="font-weight:500;color:#fff;">No incidents</div>
            <div style="font-size:13px;margin-top:4px;">Clean record.</div>
          </div>
          <div v-else class="table-wrapper" style="border:none; border-radius:0;">
            <table class="table" style="width:100%; border-collapse:collapse;">
              <tbody>
                <tr v-for="incident in project.incidents" :key="incident.id" style="border-bottom:1px solid #334155;">
                  <td style="padding:12px;width:1%"><span class="badge" :class="getBadgeClass(incident.severity)">{{ getSeverityLabel(incident.severity) }}</span></td>
                  <td style="padding:12px;">
                    <a :href="'/incidents/' + incident.id" style="color:var(--color-text);text-decoration:none">{{ incident.title }}</a>
                    <div style="font-size:0.75rem;color:var(--color-muted)">{{ formatDiffForHumans(incident.detected_at) }}</div>
                  </td>
                  <td style="padding:12px;text-align:right;">
                    <span class="badge" :class="getIncidentStatusClass(incident.status)">
                      {{ incident.status.replace('_', ' ') }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div>
        <!-- Monitoring History -->
        <div class="card mb-6">
          <div class="card-header">
            <div class="card-title">Recent Health Checks</div>
          </div>
          <div class="card-body p-0">
            <div v-if="!project.monitoring_checks || project.monitoring_checks.length === 0" style="padding:24px;text-align:center;color:var(--color-muted)">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:40px;height:40px;margin:0 auto 12px;opacity:0.5">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <div style="font-weight:500;color:#fff;">No checks yet</div>
              <div style="font-size:13px;margin-top:4px;">Monitoring history will appear here.</div>
            </div>
            <div v-else class="table-wrapper" style="border:none; border-radius:0;">
              <table class="table" style="width:100%; border-collapse:collapse; text-align:left;">
                <thead>
                  <tr style="border-bottom:1px solid #334155;">
                    <th style="padding:12px;color:#94a3b8;font-size:12px;">Time</th>
                    <th style="padding:12px;color:#94a3b8;font-size:12px;">Status</th>
                    <th style="padding:12px;color:#94a3b8;font-size:12px;">Scanned</th>
                    <th style="padding:12px;color:#94a3b8;font-size:12px;">Errors</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="check in project.monitoring_checks" :key="check.id" style="border-bottom:1px solid #334155;">
                    <td style="padding:12px;font-family:var(--font-mono);font-size:12px;color:var(--color-muted);">{{ formatDateShort(check.checked_at) }}</td>
                    <td style="padding:12px;display:flex;align-items:center;gap:6px;">
                      <span class="project-status-indicator" :class="check.status"></span>
                      {{ check.status.charAt(0).toUpperCase() + check.status.slice(1) }}
                    </td>
                    <td style="padding:12px;font-family:var(--font-mono);font-size:12px;">{{ check.log_lines_scanned.toLocaleString() }}</td>
                    <td style="padding:12px;" :class="check.errors_found > 0 ? 'text-danger font-bold' : ''">{{ check.errors_found }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        
        <!-- Latest Logs -->
        <div class="card">
          <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
            <div class="card-title">Latest Logs</div>
            <a :href="'/logs?project=' + project.id" class="btn btn-ghost btn-sm">View Full Log</a>
          </div>
          <div class="card-body p-0">
            <div v-if="!project.log_entries || project.log_entries.length === 0" style="padding:24px;text-align:center;color:var(--color-muted)">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:40px;height:40px;margin:0 auto 12px;opacity:0.5">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <div style="font-weight:500;color:#fff;">No logs captured</div>
            </div>
            <div v-else class="table-wrapper" style="border:none; border-radius:0;">
              <table class="table" style="width:100%; border-collapse:collapse;">
                <tbody>
                  <tr v-for="log in project.log_entries" :key="log.id" style="border-bottom:1px solid #334155;">
                    <td style="padding:12px;width:1%">
                      <span class="badge" :class="['error','critical'].includes(log.level) ? 'danger' : (log.level === 'warning' ? 'warning' : 'info')">{{ log.level.toUpperCase() }}</span>
                    </td>
                    <td style="padding:12px;">
                      <div style="font-family:var(--font-mono);font-size:0.75rem;color:var(--color-text-dim);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:250px" :title="log.message">
                        {{ log.message }}
                      </div>
                    </td>
                    <td style="padding:12px;text-align:right;font-family:var(--font-mono);font-size:12px;color:var(--color-muted);">
                      {{ formatTimeOnly(log.occurred_at) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
  project: { type: Object, required: true },
  latestReport: { type: Object, default: null },
  uptimeLogs: { type: Array, default: () => [] },
});

const isScanning = ref(false);
const isTestingReport = ref(false);
const isConfirmingWhitelist = ref(false);

const project = ref(props.project);

const openIncidentsCount = computed(() => {
  if (!project.value.incidents) return 0;
  return project.value.incidents.filter(i => !['resolved', 'closed'].includes(i.status)).length;
});

const recentUptimeLogs = computed(() => {
  return [...props.uptimeLogs].slice(0, 30).reverse();
});

const diskUsedPct = computed(() => {
  if (!props.latestReport || !props.latestReport.payload || !props.latestReport.payload.system_metrics) return 0;
  const m = props.latestReport.payload.system_metrics;
  const free = m.disk_free_bytes || 0;
  const total = m.disk_total_bytes || 1;
  return total > 0 ? ((total - free) / total) * 100 : 0;
});

const memUsedPct = computed(() => {
  if (!props.latestReport || !props.latestReport.payload || !props.latestReport.payload.system_metrics) return 0;
  const m = props.latestReport.payload.system_metrics;
  const free = m.memory_free_mb || 0;
  const total = m.memory_total_mb || 1;
  return total > 0 ? ((total - free) / total) * 100 : 0;
});

function getScoreColor(score) {
  if (score >= 80) return 'primary';
  if (score >= 60) return 'warning';
  return 'danger';
}

function getBadgeClass(severity) {
  const map = { p1: 'critical', p2: 'warning', p3: 'info', p4: 'success' };
  return map[severity] || 'muted';
}

function getSeverityLabel(severity) {
  const labels = { p1: 'Critical', p2: 'High', p3: 'Medium', p4: 'Low' };
  return labels[severity] || severity;
}

function getIncidentStatusClass(status) {
  const map = { 'open':'danger', 'investigating':'warning', 'contained':'info', 'resolved':'success', 'closed':'success' };
  return map[status] || 'muted';
}

function formatDiffForHumans(dateString) {
  if (!dateString) return 'Never';
  const date = new Date(dateString);
  const diffInSeconds = Math.floor((new Date() - date) / 1000);
  if (diffInSeconds < 60) return 'Just now';
  if (diffInSeconds < 3600) return Math.floor(diffInSeconds / 60) + ' min ago';
  if (diffInSeconds < 86400) return Math.floor(diffInSeconds / 3600) + ' hrs ago';
  return Math.floor(diffInSeconds / 86400) + ' days ago';
}

function formatTimestampForHumans(ts) {
  if (!ts) return 'Unknown';
  return formatDiffForHumans(new Date(ts * 1000).toISOString());
}

function formatDateShort(dateString) {
  if (!dateString) return '';
  const d = new Date(dateString);
  const month = d.toLocaleString('en-US', { month: 'short' });
  const day = d.getDate().toString().padStart(2, '0');
  const hours = d.getHours().toString().padStart(2, '0');
  const mins = d.getMinutes().toString().padStart(2, '0');
  const secs = d.getSeconds().toString().padStart(2, '0');
  return `${month} ${day}, ${hours}:${mins}:${secs}`;
}

function formatTimeOnly(dateString) {
  if (!dateString) return '';
  const d = new Date(dateString);
  const hours = d.getHours().toString().padStart(2, '0');
  const mins = d.getMinutes().toString().padStart(2, '0');
  const secs = d.getSeconds().toString().padStart(2, '0');
  return `${hours}:${mins}:${secs}`;
}

async function runScan() {
  isScanning.value = true;
  try {
    const res = await axios.post(window.route('projects.run-scan', project.value.id));
    const data = res.data;
    if (window.dispatchEvent) {
      window.dispatchEvent(new CustomEvent('toast', { detail: { type: data.success ? 'success' : 'error', title: 'Scan', message: data.message } }));
    }
    // Reload Inertia page props so new logs, last_checked_at, and health checks appear instantly!
    router.reload({ preserveScroll: true });
  } catch (e) {
    console.error(e);
  } finally {
    isScanning.value = false;
  }
}

async function sendTestReport() {
  isTestingReport.value = true;
  try {
    const res = await axios.post(window.route('projects.test-report', project.value.id));
    const data = res.data;
    if (window.dispatchEvent) {
      window.dispatchEvent(new CustomEvent('toast', { detail: { type: data.success ? 'success' : 'error', title: 'Report', message: data.message } }));
    }
  } catch (e) {
    console.error(e);
  } finally {
    isTestingReport.value = false;
  }
}

async function confirmWhitelist() {
  isConfirmingWhitelist.value = true;
  try {
    const res = await axios.post(window.route('projects.confirm-whitelist', project.value.id));
    const data = res.data;
    if (data.success) {
      project.value.firewall_whitelist_confirmed = true;
      if (window.dispatchEvent) {
        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', title: 'Whitelisted', message: data.message } }));
      }
    }
  } catch (e) {
    console.error(e);
  } finally {
    isConfirmingWhitelist.value = false;
  }
}
</script>

<style scoped>
.text-danger { color: var(--color-danger); }
.font-bold { font-weight: bold; }
</style>
