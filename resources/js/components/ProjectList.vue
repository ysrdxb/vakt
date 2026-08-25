<template>
  <div>
    <!-- Page Header -->
    <div class="page-header" style="margin-bottom: 24px; display: flex; align-items: center; gap: 16px;">
      <div style="width: 48px; height: 48px; background: rgba(59,130,246,0.1); border: 1px solid rgba(59,130,246,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #3b82f6;">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:24px;height:24px">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
        </svg>
      </div>
      <div style="flex: 1;">
        <h1 style="font-size: 24px; font-weight: 600; color: #f8fafc; margin: 0;">Projects</h1>
        <p style="color: #94a3b8; font-size: 14px; margin: 4px 0 0;">Manage your projects and websites.</p>
      </div>
      <div>
        <Link :href="route('projects.create')" class="btn btn-primary">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:18px;height:18px;margin-right:6px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
          Add Project
        </Link>
      </div>
    </div>

    <!-- Filters -->
    <div class="card" style="margin-bottom: 24px;">
      <div class="card-body" style="padding: 16px 20px; display: flex; gap: 16px; align-items: center;">
        <div style="flex: 1; position: relative;">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:18px;height:18px;position:absolute;left:12px;top:10px;color:#64748b;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input type="text" v-model="searchQuery" @input="fetchProjects" class="form-control" placeholder="Search by domain or name..." style="padding-left: 38px;" />
        </div>
        <div style="width: 200px;">
          <select v-model="filterStatus" @change="fetchProjects" class="form-control">
            <option value="">All Statuses</option>
            <option value="ok">OK - Healthy</option>
            <option value="warning">Warning</option>
            <option value="critical">Critical</option>
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
              <th style="padding:12px 20px; color:#94a3b8; font-size:12px; text-transform:uppercase; font-weight:600;">Status</th>
              <th style="padding:12px 20px; color:#94a3b8; font-size:12px; text-transform:uppercase; font-weight:600;">Project Name & Domain</th>
              <th style="padding:12px 20px; color:#94a3b8; font-size:12px; text-transform:uppercase; font-weight:600;">Security Score</th>
              <th style="padding:12px 20px; color:#94a3b8; font-size:12px; text-transform:uppercase; font-weight:600;">Stack</th>
              <th style="padding:12px 20px; color:#94a3b8; font-size:12px; text-transform:uppercase; font-weight:600; text-align:right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading" style="border-bottom:1px solid #334155;">
              <td colspan="5" style="padding:40px; text-align:center; color:#94a3b8;">
                Loading...
              </td>
            </tr>
            <tr v-else-if="projectsList.length === 0" style="border-bottom:1px solid #334155;">
              <td colspan="5" style="padding:40px; text-align:center; color:#94a3b8;">
                <div style="font-weight:500;color:#fff;">No projects found.</div>
              </td>
            </tr>
            <tr v-else v-for="project in projectsList" :key="project.id" style="border-bottom:1px solid #334155; transition:background .15s;" class="hover-surface">
              <td style="padding:12px 20px;">
                <span class="badge" :class="getStatusClass(project.status)">
                  <span class="status-dot" :class="project.status"></span> {{ project.status.toUpperCase() }}
                </span>
              </td>
              <td style="padding:12px 20px;">
                <div style="font-weight:600; color:#f8fafc; font-size:14px;">{{ project.name }}</div>
                <div style="font-family:var(--font-mono); font-size:12px; color:#94a3b8;">{{ project.domain }}</div>
              </td>
              <td style="padding:12px 20px;">
                <span class="badge" :class="getScoreClass(project.security_score)">{{ project.security_score }}/100</span>
              </td>
              <td style="padding:12px 20px;">
                <div style="display:flex; align-items:center; gap:6px;">
                  <span class="badge muted">{{ project.stack }}</span>
                  <span class="badge muted">PHP {{ project.php_version }}</span>
                </div>
              </td>
              <td style="padding:12px 20px; text-align:right;">
                <div style="display:flex; align-items:center; justify-content:flex-end; gap:8px;">
                  <!-- Toggle Active -->
                  <button @click="toggleActive(project)" class="btn btn-sm" :class="project.active ? 'btn-success' : 'btn-secondary'" :title="project.active ? 'Disable Monitoring' : 'Enable Monitoring'">
                    <svg v-if="project.active" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                  </button>
                  
                  <Link :href="route('projects.show', project.id)" class="btn btn-sm btn-secondary" title="View Details">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                  </Link>
                  
                  <Link :href="route('projects.edit', project.id)" class="btn btn-sm btn-secondary" title="Edit Project">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                  </Link>
                  
                  <button @click="deleteProject(project)" class="btn btn-sm btn-danger" title="Delete Project">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
  projects: { type: Array, default: () => [] }
});

const projectsList = ref(props.projects);
const searchQuery = ref('');
const filterStatus = ref('');
const loading = ref(false);
let searchTimeout = null;

watch(() => props.projects, (newVal) => {
  projectsList.value = newVal;
});

function getStatusClass(status) {
  if (status === 'ok') return 'success';
  if (status === 'warning') return 'warning';
  return 'danger';
}

function getScoreClass(score) {
  if (score >= 80) return 'success';
  if (score >= 60) return 'warning';
  return 'danger';
}

async function fetchProjects() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    loading.value = true;
    router.get(
      route('projects.index'),
      { search: searchQuery.value, filterStatus: filterStatus.value },
      {
        preserveState: true,
        replace: true,
        onFinish: () => loading.value = false
      }
    );
  }, 300);
}

async function toggleActive(project) {
  try {
    const res = await axios.post(route('projects.toggle-active', project.id));
    if (res.data.success) {
      project.active = res.data.active;
      if (window.dispatchEvent) {
        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', title: 'Updated', message: res.data.message } }));
      }
    }
  } catch (e) {
    console.error('Failed to toggle', e);
  }
}

function deleteProject(project) {
  if (!confirm(`Are you sure you want to delete ${project.domain}? This action cannot be undone.`)) return;
  
  router.delete(route('projects.destroy', project.id), {
    preserveState: true,
    onSuccess: () => {
      if (window.dispatchEvent) {
        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', title: 'Deleted', message: 'Project deleted successfully.' } }));
      }
    }
  });
}
</script>

<style scoped>
.hover-surface:hover {
  background: var(--color-surface-2) !important;
}
.btn-sm { padding: 6px; }
.btn-success { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); }
.btn-success:hover { background: rgba(16, 185, 129, 0.2); }
.btn-danger { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); }
.btn-danger:hover { background: rgba(239, 68, 68, 0.2); }
</style>
