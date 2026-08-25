<template>
  <div>
    <!-- Page Header -->
    <div class="page-header" style="margin-bottom: 24px; display: flex; align-items: center; gap: 16px;">
      <div style="width: 48px; height: 48px; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #10b981;">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:24px;height:24px">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
      </div>
      <div>
        <h1 style="font-size: 24px; font-weight: 600; color: #f8fafc; margin: 0;">Daily Monitoring Logs</h1>
        <p style="color: #94a3b8; font-size: 14px; margin: 4px 0 0;">Daily log of system monitoring and health checks</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="card" style="margin-bottom: 24px;">
      <div class="card-body" style="padding: 16px 20px; display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
        <select v-model="projectId" @change="fetchLogs(1)" class="form-control" style="width: 250px;">
          <option value="">All Projects</option>
          <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.domain }}</option>
        </select>
        
        <input v-model="selectedDate" @change="fetchLogs(1)" type="date" class="form-control" style="width: 180px;" />
      </div>
    </div>

    <!-- Results -->
    <div class="card" :class="{'opacity-50 pointer-events-none': loading}">
      <div class="card-body" style="padding: 0; overflow-x: auto;">
        <table class="table" style="width: 100%; border-collapse: collapse; text-align: left;">
          <thead>
            <tr style="border-bottom: 1px solid #334155; background: rgba(255,255,255,0.02);">
              <th style="padding: 12px 20px; color: #94a3b8; font-size: 12px; text-transform: uppercase; font-weight: 600;">Date</th>
              <th style="padding: 12px 20px; color: #94a3b8; font-size: 12px; text-transform: uppercase; font-weight: 600;">Project</th>
              <th style="padding: 12px 20px; color: #94a3b8; font-size: 12px; text-transform: uppercase; font-weight: 600;">Status</th>
              <th style="padding: 12px 20px; color: #94a3b8; font-size: 12px; text-transform: uppercase; font-weight: 600;">Summary</th>
              <th style="padding: 12px 20px; color: #94a3b8; font-size: 12px; text-transform: uppercase; font-weight: 600;">Source</th>
              <th style="padding: 12px 20px; color: #94a3b8; font-size: 12px; text-transform: uppercase; font-weight: 600;">Findings</th>
              <th style="padding: 12px 20px; color: #94a3b8; font-size: 12px; text-transform: uppercase; font-weight: 600;">Actions Taken</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="logs.length === 0" style="border-bottom: 1px solid #334155;">
              <td colspan="7" style="padding: 40px; text-align: center; color: #94a3b8;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:40px;height:40px;margin:0 auto 12px;opacity:0.5">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <div style="font-weight: 500; color: #fff;">No daily logs found</div>
                <div style="font-size: 13px; margin-top: 4px;">No monitoring logs match your filters.</div>
              </td>
            </tr>
            <tr v-else v-for="log in logs" :key="log.id" :class="getRowClass(log.status)" style="border-bottom: 1px solid #334155; transition: background .15s;">
              <td style="padding: 12px 20px;">
                <span class="text-mono" style="font-size: 0.82rem;">{{ formatDate(log.checked_at) }}</span>
              </td>
              <td style="padding: 12px 20px;">
                <a :href="'/projects/' + (log.project ? log.project.id : '')" class="text-mono" style="color: var(--color-primary); text-decoration: none;">
                  {{ log.project ? log.project.domain : 'Unknown' }}
                </a>
              </td>
              <td style="padding: 12px 20px;">
                <span class="badge" :class="getStatusBadgeClass(log.status)">{{ log.status.toUpperCase() }}</span>
              </td>
              <td style="padding: 12px 20px;">
                <div style="font-size: 0.85rem; color: var(--color-text); max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" :title="log.summary">
                  {{ log.summary }}
                </div>
              </td>
              <td style="padding: 12px 20px;">
                <span class="badge muted" style="font-size: 0.65rem;">{{ log.auto_generated ? 'Auto' : 'Manual' }}</span>
              </td>
              <td style="padding: 12px 20px;">
                <div style="font-family: var(--font-mono); font-size: 0.75rem;">
                  <div>Checks: {{ getFindingsCount(log.findings, 'checks_run') }}</div>
                  <div :class="getFindingsCount(log.findings, 'errors_found') > 0 ? 'text-danger' : 'text-success'">Errors: {{ getFindingsCount(log.findings, 'errors_found') }}</div>
                  <div :class="getFindingsCount(log.findings, 'incidents') > 0 ? 'text-warning' : 'text-success'">Incidents: {{ getFindingsCount(log.findings, 'incidents') }}</div>
                </div>
              </td>
              <td style="padding: 12px 20px;">
                <div style="min-width: 200px;">
                  <div v-if="!editingLog[log.id]" style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                    <div style="font-size: 0.8rem; color: var(--color-text-dim); flex: 1;">
                      {{ log.actions_taken || 'No notes.' }}
                    </div>
                    <button @click="startEdit(log)" class="btn btn-ghost btn-sm" title="Edit Note">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    </button>
                  </div>
                  <div v-else style="display: flex; gap: 4px;">
                    <textarea v-model="editNotes[log.id]" class="form-control form-control-sm" rows="2" style="flex: 1;"></textarea>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                      <button @click="saveNote(log)" class="btn btn-primary btn-sm" style="padding: 2px 8px;">Save</button>
                      <button @click="cancelEdit(log)" class="btn btn-ghost btn-sm" style="padding: 2px 8px;">X</button>
                    </div>
                  </div>
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
  initialLogs: { type: Array, default: () => [] },
  meta: { type: Object, default: () => ({}) },
  projects: { type: Array, default: () => [] },
  initialProjectId: { type: [Number, String], default: '' },
  initialSelectedDate: { type: String, default: '' },
  csrf: { type: String, required: true },
  endpoints: { type: Object, required: true }
});

