<template>
  <div>
    <!-- Page Header -->
    <div class="page-header" style="margin-bottom: 24px; display: flex; align-items: center; gap: 16px;">
      <div style="width: 48px; height: 48px; background: rgba(59,130,246,0.1); border: 1px solid rgba(59,130,246,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #3b82f6;">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:24px;height:24px">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
      </div>
      <div>
        <h1 style="font-size: 24px; font-weight: 600; color: #f8fafc; margin: 0;">Log Viewer</h1>
        <p style="color: #94a3b8; font-size: 14px; margin: 4px 0 0;">Real-time log monitoring across all projects</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="card" style="margin-bottom: 24px;">
      <div class="card-body" style="padding: 16px 20px; display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 200px; position: relative;">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:18px;height:18px;position:absolute;left:12px;top:10px;color:#64748b;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input type="text" v-model="search" @input="debouncedFetch" class="form-control" placeholder="Search logs..." style="padding-left: 38px;" />
        </div>
        
        <div style="width: 180px;">
          <select v-model="projectId" @change="fetchEntries(1)" class="form-control">
            <option value="">All Projects</option>
            <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.domain }}</option>
          </select>
        </div>
        
        <div style="width: 150px;">
          <select v-model="filterLevel" @change="fetchEntries(1)" class="form-control">
            <option value="">All Levels</option>
            <option value="debug">Debug</option>
            <option value="info">Info</option>
            <option value="warning">Warning</option>
            <option value="error">Error</option>
            <option value="critical">Critical</option>
          </select>
        </div>

        <div style="display:flex;align-items:center;gap:6px;font-size:0.75rem;color:var(--color-success);margin-left:auto;padding-left:12px">
          <div style="width:8px;height:8px;border-radius:50%;background:currentColor;animation:pulse 2s infinite"></div>
          Live — refreshes every 30s
        </div>
      </div>
    </div>

    <!-- Results -->
    <div class="card" :class="{'opacity-50 pointer-events-none': loading}">
      <div class="card-body" style="padding: 0; overflow-x: auto;">
        <table class="table" style="width:100%; border-collapse:collapse; text-align:left;">
          <thead>
            <tr style="border-bottom:1px solid #334155; background:rgba(255,255,255,0.02);">
              <th style="padding:12px 20px; color:#94a3b8; font-size:12px; text-transform:uppercase; font-weight:600;">Timestamp</th>
              <th style="padding:12px 20px; color:#94a3b8; font-size:12px; text-transform:uppercase; font-weight:600;">Level</th>
              <th style="padding:12px 20px; color:#94a3b8; font-size:12px; text-transform:uppercase; font-weight:600;">Project</th>
              <th style="padding:12px 20px; color:#94a3b8; font-size:12px; text-transform:uppercase; font-weight:600;">Message</th>
              <th style="padding:12px 20px; color:#94a3b8; font-size:12px; text-transform:uppercase; font-weight:600;">Patterns</th>
              <th style="padding:12px 20px; color:#94a3b8; font-size:12px; text-transform:uppercase; font-weight:600;">IP</th>
              <th style="padding:12px 20px; color:#94a3b8; font-size:12px; text-transform:uppercase; font-weight:600; text-align:right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="entries.length === 0" style="border-bottom:1px solid #334155;">
              <td colspan="7" style="padding:40px; text-align:center; color:#94a3b8;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:40px;height:40px;margin:0 auto 12px;opacity:0.5">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <div style="font-weight:500;color:#fff;">No logs found</div>
                <div style="font-size:13px;margin-top:4px;">No logs match your filters.</div>
              </td>
            </tr>
            <template v-else v-for="entry in entries" :key="entry.id">
              <tr :class="getRowClass(entry.level)" style="border-bottom:1px solid #334155; transition:background .15s;">
                <td style="padding:12px 20px;">
                  <span class="text-mono" style="font-size:0.82rem">{{ formatDate(entry.occurred_at) }}</span>
                </td>
                <td style="padding:12px 20px;">
                  <span class="badge" :class="getLevelBadgeClass(entry.level)">{{ entry.level.toUpperCase() }}</span>
                </td>
                <td style="padding:12px 20px;">
                  <a :href="'/projects/' + (entry.project ? entry.project.id : '')" class="text-mono" style="color:var(--color-primary);text-decoration:none">
                    {{ entry.project ? entry.project.domain : 'Unknown' }}
                  </a>
                </td>
                <td style="padding:12px 20px;">
                  <div style="font-family:var(--font-mono);font-size:0.75rem;color:var(--color-text-dim);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:350px" :title="entry.message">
                    {{ entry.message }}
                  </div>
                </td>
                <td style="padding:12px 20px;">
                  <div v-if="entry.detected_patterns && entry.detected_patterns.length > 0" style="display:flex;gap:4px;flex-wrap:wrap">
                    <span v-for="pattern in entry.detected_patterns" :key="pattern" class="badge danger" style="font-size:0.6rem;padding:2px 6px">{{ pattern }}</span>
                  </div>
                  <span v-else class="text-muted text-sm">—</span>
                </td>
                <td style="padding:12px 20px;">
                  <span v-if="entry.ip_address" class="text-mono text-sm">{{ entry.ip_address }}</span>
                  <span v-else class="text-muted text-sm">—</span>
                </td>
                <td style="padding:12px 20px; text-align:right;">
                  <div style="display:flex;gap:4px;justify-content:flex-end;">
                    <button v-if="!entry.is_reviewed" @click="markReviewed(entry)" class="btn btn-ghost btn-sm" title="Mark as reviewed">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    </button>
                    <span v-else class="text-muted text-sm" style="display:inline-flex;align-items:center;padding:4px 8px;">Reviewed</span>
                    
                    <button @click="toggleExpand(entry.id)" class="btn btn-ghost btn-sm" title="View Details">
                      <svg v-if="expandedLog !== entry.id" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                      <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" /></svg>
                    </button>
                  </div>
                </td>
              </tr>
              
              <!-- Expanded View -->
              <tr v-if="expandedLog === entry.id" style="background:var(--color-surface-2); border-bottom:1px solid #334155;">
                <td colspan="7" style="padding:16px 20px;">
                  <div style="display:flex;gap:24px;flex-wrap:wrap">
                    <div style="flex:1;min-width:300px;">
                      <div style="font-size:0.75rem;text-transform:uppercase;color:var(--color-muted);margin-bottom:8px">Full Message</div>
                      <div style="background:var(--color-background);padding:12px;border-radius:6px;font-family:var(--font-mono);font-size:0.85rem;color:var(--color-text);white-space:pre-wrap;max-height:300px;overflow-y:auto;border:1px solid var(--color-border)">
                        {{ formatMessage(entry.message) }}
                      </div>
                    </div>
                    
                    <div style="flex:1;min-width:300px;">
                      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                        <div style="font-size:0.75rem;text-transform:uppercase;color:var(--color-muted);">AI Diagnostics</div>
                        <button v-if="!entry.ai_explanation" @click="analyzeWithAI(entry)" class="btn btn-primary btn-sm" :disabled="analyzing[entry.id]">
                          <span v-if="!analyzing[entry.id]">Ask AI for Quick Fix</span>
                          <span v-else><span class="spinner-sm" style="margin-right:6px"></span>Analyzing...</span>
                        </button>
                      </div>
                      
                      <div v-if="aiErrors[entry.id]" style="background:rgba(239, 68, 68, 0.05);padding:16px;border-radius:6px;border:1px solid rgba(239, 68, 68, 0.2);color:var(--color-text);margin-bottom:12px">
                        <div style="display:flex;gap:8px;margin-bottom:8px;color:#ef4444">
                          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:20px;height:20px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                          <strong style="font-size:0.9rem">Analysis Error</strong>
                        </div>
                        <div style="font-size:0.85rem;line-height:1.5;white-space:pre-wrap">{{ aiErrors[entry.id] }}</div>
                      </div>
                      
                      <div v-if="entry.ai_explanation" style="background:rgba(139, 92, 246, 0.05);padding:20px;border-radius:8px;border:1px solid rgba(139, 92, 246, 0.2);color:var(--color-text);">
                        <div style="display:flex;gap:8px;margin-bottom:16px;color:#a78bfa;align-items:center">
                          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:22px;height:22px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                          <strong style="font-size:1rem;letter-spacing:0.5px">AI Quick Fix Hint</strong>
                        </div>
                        <div style="font-size:0.95rem;line-height:1.7;white-space:pre-wrap;">
                          {{ entry.ai_explanation }}
                        </div>
                      </div>
                      <div v-else-if="!entry.ai_explanation && !aiErrors[entry.id]" style="padding:24px;border:1px dashed var(--color-border);border-radius:8px;text-align:center;color:var(--color-text-dim);font-size:0.9rem;background:var(--color-surface)">
                        Not analyzed yet. Click the button to generate a human-readable explanation and solution hint.
                      </div>
                    </div>
                  </div>
                </td>
              </tr>
            </template>
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
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  initialEntries: { type: Array, default: () => [] },
  meta: { type: Object, default: () => ({}) },
  projects: { type: Array, default: () => [] },
  initialProjectId: { type: [Number, String], default: '' },
  csrf: { type: String, required: true },
  endpoints: { type: Object, required: true }
});

