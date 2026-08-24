<template>
  <div>
    <div class="breadcrumbs">
      <a href="/dashboard">Dashboard</a>
      <span class="sep">›</span>
      <a href="/incidents">Incidents</a>
      <span class="sep">›</span>
      <span class="current">{{ incident.title }}</span>
    </div>

    <div class="card mb-6" style="border-top:4px solid var(--color-danger)">
      <div class="card-body">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px">
          <div>
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px">
              <h1 style="margin:0;font-size:1.5rem">{{ incident.title }}</h1>
              <span class="badge" :class="getSeverityBadgeClass(incident.severity)">{{ incident.severity.toUpperCase() }}</span>
              <span class="badge" :class="getStatusBadgeClass(incident.status)">{{ incident.status.replace('_', ' ') }}</span>
            </div>
            <div style="font-family:var(--font-mono);color:var(--color-primary);margin-bottom:12px">
              <a :href="'/projects/' + incident.project.id" style="color:inherit;text-decoration:none;">{{ incident.project.domain }}</a>
            </div>
            <div style="color:var(--color-text-dim);font-size:0.875rem">Detected {{ formatDiffForHumans(incident.detected_at) }}</div>
          </div>
          
          <div style="display:flex;gap:12px">
            <button v-if="incident.status === 'open'" @click="transitionStatus('investigating')" class="btn btn-warning">Acknowledge</button>
            <button v-if="['open', 'investigating'].includes(incident.status)" @click="transitionStatus('contained')" class="btn btn-info">Mark Contained</button>
            <button v-if="['investigating', 'contained'].includes(incident.status)" @click="transitionStatus('resolved')" class="btn btn-success">Mark Resolved</button>
            <button v-if="incident.status === 'resolved'" @click="transitionStatus('closed')" class="btn btn-ghost">Close Incident</button>
            <button @click="editMode = !editMode" class="btn btn-ghost">{{ editMode ? 'Cancel Edit' : 'Edit Notes' }}</button>
          </div>
        </div>
      </div>
      <div class="card-footer" style="background:var(--color-surface-2);display:flex;gap:32px;flex-wrap:wrap">
        <div>
          <div style="font-size:0.7rem;text-transform:uppercase;color:var(--color-muted);margin-bottom:4px">Reported By</div>
          <div style="font-size:1rem;font-weight:500">{{ incident.reporter }}</div>
        </div>
        <div>
          <div style="font-size:0.7rem;text-transform:uppercase;color:var(--color-muted);margin-bottom:4px">Assignee</div>
          <div style="font-size:1rem;font-weight:500">{{ incident.assignee || 'Unassigned' }}</div>
        </div>
        <div>
          <div style="font-size:0.7rem;text-transform:uppercase;color:var(--color-muted);margin-bottom:4px">Time to Detect</div>
          <div style="font-size:1rem;font-weight:500">{{ incident.time_to_detect || 'N/A' }}</div>
        </div>
        <div>
          <div style="font-size:0.7rem;text-transform:uppercase;color:var(--color-muted);margin-bottom:4px">Time to Resolve</div>
          <div style="font-size:1rem;font-weight:500">{{ incident.time_to_resolve || 'N/A' }}</div>
        </div>
      </div>
    </div>

    <!-- AI Executive Summary -->
    <div v-if="incident.ai_summary || incident.ai_diagnosis" class="card mb-6" style="border: 1px solid rgba(139, 92, 246, 0.3); background: linear-gradient(145deg, rgba(17, 24, 39, 1) 0%, rgba(30, 27, 75, 0.4) 100%); position: relative; overflow: hidden;">
      <div style="position: absolute; top: -10px; right: -10px; opacity: 0.05;">
        <svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
      </div>
      <div class="card-header" style="border-bottom: 1px solid rgba(139, 92, 246, 0.1);">
        <div class="card-title" style="display: flex; align-items: center; gap: 8px; color: #a78bfa;">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 18px; height: 18px;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
          </svg>
          AI Executive Summary
        </div>
      </div>
      <div class="card-body">
        <div v-if="incident.ai_summary" style="margin-bottom: 20px;">
          <p style="font-size: 1.05rem; line-height: 1.6; color: #e2e8f0;">{{ incident.ai_summary }}</p>
        </div>
        
        <div v-if="incident.ai_diagnosis" style="background: rgba(0, 0, 0, 0.2); border-radius: 6px; padding: 16px; border-left: 3px solid #8b5cf6;">
          <div style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: .06em; color: #a78bfa; margin-bottom: 8px;">Technical Diagnosis</div>
          <div style="font-size: 0.875rem; color: #cbd5e1; line-height: 1.5; white-space: pre-wrap;">{{ incident.ai_diagnosis }}</div>
        </div>
      </div>

      <div v-if="incident.project && incident.project.server_type === 'external_agent'" class="card-footer" style="background: rgba(139, 92, 246, 0.05); border-top: 1px solid rgba(139, 92, 246, 0.1); display: flex; gap: 8px; flex-wrap: wrap;">
        <div style="font-size: 0.75rem; color: #a78bfa; width: 100%; margin-bottom: 4px;"><strong>Auto-Remediation:</strong> Push commands directly to the agent.</div>
        <button class="btn btn-sm" style="background: rgba(220, 38, 38, 0.2); color: #fca5a5; border: 1px solid rgba(220, 38, 38, 0.4);" @click="executeAgentCommand('block_ip')">
          Block IP in Firewall
        </button>
        <button class="btn btn-sm" style="background: rgba(59, 130, 246, 0.2); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.4);" @click="executeAgentCommand('fix_permissions')">
          Fix Storage Permissions
        </button>
        <button class="btn btn-sm" style="background: rgba(16, 185, 129, 0.2); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.4);" @click="executeAgentCommand('clear_cache')">
          Clear App Cache
        </button>
      </div>
    </div>

    <div class="grid grid-2 gap-6">

      <!-- Notes -->
      <div class="card">
        <div class="card-header">
          <div class="card-title">Investigation Notes</div>
          <div v-if="editMode" style="display:flex;gap:8px">
            <button @click="saveNotes" class="btn btn-primary btn-sm">Save</button>
            <button @click="editMode = false" class="btn btn-ghost btn-sm">Cancel</button>
          </div>
          <button v-else @click="editMode = true" class="btn btn-ghost btn-sm">Edit</button>
        </div>
        <div class="card-body">
          <div v-if="editMode">
            <div class="form-group">
              <label class="form-label">Description</label>
              <textarea v-model="notes.notes" class="form-control" rows="4" placeholder="Incident description and impact..."></textarea>
            </div>
            <div class="form-group">
              <label class="form-label">Root Cause</label>
              <textarea v-model="notes.rootCause" class="form-control" rows="3" placeholder="Root cause analysis..."></textarea>
            </div>
            <div class="form-group">
              <label class="form-label">Resolution Notes</label>
              <textarea v-model="notes.resolutionNotes" class="form-control" rows="3" placeholder="Steps taken to resolve..."></textarea>
            </div>
            <div class="form-group">
              <label class="form-label">Prevention Notes</label>
              <textarea v-model="notes.preventionNotes" class="form-control" rows="3" placeholder="How to prevent recurrence..."></textarea>
            </div>
          </div>
          <div v-else>
            <div v-if="incident.description" style="margin-bottom:16px">
              <div style="font-size:0.72rem;text-transform:uppercase;letter-spacing:.06em;color:var(--color-muted);margin-bottom:6px">Description</div>
              <p style="font-size:0.875rem;color:var(--color-text)">{{ incident.description }}</p>
            </div>
            <div v-if="incident.root_cause" style="margin-bottom:16px">
              <div style="font-size:0.72rem;text-transform:uppercase;letter-spacing:.06em;color:var(--color-muted);margin-bottom:6px">Root Cause</div>
              <p style="font-size:0.875rem;color:var(--color-text)">{{ incident.root_cause }}</p>
            </div>
            <div v-if="incident.resolution_notes" style="margin-bottom:16px">
              <div style="font-size:0.72rem;text-transform:uppercase;letter-spacing:.06em;color:var(--color-muted);margin-bottom:6px">Resolution</div>
              <p style="font-size:0.875rem;color:var(--color-text)">{{ incident.resolution_notes }}</p>
            </div>
            <p v-if="!incident.description && !incident.root_cause" class="text-muted text-sm">No notes yet. Click "Edit" to add investigation notes.</p>
          </div>
        </div>
      </div>

      <!-- Timeline -->
      <div class="card">
        <div class="card-header">
          <div class="card-title">Activity Timeline</div>
        </div>
        <div class="card-body">
          <div v-if="!incident.timeline || incident.timeline.length === 0" style="padding:24px;text-align:center;color:var(--color-muted)">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:40px;height:40px;margin:0 auto 12px;opacity:0.5">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
            <div style="font-weight:500;color:#fff;">No timeline events</div>
            <div style="font-size:13px;margin-top:4px;">Actions will be logged here.</div>
          </div>
          <div v-else class="timeline">
            <div v-for="event in incident.timeline" :key="event.id" class="timeline-item">
              <div class="timeline-dot">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="timeline-content">
                <div class="timeline-action">{{ event.action }}</div>
                <div class="timeline-meta">{{ event.performed_by }} · {{ formatEventTime(event.performed_at) }}</div>
                <div v-if="event.description" class="timeline-description">{{ event.description }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  incident: { type: Object, required: true },
  csrf: { type: String, required: true },
  endpoints: { type: Object, required: true }
});

