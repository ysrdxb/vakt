<template>
  <div>
    <!-- Page Header -->
    <div class="page-header" style="margin-bottom: 24px; display: flex; align-items: center; gap: 16px;">
      <div style="width: 48px; height: 48px; background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #3b82f6;">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:24px;height:24px">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
      </div>
      <div>
        <h1 style="font-size: 24px; font-weight: 600; color: #f8fafc; margin: 0;">SQA Reports</h1>
        <p style="color: #94a3b8; font-size: 14px; margin: 4px 0 0;">Monthly Security Quality Assurance reports for clients</p>
      </div>
    </div>

    <!-- Top Controls -->
    <div class="card mb-6" style="padding: 16px 20px;">
      <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div style="display: flex; gap: 12px; align-items: center;">
          <select v-model="projectId" @change="fetchReports(1)" class="form-control" style="width: 250px;">
            <option value="">All Projects</option>
            <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.domain }}</option>
          </select>
        </div>
        
        <button @click="generateReport" class="btn btn-primary" :disabled="!projectId || generating" :title="!projectId ? 'Select a project first' : ''">
          {{ generating ? 'Generating...' : 'Generate Monthly Report' }}
        </button>
      </div>
    </div>

    <!-- Results -->
    <div class="card" :class="{'opacity-50 pointer-events-none': loading}">
      <div class="card-body" style="padding: 0; overflow-x: auto;">
        <table class="table" style="width: 100%; border-collapse: collapse; text-align: left;">
          <thead>
            <tr style="border-bottom: 1px solid #334155; background: rgba(255,255,255,0.02);">
              <th style="padding: 12px 20px; color: #94a3b8; font-size: 12px; text-transform: uppercase; font-weight: 600;">Report Title</th>
              <th style="padding: 12px 20px; color: #94a3b8; font-size: 12px; text-transform: uppercase; font-weight: 600;">Project</th>
              <th style="padding: 12px 20px; color: #94a3b8; font-size: 12px; text-transform: uppercase; font-weight: 600;">Period</th>
              <th style="padding: 12px 20px; color: #94a3b8; font-size: 12px; text-transform: uppercase; font-weight: 600;">Score</th>
              <th style="padding: 12px 20px; color: #94a3b8; font-size: 12px; text-transform: uppercase; font-weight: 600;">Incidents</th>
              <th style="padding: 12px 20px; color: #94a3b8; font-size: 12px; text-transform: uppercase; font-weight: 600;">Status</th>
              <th style="padding: 12px 20px; color: #94a3b8; font-size: 12px; text-transform: uppercase; font-weight: 600;">Generated</th>
              <th style="padding: 12px 20px; color: #94a3b8; font-size: 12px; text-transform: uppercase; font-weight: 600; text-align: right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="reports.length === 0" style="border-bottom: 1px solid #334155;">
              <td colspan="8" style="padding: 40px; text-align: center; color: #94a3b8;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:40px;height:40px;margin:0 auto 12px;opacity:0.5">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <div style="font-weight: 500; color: #fff;">No reports generated</div>
                <div style="font-size: 13px; margin-top: 4px;">Select a project and click Generate to create the first monthly report.</div>
              </td>
            </tr>
            <tr v-else v-for="report in reports" :key="report.id" style="border-bottom: 1px solid #334155; transition: background .15s;">
              <td style="padding: 12px 20px;">
                <div style="font-weight: 600; color: var(--color-text);">{{ report.title }}</div>
              </td>
              <td style="padding: 12px 20px;">
                <a :href="'/projects/' + (report.project ? report.project.id : '')" class="text-mono" style="color: var(--color-primary); text-decoration: none;">
                  {{ report.project ? report.project.domain : 'Unknown' }}
                </a>
              </td>
              <td style="padding: 12px 20px;">
                <span class="text-mono" style="font-size: 0.85rem;">{{ formatMonth(report.period_month) }}</span>
              </td>
              <td style="padding: 12px 20px;">
                <div style="font-family: var(--font-display); font-size: 1.1rem; font-weight: 700;" :style="{ color: getScoreColor(report.security_score) }">
                  {{ report.security_score }}
                </div>
              </td>
              <td style="padding: 12px 20px;">
                <div style="font-size: 0.8rem;">
                  <span style="color: var(--color-danger);">{{ getSummaryVal(report.incidents_summary, 'total') }} Total</span> · 
                  <span style="color: var(--color-success);">{{ getSummaryVal(report.incidents_summary, 'resolved') }} Resolved</span>
                </div>
              </td>
              <td style="padding: 12px 20px;">
                <span class="badge" :class="getStatusBadgeClass(report.status)">
                  {{ report.status.charAt(0).toUpperCase() + report.status.slice(1) }}
                </span>
              </td>
              <td style="padding: 12px 20px;">
                <span class="text-mono text-muted text-sm">{{ formatDate(report.created_at) }}</span>
              </td>
              <td style="padding: 12px 20px; text-align: right;">
                <div style="display: flex; gap: 6px; justify-content: flex-end;">
                  <a :href="'/reports/' + report.id" class="btn btn-ghost btn-sm">Preview</a>
                  <button v-if="report.status === 'draft'" @click="markSent(report)" class="btn btn-primary btn-sm">Mark Sent</button>
                  <a v-else :href="'/reports/' + report.id + '?download=1'" class="btn btn-ghost btn-sm">PDF</a>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Pagination -->
      <div v-if="meta.last_page > 1" class="card-footer" style="background:var(--color-surface-2); display:flex; justify-content:space-between; align-items:center;">
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
  initialReports: { type: Array, default: () => [] },
  meta: { type: Object, default: () => ({}) },
  projects: { type: Array, default: () => [] },
  initialProjectId: { type: [Number, String], default: '' },
  csrf: { type: String, required: true },
  endpoints: { type: Object, required: true }
});

