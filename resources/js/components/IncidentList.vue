<template>
  <div>
    <!-- Page Header -->
    <div class="page-header" style="margin-bottom: 24px; display: flex; align-items: center; gap: 16px;">
      <div style="width: 48px; height: 48px; background: rgba(244,63,94,0.1); border: 1px solid rgba(244,63,94,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #f43f5e;">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:24px;height:24px">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
      </div>
      <div>
        <h1 style="font-size: 24px; font-weight: 600; color: #f8fafc; margin: 0;">Incidents</h1>
        <p style="color: #94a3b8; font-size: 14px; margin: 4px 0 0;">Security alerts and issues detected across all projects.</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="card" style="margin-bottom: 24px;">
      <div class="card-body" style="padding: 16px 20px; display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 250px; position: relative;">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:18px;height:18px;position:absolute;left:12px;top:10px;color:#64748b;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input type="text" v-model="search" @input="debouncedFetch" class="form-control" placeholder="Search incidents..." style="padding-left: 38px;" />
        </div>
        
        <div style="width: 200px;">
          <select v-model="filterProject" @change="fetchIncidents(1)" class="form-control">
            <option value="">All Projects</option>
            <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.domain }}</option>
          </select>
        </div>
        
        <div style="width: 150px;">
          <select v-model="filterSeverity" @change="fetchIncidents(1)" class="form-control">
            <option value="">All Severities</option>
            <option value="p1">P1 - Critical</option>
            <option value="p2">P2 - High</option>
            <option value="p3">P3 - Medium</option>
            <option value="p4">P4 - Low</option>
          </select>
        </div>

        <div style="width: 160px;">
          <select v-model="filterStatus" @change="fetchIncidents(1)" class="form-control">
            <option value="">All Statuses</option>
            <option value="open">Open</option>
            <option value="investigating">Investigating</option>
            <option value="contained">Contained</option>
            <option value="resolved">Resolved</option>
            <option value="closed">Closed</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Results Table -->
    <div class="card">
      <div class="card-body" style="padding: 0; overflow-x: auto;">
        <table class="table" style="width:100%; border-collapse:collapse; text-align:left;">
          <thead>
            <tr style="border-bottom:1px solid #334155; background:rgba(255,255,255,0.02);">
              <th style="padding:12px 20px; color:#94a3b8; font-size:12px; text-transform:uppercase; font-weight:600; width: 1%;">Severity</th>
              <th style="padding:12px 20px; color:#94a3b8; font-size:12px; text-transform:uppercase; font-weight:600;">Incident</th>
              <th style="padding:12px 20px; color:#94a3b8; font-size:12px; text-transform:uppercase; font-weight:600;">Project</th>
              <th style="padding:12px 20px; color:#94a3b8; font-size:12px; text-transform:uppercase; font-weight:600;">Time</th>
              <th style="padding:12px 20px; color:#94a3b8; font-size:12px; text-transform:uppercase; font-weight:600; text-align:right;">Status & Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading" style="border-bottom:1px solid #334155;">
              <td colspan="5" style="padding:40px; text-align:center; color:#94a3b8;">
                <span class="spinner-sm" style="margin-right: 8px;"></span> Loading...
              </td>
            </tr>
            <tr v-else-if="incidents.length === 0" style="border-bottom:1px solid #334155;">
              <td colspan="5" style="padding:40px; text-align:center; color:#94a3b8;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:40px;height:40px;margin:0 auto 12px;opacity:0.5">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div style="font-weight:500;color:#fff;">No incidents found</div>
                <div style="font-size:13px;margin-top:4px;">Try adjusting your filters</div>
              </td>
            </tr>
            <tr v-else v-for="incident in incidents" :key="incident.id" style="border-bottom:1px solid #334155; transition:background .15s;" class="hover-surface">
              <td style="padding:12px 20px;">
                <span class="badge" :class="severityBadgeClass(incident.severity)">{{ getSeverityLabel(incident.severity) }}</span>
              </td>
              <td style="padding:12px 20px;">
                <a :href="'/incidents/' + incident.id" style="font-weight:600; color:#f8fafc; font-size:14px; text-decoration:none;">{{ incident.title }}</a>
                <div style="font-family:var(--font-mono); font-size:12px; color:#94a3b8; margin-top:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:300px;">
                  {{ incident.description ? incident.description.substring(0, 100) : 'No description' }}
                </div>
              </td>
              <td style="padding:12px 20px; font-family:var(--font-mono); font-size:13px;">
                {{ incident.project ? incident.project.domain : 'Unknown' }}
              </td>
              <td style="padding:12px 20px;">
                <div style="font-size:13px;">{{ formatDiffForHumans(incident.detected_at) }}</div>
                <div v-if="incident.sla_respond_breached" class="sla-timer breach" style="margin-top:4px; display:inline-block;">SLA BREACH</div>
              </td>
              <td style="padding:12px 20px; text-align:right;">
                <div style="display:flex; flex-direction:column; align-items:flex-end; gap:6px;">
                  <span class="badge" :class="getIncidentStatusClass(incident.status)">{{ incident.status.replace('_', ' ') }}</span>
                  <div class="dropdown-wrapper">
                    <button class="btn btn-sm btn-ghost dropdown-trigger">Quick Update ▾</button>
                    <div class="dropdown-menu">
                      <button @click="updateStatus(incident.id, 'investigating')" class="dropdown-item">Mark Investigating</button>
                      <button @click="updateStatus(incident.id, 'contained')" class="dropdown-item">Mark Contained</button>
                      <button @click="updateStatus(incident.id, 'resolved')" class="dropdown-item" style="color:var(--color-success)">Mark Resolved</button>
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
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
  initialIncidents: { type: Array, default: () => [] },
  meta: { type: Object, default: () => ({}) },
  projects: { type: Array, default: () => [] },
  csrf: { type: String, required: true },
  endpoints: { type: Object, required: true }
});

