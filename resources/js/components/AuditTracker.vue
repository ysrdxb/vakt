<template>
  <div>
    <!-- Page Header -->
    <div class="page-header" style="margin-bottom: 24px; display: flex; align-items: center; gap: 16px;">
      <div style="width: 48px; height: 48px; background: rgba(139,92,246,0.1); border: 1px solid rgba(139,92,246,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #8b5cf6;">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:24px;height:24px">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
      </div>
      <div>
        <h1 style="font-size: 24px; font-weight: 600; color: #f8fafc; margin: 0;">Security Audit</h1>
        <p style="color: #94a3b8; font-size: 14px; margin: 4px 0 0;">OWASP-based security checklist and compliance tracking</p>
      </div>
    </div>

    <!-- Top Controls -->
    <div class="card mb-6" style="padding: 16px 20px;">
      <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div style="display: flex; gap: 12px; align-items: center;">
          <select v-model="projectId" @change="fetchAuditData" class="form-control" style="width: 250px;">
            <option value="">Select a Project...</option>
            <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.domain }}</option>
          </select>
          
          <button v-if="projectId" @click="seedChecklist" class="btn btn-ghost" :disabled="loading">
            Load Default Checklist
          </button>
        </div>
        
        <button class="btn btn-ghost" disabled>Export PDF (Coming Soon)</button>
      </div>
    </div>

    <!-- States -->
    <div v-if="!projectId" style="padding: 40px; text-align: center; color: #94a3b8;">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:40px;height:40px;margin:0 auto 12px;opacity:0.5">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
      </svg>
      <div style="font-weight: 500; color: #fff;">Select a project</div>
      <div style="font-size: 13px; margin-top: 4px;">Choose a project to view or manage its security audit checklist.</div>
    </div>
    
    <div v-else-if="Object.keys(itemsByCategory).length === 0" style="padding: 40px; text-align: center; color: #94a3b8;">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:40px;height:40px;margin:0 auto 12px;opacity:0.5">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
      </svg>
      <div style="font-weight: 500; color: #fff;">Checklist empty</div>
      <div style="font-size: 13px; margin-top: 4px;">Click 'Load Default Checklist' to populate the OWASP security items for this project.</div>
    </div>
    
    <div v-else :class="{'opacity-50 pointer-events-none': loading}">
      <div class="grid grid-2 gap-6 mb-6" style="align-items: start; display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">
        <div class="card" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 32px;">
          <div style="font-size: 0.875rem; text-transform: uppercase; letter-spacing: .1em; color: var(--color-muted); margin-bottom: 16px;">Compliance Score</div>
          
          <div style="position: relative; width: 160px; height: 160px; border-radius: 50%; display: flex; align-items: center; justify-content: center;" :style="{ background: `conic-gradient(${getScoreColor(score)} ${score}%, var(--color-surface-2) 0)` }">
            <div style="position: absolute; width: 140px; height: 140px; border-radius: 50%; background: var(--color-surface); display: flex; flex-direction: column; align-items: center; justify-content: center;">
              <div style="font-family: var(--font-display); font-size: 2.5rem; font-weight: 700;" :style="{ color: getScoreColor(score) }">{{ score }}</div>
              <div style="font-size: 0.875rem; color: var(--color-muted);">/ 100</div>
            </div>
          </div>
        </div>
        
        <div>
          <div style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.2); color: #93c5fd; padding: 16px; border-radius: 8px; margin-bottom: 16px;">
            <strong>How it works:</strong> Pass items to increase score. Failed critical items subtract 20 points, high subtract 10, medium 5, and low 2.
          </div>
          
          <div class="grid grid-2 gap-4" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="card p-4" style="padding: 16px;">
              <div style="color: var(--color-muted); font-size: 0.875rem; text-transform: uppercase; margin-bottom: 4px;">Passed Items</div>
              <div style="font-family: var(--font-display); font-size: 1.5rem; font-weight: 700; color: var(--color-success);">{{ getPassCount() }}</div>
            </div>
            <div class="card p-4" style="padding: 16px;">
              <div style="color: var(--color-muted); font-size: 0.875rem; text-transform: uppercase; margin-bottom: 4px;">Failed Items</div>
              <div style="font-family: var(--font-display); font-size: 1.5rem; font-weight: 700;" :style="{ color: getFailCount() > 0 ? 'var(--color-danger)' : 'var(--color-success)' }">{{ getFailCount() }}</div>
            </div>
          </div>
        </div>
      </div>

      <div style="display: flex; flex-direction: column; gap: 24px;">
        <div v-for="(items, category) in itemsByCategory" :key="category" class="card">
          <div class="card-header" style="background: var(--color-surface-2);">
            <div class="card-title">{{ category }}</div>
          </div>
          <div class="card-body" style="padding: 0; overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse; text-align: left;">
              <thead>
                <tr style="border-bottom: 1px solid #334155; background: rgba(255,255,255,0.02);">
                  <th style="padding: 12px 20px; color: #94a3b8; font-size: 12px; text-transform: uppercase; font-weight: 600; width: 35%;">Audit Item</th>
                  <th style="padding: 12px 20px; color: #94a3b8; font-size: 12px; text-transform: uppercase; font-weight: 600;">Severity</th>
                  <th style="padding: 12px 20px; color: #94a3b8; font-size: 12px; text-transform: uppercase; font-weight: 600; width: 15%;">Status</th>
                  <th style="padding: 12px 20px; color: #94a3b8; font-size: 12px; text-transform: uppercase; font-weight: 600; width: 30%;">Notes</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in items" :key="item.id" :style="{ background: getRowBackground(item.status) }" style="border-bottom: 1px solid #334155; transition: background .15s;">
                  <td style="padding: 12px 20px; font-weight: 500; font-size: 14px;">{{ item.item_name }}</td>
                  <td style="padding: 12px 20px;">
                    <span class="badge" :class="getSeverityBadgeClass(item.severity)">{{ item.severity.charAt(0).toUpperCase() + item.severity.slice(1) }}</span>
                  </td>
                  <td style="padding: 12px 20px;">
                    <select v-model="item.status" @change="updateStatus(item)" class="form-select form-control-sm" style="min-width: 120px;">
                      <option value="unchecked">Unchecked</option>
                      <option value="pass">Pass</option>
                      <option value="fail">Fail</option>
                      <option value="partial">Partial</option>
                      <option value="na">N/A</option>
                    </select>
                  </td>
                  <td style="padding: 12px 20px;">
                    <input type="text" v-model="item.notes" @blur="updateNotes(item)" class="form-control form-control-sm" placeholder="Add notes..." />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  initialItemsByCategory: { type: [Object, Array], default: () => ({}) },
  initialScore: { type: Number, default: 0 },
  projects: { type: Array, default: () => [] },
  initialProjectId: { type: [Number, String], default: '' },
  csrf: { type: String, required: true },
  endpoints: { type: Object, required: true }
});