const reports = ref(props.initialReports);
const meta = ref(props.meta);
const projects = ref(props.projects);
const projectId = ref(props.initialProjectId || '');
const loading = ref(false);
const generating = ref(false);

const pagesArray = computed(() => {
  const pages = [];
  const start = Math.max(1, meta.value.current_page - 2);
  const end = Math.min(meta.value.last_page, meta.value.current_page + 2);
  for (let i = start; i <= end; i++) {
    pages.push(i);
  }
  return pages;
});

const fetchReports = async (page = 1) => {
  loading.value = true;
  try {
    const params = new URLSearchParams({
      page,
      project_id: projectId.value
    });
    
    const response = await fetch(`${props.endpoints.index}?${params.toString()}`, {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    });
    const data = await response.json();
    reports.value = data.data;
    meta.value = data.meta;
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
};

const changePage = (page) => {
  if (page < 1 || page > meta.value.last_page) return;
  fetchReports(page);
};

const generateReport = async () => {
  if (!projectId.value) return;
  generating.value = true;
  try {
    const response = await fetch(props.endpoints.store, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': props.csrf,
        'Accept': 'application/json'
      },
      body: JSON.stringify({ project_id: projectId.value })
    });
    const data = await response.json();
    if (data.success) {
      if (window.dispatchEvent) {
        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', title: 'Generated', message: data.message } }));
      }
      fetchReports(1);
    } else {
      if (window.dispatchEvent) {
        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'warning', title: 'Exists', message: data.message } }));
      }
    }
  } catch (e) {
    console.error(e);
  } finally {
    generating.value = false;
  }
};

const markSent = async (report) => {
  try {
    const response = await fetch(`${props.endpoints.base}/${report.id}/mark-sent`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': props.csrf,
        'Accept': 'application/json'
      }
    });
    const data = await response.json();
    if (data.success) {
      if (window.dispatchEvent) {
        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', title: 'Sent', message: data.message } }));
      }
      const idx = reports.value.findIndex(r => r.id === report.id);
      if (idx !== -1) {
        reports.value[idx].status = 'sent';
      }
    }
  } catch (e) {
    console.error(e);
  }
};

function formatMonth(dateString) {
  if (!dateString) return '';
  const [year, month] = dateString.split('-');
  const date = new Date(year, month - 1);
  return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
}

function formatDate(dateString) {
  if (!dateString) return '';
  const d = new Date(dateString);
  return d.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
}

function getSummaryVal(summary, key) {
  if (!summary) return 0;
  let obj = summary;
  if (typeof summary === 'string') {
    try { obj = JSON.parse(summary); } catch (e) { return 0; }
  }
  return obj[key] || 0;
}

function getScoreColor(s) {
  if (s >= 80) return 'var(--color-primary)';
  if (s >= 60) return 'var(--color-warning)';
  return 'var(--color-danger)';
}

function getStatusBadgeClass(status) {
  const map = { 'draft': 'warning', 'sent': 'success', 'archived': 'muted' };
  return map[status] || 'muted';
}
</script>

<style scoped>
.badge.success {
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
  border: 1px solid rgba(16, 185, 129, 0.2);
}
.badge.warning {
  background: rgba(245, 158, 11, 0.1);
  color: #f59e0b;
  border: 1px solid rgba(245, 158, 11, 0.2);
}
.badge.muted {
  background: rgba(148, 163, 184, 0.1);
  color: #94a3b8;
  border: 1px solid rgba(148, 163, 184, 0.2);
}
</style>
