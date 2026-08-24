<template>
  <div>
    <!-- Page Header -->
    <div class="page-header" style="margin-bottom: 24px; display: flex; align-items: center; gap: 16px;">
      <div style="width: 48px; height: 48px; background: rgba(59,130,246,0.1); border: 1px solid rgba(59,130,246,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #3b82f6;">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:24px;height:24px">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
        </svg>
      </div>
      <div>
        <h1 style="font-size: 24px; font-weight: 600; color: #f8fafc; margin: 0;">File Integrity Monitor</h1>
        <p style="color: #94a3b8; font-size: 14px; margin: 4px 0 0;">Detect unauthorized file changes on same-server projects</p>
      </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-4 gap-6 mb-6" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
      <div class="card">
        <div class="card-body" style="padding:16px;">
          <div style="font-size:0.75rem; text-transform:uppercase; color:var(--color-muted); margin-bottom:4px;">Total Files</div>
          <div style="font-size:1.8rem; font-family:var(--font-display); font-weight:700; color:var(--color-primary);">{{ formatNumber(stats.total) }}</div>
        </div>
      </div>
      <div class="card">
        <div class="card-body" style="padding:16px;">
          <div style="font-size:0.75rem; text-transform:uppercase; color:var(--color-muted); margin-bottom:4px;">Suspicious</div>
          <div style="font-size:1.8rem; font-family:var(--font-display); font-weight:700;" :style="{ color: stats.suspicious > 0 ? 'var(--color-danger)' : 'var(--color-success)' }">{{ formatNumber(stats.suspicious) }}</div>
        </div>
      </div>
      <div class="card">
        <div class="card-body" style="padding:16px;">
          <div style="font-size:0.75rem; text-transform:uppercase; color:var(--color-muted); margin-bottom:4px;">Changed</div>
          <div style="font-size:1.8rem; font-family:var(--font-display); font-weight:700;" :style="{ color: stats.changed > 0 ? 'var(--color-warning)' : 'var(--color-success)' }">{{ formatNumber(stats.changed) }}</div>
        </div>
      </div>
      <div class="card">
        <div class="card-body" style="padding:16px;">
          <div style="font-size:0.75rem; text-transform:uppercase; color:var(--color-muted); margin-bottom:4px;">Clean</div>
          <div style="font-size:1.8rem; font-family:var(--font-display); font-weight:700; color:var(--color-success);">{{ formatNumber(stats.clean) }}</div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="card" style="margin-bottom: 24px;">
      <div class="card-body" style="padding: 16px 20px; display: flex; gap: 12px; align-items: center; flex-wrap: wrap; justify-content: space-between;">
        <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
          <select v-model="projectId" @change="fetchSnapshots(1)" class="form-control" style="width:200px">
            <option value="">Select a Project...</option>
            <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.domain }}</option>
          </select>
          <select v-model="filterStatus" @change="fetchSnapshots(1)" class="form-control" style="width:150px">
            <option value="">All Statuses</option>
            <option value="clean">Clean</option>
            <option value="changed">Changed</option>
            <option value="new">New</option>
            <option value="suspicious">Suspicious</option>
            <option value="deleted">Deleted</option>
          </select>
        </div>
        
        <button @click="initScan" class="btn btn-primary" :disabled="!projectId || isScanning">
          <span v-if="isScanning" class="spinner-sm" style="margin-right: 6px;"></span>
          {{ isScanning ? 'Scanning...' : 'Run Integrity Scan' }}
        </button>
      </div>
    </div>

    <!-- Results -->
    <div class="card" :class="{'opacity-50 pointer-events-none': loading}">
      <div class="card-body" style="padding: 0; overflow-x: auto;">
        <table v-if="projectId || filterStatus" class="table" style="width:100%; border-collapse:collapse; text-align:left;">
          <thead>
            <tr style="border-bottom:1px solid #334155; background:rgba(255,255,255,0.02);">
              <th style="padding:12px 20px; color:#94a3b8; font-size:12px; text-transform:uppercase; font-weight:600;">File Path</th>
              <th style="padding:12px 20px; color:#94a3b8; font-size:12px; text-transform:uppercase; font-weight:600;">Project</th>
              <th style="padding:12px 20px; color:#94a3b8; font-size:12px; text-transform:uppercase; font-weight:600;">Status</th>
              <th style="padding:12px 20px; color:#94a3b8; font-size:12px; text-transform:uppercase; font-weight:600;">Size</th>
              <th style="padding:12px 20px; color:#94a3b8; font-size:12px; text-transform:uppercase; font-weight:600;">Last Modified</th>
              <th style="padding:12px 20px; color:#94a3b8; font-size:12px; text-transform:uppercase; font-weight:600;">Changed At</th>
              <th style="padding:12px 20px; color:#94a3b8; font-size:12px; text-transform:uppercase; font-weight:600; text-align:right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="snapshots.length === 0" style="border-bottom:1px solid #334155;">
              <td colspan="7" style="padding:40px; text-align:center; color:#94a3b8;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:40px;height:40px;margin:0 auto 12px;opacity:0.5">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <div style="font-weight:500;color:#fff;">No files tracked</div>
                <div style="font-size:13px;margin-top:4px;">Try taking a snapshot to initialize baseline monitoring.</div>
              </td>
            </tr>
            <tr v-else v-for="file in snapshots" :key="file.id" :class="getRowClass(file.status)" style="border-bottom:1px solid #334155; transition:background .15s;">
              <td style="padding:12px 20px;">
                <div class="text-mono" style="font-size:0.82rem;word-break:break-all">{{ file.file_path }}</div>
                <div v-if="file.flagged_patterns && file.flagged_patterns.length > 0" style="display:flex;gap:4px;flex-wrap:wrap;margin-top:4px">
                  <span v-for="pattern in file.flagged_patterns" :key="pattern" class="badge danger" style="font-size:0.6rem;padding:2px 6px">{{ pattern }}</span>
                </div>
              </td>
              <td style="padding:12px 20px;">
                <span class="text-mono" style="font-size:0.8rem">{{ file.project ? file.project.domain : 'Unknown' }}</span>
              </td>
              <td style="padding:12px 20px;">
                <span class="badge" :class="getStatusBadgeClass(file.status)">{{ file.status.toUpperCase() }}</span>
              </td>
              <td style="padding:12px 20px;">
                <span class="text-mono text-muted text-sm">{{ (file.file_size / 1024).toFixed(2) }} KB</span>
              </td>
              <td style="padding:12px 20px;">
                <span class="text-mono text-muted text-sm">{{ formatDate(file.last_modified) }}</span>
              </td>
              <td style="padding:12px 20px;">
                <span class="text-mono text-muted text-sm">{{ formatDiffForHumans(file.changed_at) || '-' }}</span>
              </td>
              <td style="padding:12px 20px; text-align:right;">
                <button v-if="['suspicious', 'changed', 'new'].includes(file.status)" @click="approveChange(file)" class="btn btn-success btn-sm">Approve</button>
                <span v-else class="text-muted text-sm">—</span>
              </td>
            </tr>
          </tbody>
        </table>
        
        <div v-if="!projectId && !filterStatus" style="padding:40px; text-align:center; color:#94a3b8;">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:40px;height:40px;margin:0 auto 12px;opacity:0.5">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
          </svg>
          <div style="font-weight:500;color:#fff;">Select a project</div>
          <div style="font-size:13px;margin-top:4px;">Choose a same-server project above to view file integrity.</div>
        </div>
      </div>
      
      <!-- Pagination -->
      <div v-if="meta.last_page > 1 && (projectId || filterStatus)" class="card-footer" style="background:var(--color-surface-2); display:flex; justify-content:space-between; align-items:center;">
        <div style="font-size:13px; color:#94a3b8;">
          Showing {{ (meta.current_page - 1) * meta.per_page + 1 }} to {{ Math.min(meta.current_page * meta.per_page, meta.total) }} of {{ meta.total }} entries
        </div>
        <div style="display:flex; gap:4px;">
          <button @click="changePage(meta.current_page - 1)" :disabled="meta.current_page === 1" class="btn btn-sm btn-secondary">Prev</button>
          <button v-for="p in pagesArray" :key="p" @click="changePage(p)" class="btn btn-sm" :class="p === meta.current_page ? 'btn-primary' : 'btn-secondary'">{{ p }}</button>
          <button @click="changePage(meta.current_page + 1)" :disabled="meta.current_page === meta.last_page" class="btn btn-sm btn-secondary">Next</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  initialSnapshots: { type: Array, default: () => [] },
  initialStats: { type: Object, default: () => ({ total: 0, suspicious: 0, changed: 0, clean: 0 }) },
  meta: { type: Object, default: () => ({}) },
  projects: { type: Array, default: () => [] },
  initialProjectId: { type: [Number, String], default: '' },
  csrf: { type: String, required: true },
  endpoints: { type: Object, required: true }
});

