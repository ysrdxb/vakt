<template>
  <div>
    <!-- Page Header -->
    <div class="page-header" style="margin-bottom: 24px; display: flex; align-items: center; gap: 16px;">
      <div style="width: 48px; height: 48px; background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #3b82f6;">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:24px;height:24px">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
      </div>
      <div>
        <h1 style="font-size: 24px; font-weight: 600; color: #f8fafc; margin: 0;">Improvement Pipeline</h1>
        <p style="color: #94a3b8; font-size: 14px; margin: 4px 0 0;">Track proposed improvements through approval and implementation</p>
      </div>
    </div>

    <!-- Controls -->
    <div class="card mb-6" style="padding: 16px 20px;">
      <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <select v-model="projectId" @change="fetchItems" class="form-control" style="width: 250px;">
          <option value="">All Projects</option>
          <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.domain }}</option>
        </select>
        
        <button @click="showForm = !showForm" class="btn btn-primary">
          {{ showForm ? 'Cancel' : 'Add Improvement' }}
        </button>
      </div>
    </div>

    <!-- Add Form -->
    <div v-if="showForm" class="card mb-6">
      <div class="card-header">
        <div class="card-title">Propose Improvement</div>
      </div>
      <form @submit.prevent="addImprovement">
        <div class="card-body">
          <div class="grid grid-2 gap-6 mb-4" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1rem;">
            <div class="form-group">
              <label class="form-label" style="display: block; margin-bottom: 0.5rem;">Project</label>
              <select v-model="form.project_id" class="form-control" required>
                <option value="">-- Select Project --</option>
                <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.domain }}</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label" style="display: block; margin-bottom: 0.5rem;">Title</label>
              <input v-model="form.title" type="text" class="form-control" required />
            </div>
          </div>
          
          <div class="mb-4" style="margin-bottom: 1rem;">
            <label class="form-label" style="display: block; margin-bottom: 0.5rem;">Category</label>
            <select v-model="form.category" class="form-control" required>
              <option value="security">Security</option>
              <option value="performance">Performance</option>
              <option value="ux">UX/UI</option>
              <option value="feature">New Feature</option>
              <option value="technical_debt">Technical Debt</option>
              <option value="compliance">Compliance</option>
            </select>
          </div>
          
          <div class="mb-4" style="margin-bottom: 1rem;">
            <label class="form-label" style="display: block; margin-bottom: 0.5rem;">Description</label>
            <textarea v-model="form.description" class="form-control" rows="3" placeholder="What needs to be improved and why?"></textarea>
          </div>
          
          <div class="grid grid-2 gap-6" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
              <label class="form-label" style="display: block; margin-bottom: 0.5rem;">Priority</label>
              <select v-model="form.priority" class="form-control" required>
                <option value="high">High</option>
                <option value="medium">Medium</option>
                <option value="low">Low</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label" style="display: block; margin-bottom: 0.5rem;">Estimated Effort</label>
              <select v-model="form.effort" class="form-control" required>
                <option value="high">High (Days)</option>
                <option value="medium">Medium (Hours)</option>
                <option value="low">Low (Minutes)</option>
              </select>
            </div>
          </div>
        </div>
        <div class="card-footer" style="display: flex; justify-content: flex-end; gap: 8px;">
          <button type="button" @click="showForm = false" class="btn btn-ghost">Cancel</button>
          <button type="submit" class="btn btn-primary" :disabled="!form.project_id || submitting">Submit Proposal</button>
        </div>
      </form>
    </div>

    <!-- Kanban Board -->
    <div :class="{'opacity-50 pointer-events-none': loading}" style="display: flex; gap: 16px; overflow-x: auto; padding-bottom: 16px; min-height: 600px; width: 100%;">
      <div v-for="(colItems, status) in columnedItems" :key="status" style="flex: 0 0 320px; background: var(--color-surface-2); border-radius: 12px; padding: 16px; display: flex; flex-direction: column; gap: 12px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
          <div style="font-weight: 600; color: var(--color-text);">{{ getColumnName(status) }}</div>
          <span style="background: var(--color-surface); padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; color: var(--color-text-dim);">{{ colItems.length }}</span>
        </div>

        <div v-if="colItems.length === 0" style="border: 2px dashed var(--color-border); border-radius: 8px; padding: 24px; text-align: center; color: var(--color-muted); font-size: 0.875rem;">
          No items
        </div>

        <div v-for="item in colItems" :key="item.id" class="card p-4" style="background: var(--color-surface); box-shadow: 0 4px 6px rgba(0,0,0,0.2); padding: 16px;">
          <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
            <span class="badge" :class="getPriorityBadgeClass(item.priority)" style="font-size: 0.6rem;">{{ capitalize(item.priority) }}</span>
            <span class="badge muted" style="font-size: 0.6rem;">{{ capitalize(item.effort) }} Effort</span>
          </div>
          
          <div style="font-weight: 600; margin-bottom: 4px; color: var(--color-text);">{{ item.title }}</div>
          <a v-if="item.project" :href="'/projects/' + item.project.id" class="text-mono" style="font-size: 0.75rem; color: var(--color-primary); text-decoration: none;">{{ item.project.domain }}</a>
          
          <div style="margin-top: 12px; display: flex; gap: 4px; flex-wrap: wrap;">
            <template v-if="status === 'proposed'">
              <button @click="moveCard(item, 'client_review')" class="btn btn-ghost btn-sm" style="flex: 1;">Send to Client</button>
              <button @click="moveCard(item, 'approved')" class="btn btn-ghost btn-sm" style="flex: 1;">Auto-Approve</button>
            </template>
            <template v-else-if="status === 'client_review'">
              <button @click="moveCard(item, 'proposed')" class="btn btn-ghost btn-sm" style="flex: 1;">Revoke</button>
            </template>
            <template v-else-if="status === 'approved'">
              <button @click="moveCard(item, 'in_progress')" class="btn btn-primary btn-sm" style="flex: 1;">Start Work</button>
            </template>
            <template v-else-if="status === 'in_progress'">
              <button @click="moveCard(item, 'done')" class="btn btn-success btn-sm" style="flex: 1;">Complete</button>
            </template>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  initialColumnedItems: { type: Object, default: () => ({}) },
  projects: { type: Array, default: () => [] },
  initialProjectId: { type: [Number, String], default: '' },
  csrf: { type: String, required: true },
  endpoints: { type: Object, required: true }
});