const itemsByCategory = ref(props.initialItemsByCategory);
const score = ref(props.initialScore);
const projects = ref(props.projects);
const projectId = ref(props.initialProjectId || '');
const loading = ref(false);

const fetchAuditData = async () => {
  if (!projectId.value) return;
  loading.value = true;
  try {
    const params = new URLSearchParams({ project_id: projectId.value });
    const response = await fetch(`${props.endpoints.index}?${params.toString()}`, {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    });
    const data = await response.json();
    itemsByCategory.value = data.itemsByCategory || {};
    score.value = data.score || 0;
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
};

const seedChecklist = async () => {
  if (!confirm('This will load the default checklist items if they do not exist. Continue?')) return;
  
  loading.value = true;
  try {
    const response = await fetch(props.endpoints.seed, {
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
        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', title: 'Seeded', message: data.message } }));
      }
      await fetchAuditData();
    } else {
      alert(data.message);
    }
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
};

const updateStatus = async (item) => {
  try {
    const response = await fetch(`${props.endpoints.base}/${item.id}/status`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': props.csrf,
        'Accept': 'application/json'
      },
      body: JSON.stringify({ status: item.status })
    });
    const data = await response.json();
    if (data.success) {
      // Re-fetch to update score immediately from backend logic
      await fetchAuditData();
      
      if (window.dispatchEvent) {
        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', title: 'Updated', message: data.message } }));
      }
    }
  } catch (e) {
    console.error(e);
  }
};

const updateNotes = async (item) => {
  try {
    await fetch(`${props.endpoints.base}/${item.id}/notes`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': props.csrf,
        'Accept': 'application/json'
      },
      body: JSON.stringify({ notes: item.notes })
    });
  } catch (e) {
    console.error(e);
  }
};

function getScoreColor(s) {
  if (s >= 80) return 'var(--color-success)';
  if (s >= 60) return 'var(--color-warning)';
  return 'var(--color-danger)';
}

function getPassCount() {
  let count = 0;
  for (const category in itemsByCategory.value) {
    itemsByCategory.value[category].forEach(item => {
      if (item.status === 'pass') count++;
    });
  }
  return count;
}

function getFailCount() {
  let count = 0;
  for (const category in itemsByCategory.value) {
    itemsByCategory.value[category].forEach(item => {
      if (item.status === 'fail') count++;
    });
  }
  return count;
}

function getRowBackground(status) {
  const map = {
    'fail': 'rgba(239, 68, 68, 0.05)',
    'pass': 'rgba(16, 185, 129, 0.05)',
    'partial': 'rgba(245, 158, 11, 0.05)'
  };
  return map[status] || 'transparent';
}

function getSeverityBadgeClass(severity) {
  const map = { 'critical': 'danger', 'high': 'warning', 'medium': 'info' };
  return map[severity] || 'muted';
}
</script>

<style scoped>
.form-control-sm {
  padding: 4px 8px;
  font-size: 13px;
}
.badge.danger {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
  border: 1px solid rgba(239, 68, 68, 0.2);
}
</style>