const logs = ref(props.initialLogs);
const meta = ref(props.meta);
const projects = ref(props.projects);
const projectId = ref(props.initialProjectId || '');
const selectedDate = ref(props.initialSelectedDate || '');
const loading = ref(false);

const editingLog = ref({});
const editNotes = ref({});

const pagesArray = computed(() => {
  const pages = [];
  const start = Math.max(1, meta.value.current_page - 2);
  const end = Math.min(meta.value.last_page, meta.value.current_page + 2);
  for (let i = start; i <= end; i++) {
    pages.push(i);
  }
  return pages;
});

const fetchLogs = async (page = 1) => {
  loading.value = true;
  try {
    const params = new URLSearchParams({
      page,
      project_id: projectId.value,
      selectedDate: selectedDate.value
    });
    
    const response = await fetch(`${props.endpoints.index}?${params.toString()}`, {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    });
    const data = await response.json();
    logs.value = data.data;
    meta.value = data.meta;
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
};

const changePage = (page) => {
  if (page < 1 || page > meta.value.last_page) return;
  fetchLogs(page);
};

const startEdit = (log) => {
  editNotes.value[log.id] = log.actions_taken || '';
  editingLog.value[log.id] = true;
};

const cancelEdit = (log) => {
  editingLog.value[log.id] = false;
};

const saveNote = async (log) => {
  try {
    const response = await fetch(`${props.endpoints.base}/${log.id}/note`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': props.csrf,
        'Accept': 'application/json'
      },
      body: JSON.stringify({ note: editNotes.value[log.id] })
    });
    const data = await response.json();
    if (data.success) {
      const idx = logs.value.findIndex(l => l.id === log.id);
      if (idx !== -1) logs.value[idx] = data.log;
      editingLog.value[log.id] = false;
      
      if (window.dispatchEvent) {
        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', title: 'Saved', message: data.message } }));
      }
    }
  } catch (e) {
    console.error(e);
  }
};

function formatDate(dateString) {
  if (!dateString) return '';
  const d = new Date(dateString);
  return d.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
}

function getFindingsCount(findings, key) {
  if (!findings) return 0;
  
  let obj = findings;
  if (typeof findings === 'string') {
    try { obj = JSON.parse(findings); } catch (e) { return 0; }
  }
  
  return obj[key] || 0;
}

function getStatusBadgeClass(status) {
  const map = { 'critical': 'danger', 'warning': 'warning', 'ok': 'success' };
  return map[status] || 'muted';
}

function getRowClass(status) {
  if (status === 'critical') return 'row-critical';
  if (status === 'warning') return 'row-warning';
  return '';
}
</script>

<style scoped>
.row-critical {
  background: rgba(239, 68, 68, 0.05);
}
.row-warning {
  background: rgba(245, 158, 11, 0.05);
}
.text-danger {
  color: #ef4444;
}
.text-warning {
  color: #f59e0b;
}
.text-success {
  color: #10b981;
}
.badge.danger {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
  border: 1px solid rgba(239, 68, 68, 0.2);
}
.badge.muted {
  background: rgba(148, 163, 184, 0.1);
  color: #94a3b8;
  border: 1px solid rgba(148, 163, 184, 0.2);
}
</style>
