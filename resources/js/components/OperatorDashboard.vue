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
        <h1 style="font-size: 24px; font-weight: 600; color: #f8fafc; margin: 0;">Dashboard</h1>
        <p style="color: #94a3b8; font-size: 14px; margin: 4px 0 0;">System Overview & Monitoring</p>
      </div>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-4" style="margin-bottom: 24px;">
      <!-- Projects Monitored -->
      <div class="stat-card primary">
        <div class="stat-glow"></div>
        <div class="stat-label">Projects Monitored</div>
        <div class="stat-value">{{ projects.length }}</div>
      </div>

      <!-- Open Incidents -->
      <div class="stat-card" :class="openIncidents.length > 0 ? 'danger' : 'success'">
        <div class="stat-glow"></div>
        <div class="stat-label">Open Incidents</div>
        <div class="stat-value">{{ openIncidents.length }}</div>
        <div v-if="p1Count > 0" class="stat-trend up">+{{ p1Count }} P1</div>
      </div>

      <!-- Security Score -->
      <div class="stat-card" :class="scoreColor">
        <div class="stat-glow"></div>
        <div class="stat-label">Security Score</div>
        <div class="stat-value">{{ overallScore }}/100</div>
      </div>

      <!-- Last Check -->
      <div class="stat-card muted">
        <div class="stat-glow"></div>
        <div class="stat-label">Last Check</div>
        <div class="stat-value" style="font-size: 1.5rem;">
          {{ formatDiffForHumans(lastCheckTime || (recentChecks.length ? recentChecks[0].checked_at : null)) }}
        </div>
      </div>
    </div>

    <div class="grid grid-3" style="gap: 24px; margin-bottom: 24px;">
      <!-- ===== PULSE RING / SECURITY SCORE ===== -->
      <div class="card" style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:40px;text-align:center;">
        <div class="pulse-ring-container" style="margin-bottom:20px">
          <div class="pulse-ring" :class="scoreColor"></div>
          <div class="pulse-ring" :class="scoreColor"></div>
          <div class="pulse-ring" :class="scoreColor"></div>
          <div class="score-ring" :class="scoreColor">
            <span class="score-number">{{ overallScore }}</span>
            <span class="score-label">Security Score</span>
          </div>
        </div>
        <div style="font-weight:600;font-size:16px;color:#fff;margin-bottom:4px;">
          {{ p1Count > 0 ? 'Active Threats Detected' : 'All Systems Monitored' }}
        </div>
        <div style="font-size:13px;color:var(--color-muted);margin-bottom:12px;">
          {{ projects.length }} projects monitored
        </div>
        <div class="badge" :class="scoreColor">
          {{ p1Count > 0 ? p1Count + ' P1 CRITICAL ACTIVE' : 'NO CRITICAL THREATS' }}
        </div>
      </div>

      <!-- ===== LIVE INCIDENT FEED ===== -->
      <div class="card" style="grid-column: span 2;">
        <div class="card-header">
          <div class="card-title">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            Live Incident Feed
          </div>
          <div style="display:flex;align-items:center;gap:12px">
            <span class="badge warning" style="font-size:11px">Live</span>
            <a href="/incidents" class="btn btn-ghost btn-sm">View All</a>
          </div>
        </div>
        <div class="card-body" style="padding:0">
          <template v-if="openIncidents.length > 0">
            <div v-for="inc in openIncidents.slice(0, 5)" :key="inc.id" style="display:flex;align-items:center;gap:16px;padding:14px 20px;border-bottom:1px solid var(--color-border);transition:background .15s" class="hover-surface">
              <span class="badge" :class="getBadgeClass(inc.severity)">{{ getSeverityLabel(inc.severity) }}</span>
              <div style="flex:1;min-width:0">
                <div style="font-weight:600;font-size:14px;color:var(--color-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ inc.title }}</div>
                <div style="font-size:12px;color:var(--color-muted);margin-top:2px">{{ inc.project ? inc.project.domain : 'Global' }} &bull; {{ formatDiffForHumans(inc.created_at) }}</div>
              </div>
              <a :href="'/incidents/' + inc.id" class="btn btn-ghost btn-sm">View</a>
            </div>
          </template>
          <div v-else style="padding:40px;text-align:center;color:var(--color-muted)">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:40px;height:40px;margin:0 auto 12px;color:var(--color-success);opacity:0.8">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div style="font-weight:500;color:#fff;">No open incidents</div>
            <div style="font-size:13px;margin-top:4px;">All systems operational.</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Middle Row: Projects & Chart -->
    <div class="grid grid-2" style="margin-bottom: 24px;">
      <!-- ===== PROJECT HEALTH LIST ===== -->
      <div class="card">
        <div class="card-header">
          <div class="card-title">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            Project Health
          </div>
          <a href="/projects" class="btn btn-ghost btn-sm">All Projects</a>
        </div>
        <div class="card-body" style="padding:0">
          <template v-if="projects.length > 0">
            <a v-for="project in projects.slice(0, 8)" :key="project.id" :href="'/projects/' + project.id" style="text-decoration:none;color:inherit;display:block;">
              <div style="display:flex;align-items:center;gap:12px;padding:12px 20px;border-bottom:1px solid var(--color-border);transition:background .15s" class="hover-surface">
                <span class="project-status-indicator" :class="project.status"></span>
                <span style="font-family:var(--font-mono);font-size:0.85rem;color:var(--color-text);flex:1">{{ project.domain }}</span>
                <span v-if="project.incidents && project.incidents.length > 0" class="badge danger">{{ project.incidents.length }} incidents</span>
                <span v-else class="badge success">Clean</span>
                <span style="font-size:0.78rem;color:var(--color-muted)">{{ project.security_score ?? 100 }}/100</span>
              </div>
            </a>
          </template>
          <div v-else style="padding:40px;text-align:center;color:var(--color-muted)">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:40px;height:40px;margin:0 auto 12px;opacity:0.5">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
            </svg>
            <div style="font-weight:500;color:#fff;">No projects</div>
            <div style="font-size:13px;margin-top:4px;">Add your first project to start monitoring.</div>
          </div>
        </div>
      </div>

      <!-- ===== ERROR TREND CHART ===== -->
      <div class="card">
        <div class="card-header">
          <div class="card-title">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            Error Trend (7 days)
          </div>
        </div>
        <div class="card-body">
          <div id="errorTrendChart" ref="chartEl"></div>
        </div>
      </div>
    </div>

    <!-- Bottom Row: Recent Checks -->
    <div class="card">
      <div class="card-header">
        <div class="card-title">Recent Monitoring Activity</div>
      </div>
      <div class="card-body" style="padding:0; overflow-x: auto;">
        <template v-if="recentChecks.length > 0">
          <table class="table" style="width:100%; border-collapse:collapse; text-align:left;">
            <thead>
              <tr style="border-bottom:1px solid #334155;">
                <th style="padding:12px 20px; color:#94a3b8; font-size:12px; text-transform:uppercase; font-weight:600;">Project</th>
                <th style="padding:12px 20px; color:#94a3b8; font-size:12px; text-transform:uppercase; font-weight:600;">Status</th>
                <th style="padding:12px 20px; color:#94a3b8; font-size:12px; text-transform:uppercase; font-weight:600;">Checked</th>
                <th style="padding:12px 20px; color:#94a3b8; font-size:12px; text-transform:uppercase; font-weight:600;">Lines Scanned</th>
                <th style="padding:12px 20px; color:#94a3b8; font-size:12px; text-transform:uppercase; font-weight:600;">Errors</th>
                <th style="padding:12px 20px; color:#94a3b8; font-size:12px; text-transform:uppercase; font-weight:600;">Warnings</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="check in recentChecks" :key="check.id" style="border-bottom:1px solid #334155;">
                <td style="padding:12px 20px;"><span style="font-family:var(--font-mono); font-size:0.82rem;">{{ check.project?.domain || 'Unknown' }}</span></td>
                <td style="padding:12px 20px;">
                  <span class="badge" :class="check.status === 'ok' ? 'success' : (check.status === 'warning' ? 'warning' : 'danger')">{{ check.status }}</span>
                </td>
                <td style="padding:12px 20px;"><span style="font-family:var(--font-mono); font-size:0.82rem; color:#94a3b8;">{{ formatDiffForHumans(check.checked_at) }}</span></td>
                <td style="padding:12px 20px;">{{ (check.log_lines_scanned || 0).toLocaleString() }}</td>
                <td style="padding:12px 20px;"><span :style="{ color: check.errors_found > 0 ? '#ef4444' : '#94a3b8' }">{{ check.errors_found || 0 }}</span></td>
                <td style="padding:12px 20px;"><span :style="{ color: check.warnings_found > 0 ? '#f59e0b' : '#94a3b8' }">{{ check.warnings_found || 0 }}</span></td>
              </tr>
            </tbody>
          </table>
        </template>
        <div v-else style="padding:40px;text-align:center;color:var(--color-muted)">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:40px;height:40px;margin:0 auto 12px;opacity:0.5">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div style="font-weight:500;color:#fff;">No recent checks</div>
          <div style="font-size:13px;margin-top:4px;">Monitoring will begin automatically once projects are configured.</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const props = defineProps({
  projects: { type: Array, default: () => [] },
  openIncidents: { type: Array, default: () => [] },
  p1Count: { type: Number, default: 0 },
  overallScore: { type: Number, default: 0 },
  scoreColor: { type: String, default: 'success' },
  recentChecks: { type: Array, default: () => [] },
  lastCheckTime: { type: String, default: null },
  chartData: { type: Object, default: () => ({ categories: [], series: [] }) },
  agentStatus: { type: Array, default: () => [] },
});