const incident = ref(props.incident);
const editMode = ref(false);

const notes = ref({
  notes: incident.value.description || '',
  rootCause: incident.value.root_cause || '',
  resolutionNotes: incident.value.resolution_notes || '',
  preventionNotes: incident.value.prevention_notes || ''
});

const transitionStatus = async (status) => {
  try {
    const response = await fetch(props.endpoints.transitionStatus, {
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
      incident.value = data.incident;
      if (!incident.value.timeline) incident.value.timeline = [];
      incident.value.timeline.unshift(data.timeline);
      
      if (window.dispatchEvent) {
        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', title: 'Status Updated', message: data.message } }));
      }
    }
  } catch (e) {
    console.error(e);
  }
};

const saveNotes = async () => {
  try {
    const response = await fetch(props.endpoints.saveNotes, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': props.csrf,
        'Accept': 'application/json'
      },
      body: JSON.stringify(notes.value)
    });
    const data = await response.json();
    if (data.success) {
      incident.value = data.incident;
      if (!incident.value.timeline) incident.value.timeline = [];
      incident.value.timeline.unshift(data.timeline);
      editMode.value = false;
      
      if (window.dispatchEvent) {
        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', title: 'Saved', message: data.message } }));
      }
    }
  } catch (e) {
    console.error(e);
  }
};