const snapshots = ref(props.initialSnapshots);
const stats = ref(props.initialStats);
const meta = ref(props.meta);
const projects = ref(props.projects);
const filterStatus = ref('');
const projectId = ref(props.initialProjectId || '');
const loading = ref(false);
const isScanning = ref(false);

const pagesArray = computed(() => {
  const pages = [];
  const start = Math.max(1, meta.value.current_page - 2);
  const end = Math.min(meta.value.last_page, meta.value.current_page + 2);
  for (let i = start; i <= end; i++) {
    pages.push(i);
  }
  return pages;
});

const fetchSnapshots = async (page = 1) => {
  loading.value = true;
  try {
    const params = new URLSearchParams({
      page,
      filterStatus: filterStatus.value,
      project_id: projectId.value
    });
    
    const response = await fetch(`${props.endpoints.index}?${params.toString()}`, {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    });
    const data = await response.json();
    snapshots.value = data.data;
    meta.value = data.meta;
    stats.value = data.stats;
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
};

const changePage = (page) => {
  if (page < 1 || page > meta.value.last_page) return;
  fetchSnapshots(page);
};

const initScan = async () => {
  isScanning.value = true;
  try {
    const response = await fetch(props.endpoints.initScan, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': props.csrf,
        'Accept': 'application/json'
      },
      body: JSON.stringify({ project_id: projectId.value })
    });
    const data = await response.json();
    if (window.dispatchEvent) {
      window.dispatchEvent(new CustomEvent('toast', { detail: { type: data.success ? 'success' : 'error', title: 'Scan', message: data.message } }));
    }
    if (data.success) {
      fetchSnapshots(1);
    }
  } catch (e) {
    console.error(e);
  } finally {
    isScanning.value = false;
  }
};