const chartEl = ref(null);

function formatDiffForHumans(dateString) {
  if (!dateString) return 'Never';
  const date = new Date(dateString);
  const diffInSeconds = Math.floor((new Date() - date) / 1000);
  if (diffInSeconds < 60) return 'Just now';
  if (diffInSeconds < 3600) return Math.floor(diffInSeconds / 60) + ' min ago';
  if (diffInSeconds < 86400) return Math.floor(diffInSeconds / 3600) + ' hrs ago';
  return Math.floor(diffInSeconds / 86400) + ' days ago';
}

function getBadgeClass(severity) {
  const map = { p1: 'critical', p2: 'warning', p3: 'info', p4: 'success' };
  return map[severity] || 'muted';
}

function getSeverityLabel(severity) {
  const labels = { p1: 'Critical', p2: 'High', p3: 'Medium', p4: 'Low' };
  return labels[severity] || severity;
}

function renderChart() {
  if (!chartEl.value) return;

  const initChart = () => {
    if (typeof ApexCharts !== 'undefined' && chartEl.value) {
      chartEl.value.innerHTML = '';
      new ApexCharts(chartEl.value, {
        chart: {
          type: 'area',
          height: 200,
          background: 'transparent',
          toolbar: { show: false },
          animations: { enabled: true, speed: 600 },
        },
        theme: { mode: 'dark' },
        colors: ['#ff4757'],
        fill: {
          type: 'gradient',
          gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.4,
            opacityTo: 0.05,
            stops: [0, 100],
          }
        },
        series: [{ name: 'Errors & Warnings', data: props.chartData.series }],
        xaxis: {
          categories: props.chartData.categories,
          labels: { style: { colors: '#94a3b8', fontFamily: 'Inter' } },
        },
        yaxis: {
          labels: { style: { colors: '#8899aa' } },
          min: 0,
        },
        grid: { borderColor: '#1f2d45', strokeDashArray: 4 },
        stroke: { width: 2, curve: 'smooth' },
        tooltip: { theme: 'dark' },
        dataLabels: { enabled: false },
      }).render();
    }
  };

  if (typeof ApexCharts !== 'undefined') {
    initChart();
  } else {
    let attempts = 0;
    const timer = setInterval(() => {
      attempts++;
      if (typeof ApexCharts !== 'undefined') {
        clearInterval(timer);
        initChart();
      } else if (attempts > 20) {
        clearInterval(timer);
      }
    }, 250);
  }
}

onMounted(() => {
  renderChart();
});
</script>

<style scoped>
.hover-surface:hover {
  background: var(--color-surface-2) !important;
}
</style>