const incidents = ref(props.initialIncidents);
const meta = ref(props.meta);
const search = ref('');
const filterSeverity = ref('');
const filterStatus = ref('');
const filterProject = ref((new URLSearchParams(window.location.search)).get('project') || '');
const loading = ref(false);
let searchTimeout = null;

const pagesArray = computed(() => {
  const pages = [];
  const start = Math.max(1, meta.value.current_page - 2);
  const end = Math.min(meta.value.last_page, meta.value.current_page + 2);
  for (let i = start; i <= end; i++) {
    pages.push(i);
  }
  return pages;
});

onMounted(() => {
  if (filterProject.value) {
    fetchIncidents(1); // Auto-filter if project passed in URL
  }
});

const debouncedFetch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => fetchIncidents(1), 300);
};

const fetchIncidents = async (page = 1) => {
  loading.value = true;
  try {
    const params = new URLSearchParams({
      page,
      search: search.value,
      filterSeverity: filterSeverity.value,
      filterStatus: filterStatus.value,
      filterProject: filterProject.value
    });
    
    const response = await fetch(`${props.endpoints.index}?${params.toString()}`, {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    });
    const data = await response.json();
    incidents.value = data.data;
    meta.value = data.meta;
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
};

const changePage = (page) => {
  if (page < 1 || page > meta.value.last_page) return;
  fetchIncidents(page);
};

const updateStatus = async (id, status) => {
  try {
    const response = await fetch(`/incidents/${id}/status`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': props.csrf,
        'Accept': 'application/json'
      },
      body: JSON.stringify({ status })
    });
    const data = await response.json();
    if (data.success) {
      if (window.dispatchEvent) {
        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', title: 'Status Updated', message: data.message } }));
      }
      fetchIncidents(meta.value.current_page);
    }
  } catch (e) {
    console.error(e);
  }
};

const severityBadgeClass = (severity) => {
  const map = { p1: 'critical', p2: 'warning', p3: 'info', p4: 'success' };
  return map[severity] || 'muted';
};

function getSeverityLabel(severity) {
  const labels = { p1: 'P1 - Critical', p2: 'P2 - High', p3: 'P3 - Medium', p4: 'P4 - Low' };
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
</script>

<style scoped>
.hover-surface:hover {
  background: var(--color-surface-2) !important;
}
.dropdown-wrapper { position: relative; display: inline-block; }
.dropdown-menu { 
  display: none; 
  position: absolute; 
  right: 0; 
  top: 100%; 
  background: var(--color-surface-2); 
  border: 1px solid var(--color-border); 
  border-radius: 6px; 
  box-shadow: 0 10px 15px -3px rgba(0,0,0,0.5); 
  z-index: 10; 
  min-width: 160px;
}
.dropdown-wrapper:hover .dropdown-menu { display: block; }
.dropdown-item {
  display: block;
  width: 100%;
  text-align: left;
  padding: 8px 16px;
  background: none;
  border: none;
  color: var(--color-text);
  cursor: pointer;
  font-size: 13px;
}
.dropdown-item:hover { background: var(--color-surface-3); }
</style>