const approveChange = async (file) => {
  try {
    const response = await fetch(`${props.endpoints.approveChange}/${file.id}/approve`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': props.csrf,
        'Accept': 'application/json'
      }
    });
    const data = await response.json();
    if (data.success) {
      const idx = snapshots.value.findIndex(f => f.id === file.id);
      if (idx !== -1) snapshots.value[idx] = data.snapshot;
      
      // Update stats locally
      if (file.status === 'suspicious') {
        stats.value.suspicious = Math.max(0, stats.value.suspicious - 1);
        stats.value.clean++;
      } else if (file.status === 'changed') {
        stats.value.changed = Math.max(0, stats.value.changed - 1);
        stats.value.clean++;
      }
      
      if (window.dispatchEvent) {
        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', title: 'Approved', message: data.message } }));
      }
    }
  } catch (e) {
    console.error(e);
  }
};

function formatNumber(num) {
  return new Intl.NumberFormat().format(num || 0);
}

function getStatusBadgeClass(status) {
  const map = { 'suspicious':'danger', 'changed':'warning', 'new':'info', 'deleted':'muted', 'clean':'success' };
  return map[status] || 'muted';
}

function getRowClass(status) {
  if (status === 'suspicious') return 'row-critical';
  if (status === 'changed') return 'row-warning';
  return '';
}

function formatDate(dateString) {
  if (!dateString) return '';
  const d = new Date(dateString);
  const month = d.toLocaleString('en-US', { month: 'short' });
  const day = d.getDate().toString().padStart(2, '0');
  const hours = d.getHours().toString().padStart(2, '0');
  const mins = d.getMinutes().toString().padStart(2, '0');
  return `${month} ${day}, ${hours}:${mins}`;
}

function formatDiffForHumans(dateString) {
  if (!dateString) return null;
  const date = new Date(dateString);
  const diffInSeconds = Math.floor((new Date() - date) / 1000);
  if (diffInSeconds < 60) return 'Just now';
  if (diffInSeconds < 3600) return Math.floor(diffInSeconds / 60) + ' min ago';
  if (diffInSeconds < 86400) return Math.floor(diffInSeconds / 3600) + ' hrs ago';
  return Math.floor(diffInSeconds / 86400) + ' days ago';
}
</script>

<style scoped>
.row-critical {
  background: rgba(239, 68, 68, 0.05);
}
.row-warning {
  background: rgba(245, 158, 11, 0.05);
}
.badge.danger {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
  border: 1px solid rgba(239, 68, 68, 0.2);
}
.btn-success {
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
  border: 1px solid rgba(16, 185, 129, 0.3);
}
.btn-success:hover {
  background: rgba(16, 185, 129, 0.2);
}
</style>
