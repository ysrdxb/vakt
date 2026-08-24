<template>
  <div class="app-layout">

    <!-- Mobile Sidebar Backdrop -->
    <div
      class="sidebar-backdrop"
      :class="{ 'visible': sidebarOpen }"
      @click="sidebarOpen = false"
    ></div>

    <!-- ===================== SIDEBAR ===================== -->
    <aside class="sidebar" :class="{ 'open': sidebarOpen }">
      <div class="sidebar-logo">
        <div class="logo-icon">🛡️</div>
        <div>
          <div class="logo-text">Vakt</div>
          <div class="logo-sub">Monitoring</div>
        </div>
      </div>

      <nav class="sidebar-nav">
        <a :href="routes.dashboard" class="nav-item" :class="{ active: isRoute('dashboard') }">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
          </svg>
          Dashboard
        </a>

        <div class="nav-section-label">Monitoring</div>

        <a :href="routes.projects" class="nav-item" :class="{ active: isRoute('projects') }">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
          </svg>
          Projects
        </a>

        <a :href="routes.incidents" class="nav-item" :class="{ active: isRoute('incidents') }">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
          Incidents
          <span v-if="p1Count > 0" class="nav-badge">{{ p1Count }}</span>
        </a>

        <a :href="routes.daily_logs" class="nav-item" :class="{ active: isRoute('daily-logs') }">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          Daily Logs
        </a>

        <a :href="routes.logs" class="nav-item" :class="{ active: isRoute('logs') }">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          Log Viewer
        </a>

        <div class="nav-section-label">Security</div>

        <a :href="routes.file_integrity" class="nav-item" :class="{ active: isRoute('file-integrity') }">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
          </svg>
          File Integrity
        </a>

        <a :href="routes.audit" class="nav-item" :class="{ active: isRoute('audit') }">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
          </svg>
          Security Audit
        </a>

        <a :href="routes.vulnerabilities" class="nav-item" :class="{ active: isRoute('vulnerabilities') }">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
          Vulnerabilities
        </a>

        <div class="nav-section-label">Pipeline</div>

        <a :href="routes.improvements" class="nav-item" :class="{ active: isRoute('improvements') }">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
          </svg>
          Improvements
        </a>

        <a :href="routes.reports" class="nav-item" :class="{ active: isRoute('reports') }">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          SQA Reports
        </a>

        <a :href="routes.alerts" class="nav-item" :class="{ active: isRoute('alerts') }">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
          </svg>
          Alerts
        </a>

        <div class="nav-section-label">System</div>

        <a :href="routes.settings" class="nav-item" :class="{ active: isRoute('settings') }">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
          Settings
        </a>
      </nav>

      <div class="sidebar-footer">
        <div class="sidebar-user">
          <div class="user-avatar">{{ user.initials }}</div>
          <div class="user-info">
            <div class="user-name">{{ user.name }}</div>
            <div class="user-role">{{ user.role }}</div>
          </div>
        </div>
      </div>
    </aside>

    <!-- ===================== MAIN ===================== -->
    <main class="main-content">

      <!-- Topbar -->
      <header class="topbar">
        <button class="mobile-menu-btn" @click="sidebarOpen = true" title="Menu">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>

        <div class="topbar-search-wrap topbar-search">
          <svg class="topbar-search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input type="text" placeholder="Search projects, incidents..." />
        </div>

        <div class="topbar-actions">
          <a v-if="p1Count > 0" :href="routes.incidents" style="display:flex;align-items:center;gap:6px;padding:6px 12px;background:rgba(255,71,87,0.12);border:1px solid rgba(255,71,87,0.3);border-radius:8px;color:var(--color-danger);font-size:0.78rem;font-weight:700;">
            <span style="width:7px;height:7px;background:var(--color-danger);border-radius:50%;animation:blink 1s ease-in-out infinite;"></span>
            {{ p1Count }} P1 ACTIVE
          </a>

          <button class="icon-btn" title="Notifications">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:18px;height:18px">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span v-if="p1Count > 0" class="badge-dot"></span>
          </button>

          <button class="icon-btn" title="Logout" @click="logout">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:18px;height:18px">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
          </button>
        </div>
      </header>

      <!-- Page Content (server-rendered Blade injected here) -->
      <div class="page-body" v-html="pageContent"></div>

    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

// Read PHP data from JSON script tag
const pageData = JSON.parse(document.getElementById('__page_data__')?.textContent || '{}');

const user = ref(pageData.user || {});
const routes = ref(pageData.routes || {});
const p1Count = ref(pageData.p1_count || 0);
const activeRoute = ref(pageData.active_route || '');
const sidebarOpen = ref(false);
const pageContent = ref('');

const isRoute = (name) => activeRoute.value.includes(name);

const logout = async () => {
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = routes.value.logout;
  const csrf = document.createElement('input');
  csrf.type = 'hidden';
  csrf.name = '_token';
  csrf.value = pageData.csrf;
  form.appendChild(csrf);
  document.body.appendChild(form);
  form.submit();
};

onMounted(() => {
  // Inject server-rendered page content into our Vue layout
  const contentEl = document.getElementById('page-content');
  if (contentEl) {
    pageContent.value = contentEl.innerHTML;
    contentEl.remove();
  }
});
</script>
