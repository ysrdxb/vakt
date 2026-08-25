<template>
  <div>
    <!-- Page Header -->
    <div class="page-header" style="margin-bottom: 24px; display: flex; align-items: center; gap: 16px;">
      <div style="width: 48px; height: 48px; background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #3b82f6;">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:24px;height:24px">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
      </div>
      <div>
        <h1 style="font-size: 24px; font-weight: 600; color: #f8fafc; margin: 0;">Alert Log</h1>
        <p style="color: #94a3b8; font-size: 14px; margin: 4px 0 0;">History of all automated alerts sent by the SOC</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="card" style="margin-bottom: 24px;">
      <div class="card-body" style="padding: 16px 20px; display: flex; gap: 12px; align-items: center;">
        <select v-model="filterType" @change="fetchLogs(1)" class="form-control" style="width: 200px;">
          <option value="">All Alert Types</option>
          <option value="incident_created">Incident Created</option>
          <option value="daily_digest">Daily Digest</option>
          <option value="weekly_summary">Weekly Summary</option>
          <option value="sla_breach">SLA Breach</option>
        </select>
      </div>
    </div>

    <!-- Results -->
    <div class="card" :class="{'opacity-50 pointer-events-none': loading}">
      <div class="card-body" style="padding: 0; overflow-x: auto;">
        <table class="table" style="width: 100%; border-collapse: collapse; text-align: left;">
          <thead>
            <tr style="border-bottom: 1px solid #334155; background: rgba(255,255,255,0.02);">
              <th style="padding: 12px 20px; color: #94a3b8; font-size: 12px; text-transform: uppercase; font-weight: 600;">Type</th>
              <th style="padding: 12px 20px; color: #94a3b8; font-size: 12px; text-transform: uppercase; font-weight: 600;">Recipient</th>
              <th style="padding: 12px 20px; color: #94a3b8; font-size: 12px; text-transform: uppercase; font-weight: 600;">Subject</th>
              <th style="padding: 12px 20px; color: #94a3b8; font-size: 12px; text-transform: uppercase; font-weight: 600;">Project / Related</th>
              <th style="padding: 12px 20px; color: #94a3b8; font-size: 12px; text-transform: uppercase; font-weight: 600;">Status</th>
              <th style="padding: 12px 20px; color: #94a3b8; font-size: 12px; text-transform: uppercase; font-weight: 600;">Sent At</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="logs.length === 0" style="border-bottom: 1px solid #334155;">
              <td colspan="6" style="padding: 40px; text-align: center; color: #94a3b8;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:40px;height:40px;margin:0 auto 12px;opacity:0.5">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <div style="font-weight: 500; color: #fff;">No alerts sent</div>
                <div style="font-size: 13px; margin-top: 4px;">No alerts match your filter criteria.</div>
              </td>
            </tr>
            <tr v-else v-for="log in logs" :key="log.id" style="border-bottom: 1px solid #334155; transition: background .15s;">
              <td style="padding: 12px 20px;">
                <span class="badge muted">{{ formatType(log.alert_type) }}</span>
              </td>
              <td style="padding: 12px 20px;">
                <span class="text-mono" style="font-size: 0.8rem;">{{ log.recipient }}</span>
              </td>
              <td style="padding: 12px 20px;">
                <div style="font-weight: 500; color: var(--color-text); max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" :title="log.subject">
                  {{ log.subject }}
                </div>
              </td>
              <td style="padding: 12px 20px;">
                <div v-if="log.project">
                  <a :href="'/projects/' + log.project.id" class="text-mono" style="font-size: 0.8rem; color: var(--color-primary); text-decoration: none;">
                    {{ log.project.domain }}
                  </a>
                </div>
                <div v-if="log.incident" style="font-size: 0.75rem; color: var(--color-muted); margin-top: 2px;">
                  Incident: <a :href="'/incidents/' + log.incident.id" style="color: inherit; text-decoration: none;">#{{ log.incident.id }}</a>
                </div>
              </td>
              <td style="padding: 12px 20px;">
                <span class="badge" :class="log.status === 'sent' ? 'success' : 'danger'">
                  {{ log.status.charAt(0).toUpperCase() + log.status.slice(1) }}
                </span>
              </td>
              <td style="padding: 12px 20px;">
                <span class="text-mono text-muted text-sm">{{ formatDate(log.created_at) }}</span>
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
  initialFilterType: { type: String, default: '' },
  endpoints: { type: Object, required: true }
});

const logs = ref(props.initialLogs);
const meta = ref(props.meta);
const filterType = ref(props.initialFilterType || '');
const loading = ref(false);

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
      filterType: filterType.value
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

function formatType(type) {
  if (!type) return '';
  return type.replace(/_/g, ' ');
}

function formatDate(dateString) {
  if (!dateString) return '';
  const d = new Date(dateString);
  const month = d.toLocaleString('en-US', { month: 'short' });
  const day = d.getDate().toString().padStart(2, '0');
  const hours = d.getHours().toString().padStart(2, '0');
  const minutes = d.getMinutes().toString().padStart(2, '0');
  const seconds = d.getSeconds().toString().padStart(2, '0');
  return `${month} ${day}, ${hours}:${minutes}:${seconds}`;
}
</script>

<style scoped>
.badge.success {
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
  border: 1px solid rgba(16, 185, 129, 0.2);
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