const executeAgentCommand = async (command) => {
  let ip = null;
  if (command === 'block_ip') {
    ip = prompt('Enter IP address to block:');
    if (!ip) return;
  }
  
  try {
    const response = await fetch(props.endpoints.executeCommand, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': props.csrf,
        'Accept': 'application/json'
      },
      body: JSON.stringify({ command, ip })
    });
    const data = await response.json();
    
    if (data.timeline) {
      if (!incident.value.timeline) incident.value.timeline = [];
      incident.value.timeline.unshift(data.timeline);
    }
    
    if (window.dispatchEvent) {
      window.dispatchEvent(new CustomEvent('toast', { detail: { type: data.status === 'success' ? 'success' : 'error', title: 'Command Sent', message: data.message } }));
    } else {
      alert(data.message);
    }
  } catch (e) {
    console.error(e);
  }
};

function getSeverityBadgeClass(severity) {
  const map = { p1: 'danger', p2: 'warning', p3: 'info', p4: 'success' };
  return map[severity] || 'muted';
}

function getStatusBadgeClass(status) {
  const map = { 'open':'danger', 'investigating':'warning', 'contained':'info', 'resolved':'success', 'closed':'success' };
  return map[status] || 'muted';
}

function formatDiffForHumans(dateString) {
  if (!dateString) return 'Unknown';
  const date = new Date(dateString);
  const diffInSeconds = Math.floor((new Date() - date) / 1000);
  if (diffInSeconds < 60) return 'Just now';
  if (diffInSeconds < 3600) return Math.floor(diffInSeconds / 60) + ' min ago';
  if (diffInSeconds < 86400) return Math.floor(diffInSeconds / 3600) + ' hrs ago';
  return Math.floor(diffInSeconds / 86400) + ' days ago';
}

function formatEventTime(dateString) {
  if (!dateString) return '';
  const d = new Date(dateString);
  const month = d.toLocaleString('en-US', { month: 'short' });
  const day = d.getDate().toString().padStart(2, '0');
  const hours = d.getHours().toString().padStart(2, '0');
  const mins = d.getMinutes().toString().padStart(2, '0');
  const secs = d.getSeconds().toString().padStart(2, '0');
  return `${month} ${day}, ${hours}:${mins}:${secs}`;
}
</script>

<style scoped>
.timeline {
  position: relative;
  padding-left: 24px;
}
.timeline::before {
  content: '';
  position: absolute;
  left: 11px;
  top: 0;
  bottom: 0;
  width: 2px;
  background: var(--color-surface-3);
}
.timeline-item {
  position: relative;
  margin-bottom: 24px;
}
.timeline-item:last-child {
  margin-bottom: 0;
}
.timeline-dot {
  position: absolute;
  left: -24px;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: var(--color-surface-2);
  border: 2px solid var(--color-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--color-primary);
}
.timeline-dot svg {
  width: 12px;
  height: 12px;
}
.timeline-content {
  padding-left: 12px;
}
.timeline-action {
  font-weight: 500;
  font-size: 0.9rem;
  color: var(--color-text);
  margin-bottom: 2px;
}
.timeline-meta {
  font-size: 0.75rem;
  color: var(--color-muted);
  margin-bottom: 4px;
}
.timeline-description {
  font-size: 0.85rem;
  color: var(--color-text-dim);
  background: var(--color-surface-2);
  padding: 8px 12px;
  border-radius: 6px;
  margin-top: 4px;
}
</style>