const entries = ref(props.initialEntries);
const meta = ref(props.meta);
const projects = ref(props.projects);
const search = ref('');
const filterLevel = ref('');
const projectId = ref(props.initialProjectId || '');
const loading = ref(false);
const expandedLog = ref(null);
const analyzing = ref({});
const aiErrors = ref({});
let searchTimeout = null;
let pollInterval = null;

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
  pollInterval = setInterval(() => {
    if (meta.value.current_page === 1) {
      fetchEntries(1, false);
    }
  }, 30000);
});

onUnmounted(() => {
  clearInterval(pollInterval);
});

const debouncedFetch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => fetchEntries(1), 300);
};

const fetchEntries = async (page = 1, showLoading = true) => {
  if (showLoading) loading.value = true;
  try {
    const params = new URLSearchParams({
      page,
      search: search.value,
      filterLevel: filterLevel.value,
      project_id: projectId.value
    });
    
    const response = await fetch(`${props.endpoints.index}?${params.toString()}`, {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    });
    const data = await response.json();
    entries.value = data.data;
    meta.value = data.meta;
  } catch (e) {
    console.error(e);
  } finally {
    if (showLoading) loading.value = false;
  }
};

const changePage = (page) => {
  if (page < 1 || page > meta.value.last_page) return;
  fetchEntries(page);
};