const columnedItems = ref(props.initialColumnedItems);
const projects = ref(props.projects);
const projectId = ref(props.initialProjectId || '');
const loading = ref(false);
const submitting = ref(false);
const showForm = ref(false);

const form = ref({
  project_id: '',
  title: '',
  description: '',
  category: 'feature',
  priority: 'medium',
  effort: 'medium'
});

const fetchItems = async () => {
  loading.value = true;
  try {
    const params = new URLSearchParams({ project_id: projectId.value });
    const response = await fetch(`${props.endpoints.index}?${params.toString()}`, {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    });
    const data = await response.json();
    columnedItems.value = data.columnedItems;
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
};

const addImprovement = async () => {
  submitting.value = true;
  try {
    const response = await fetch(props.endpoints.store, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': props.csrf,
        'Accept': 'application/json'
      },
      body: JSON.stringify(form.value)
    });
    const data = await response.json();
    if (data.success) {
      if (window.dispatchEvent) {
        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', title: 'Added', message: data.message } }));
      }
      showForm.value = false;
      form.value = {
        project_id: '',
        title: '',
        description: '',
        category: 'feature',
        priority: 'medium',
        effort: 'medium'
      };
      fetchItems();
    }
  } catch (e) {
    console.error(e);
  } finally {
    submitting.value = false;
  }
};

const moveCard = async (item, status) => {
  try {
    const response = await fetch(`${props.endpoints.base}/${item.id}/status`, {
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
        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', title: 'Moved', message: data.message } }));
      }
      fetchItems();
    }
  } catch (e) {
    console.error(e);
  }
};

function getColumnName(key) {
  const map = {
    'proposed': 'Proposed',
    'client_review': 'Client Review',
    'approved': 'Approved',
    'in_progress': 'In Progress',
    'done': 'Done',
    'declined': 'Declined'
  };
  return map[key] || capitalize(key);
}

function capitalize(str) {
  if (!str) return '';
  return str.charAt(0).toUpperCase() + str.slice(1);
}

function getPriorityBadgeClass(priority) {
  const map = { 'high': 'danger', 'medium': 'warning', 'low': 'info' };
  return map[priority] || 'muted';
}
</script>

<style scoped>
.badge.danger {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
  border: 1px solid rgba(239, 68, 68, 0.2);
}
.badge.warning {
  background: rgba(245, 158, 11, 0.1);
  color: #f59e0b;
  border: 1px solid rgba(245, 158, 11, 0.2);
}
.badge.info {
  background: rgba(59, 130, 246, 0.1);
  color: #3b82f6;
  border: 1px solid rgba(59, 130, 246, 0.2);
}
.badge.muted {
  background: rgba(148, 163, 184, 0.1);
  color: #94a3b8;
  border: 1px solid rgba(148, 163, 184, 0.2);
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