const toggleExpand = (id) => {
  if (expandedLog.value === id) {
    expandedLog.value = null;
  } else {
    expandedLog.value = id;
  }
};

const markReviewed = async (entry) => {
  try {
    const response = await fetch(`${props.endpoints.markReviewed}/${entry.id}/review`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': props.csrf,
        'Accept': 'application/json'
      }
    });
    const data = await response.json();
    if (data.success) {
      const idx = entries.value.findIndex(e => e.id === entry.id);
      if (idx !== -1) entries.value[idx] = data.entry;
      
      if (window.dispatchEvent) {
        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', title: 'Marked', message: data.message } }));
      }
    }
  } catch (e) {
    console.error(e);
  }
};

const analyzeWithAI = async (entry) => {
  analyzing.value[entry.id] = true;
  aiErrors.value[entry.id] = null;
  
  try {
    const response = await fetch(`${props.endpoints.analyzeWithAI}/${entry.id}/analyze`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': props.csrf,
        'Accept': 'application/json'
      }
    });
    const data = await response.json();
    if (data.success) {
      const idx = entries.value.findIndex(e => e.id === entry.id);
      if (idx !== -1) entries.value[idx] = data.entry;
    } else {
      aiErrors.value[entry.id] = data.message;
    }
  } catch (e) {
    aiErrors.value[entry.id] = "Network error while requesting analysis.";
  } finally {
    analyzing.value[entry.id] = false;
  }
};

function getLevelBadgeClass(level) {
  const map = { 'critical':'danger', 'error':'danger', 'warning':'warning', 'info':'info' };
  return map[level] || 'muted';
}

function getRowClass(level) {
  if (['critical', 'error'].includes(level)) return 'row-critical';
  if (level === 'warning') return 'row-warning';
  return '';
}

function formatDate(dateString) {
  if (!dateString) return '';
  const d = new Date(dateString);
  const month = d.toLocaleString('en-US', { month: 'short' });
  const day = d.getDate().toString().padStart(2, '0');
  const hours = d.getHours().toString().padStart(2, '0');
  const mins = d.getMinutes().toString().padStart(2, '0');
  const secs = d.getSeconds().toString().padStart(2, '0');
  return `${month} ${day}, ${hours}:${mins}:${secs}`;
}

function formatMessage(msg) {
  try {
    const decoded = JSON.parse(msg);
    if (Array.isArray(decoded)) {
      return decoded.join('\n');
    } else if (typeof decoded === 'object') {
      return JSON.stringify(decoded, null, 2);
    }
    return decoded;
  } catch (e) {
    // Basic cleanup if not valid JSON
    let f = msg.replace(/","/g, '\n');
    f = f.replace(/\["/g, '');
    f = f.replace(/"\]/g, '');
    f = f.replace(/\\\//g, '/');
    return f;
  }
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
@keyframes pulse {
  0% { transform: scale(1); opacity: 1; }
  50% { transform: scale(1.5); opacity: 0.5; }
  100% { transform: scale(1); opacity: 1; }
}
</style>
