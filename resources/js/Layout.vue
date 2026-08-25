<template>
  <div class="app-layout" id="main-layout">
    <!-- Mobile backdrop -->
    <div 
      class="sidebar-backdrop" 
      id="sidebar-backdrop" 
      :class="{ 'visible': isSidebarOpen }"
      @click="isSidebarOpen = false"
    ></div>

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar" :class="{ 'open': isSidebarOpen }">
      <div class="sidebar-logo">
        <div class="logo-icon">🛡️</div>
        <div>
          <div class="logo-text">Vakt</div>
          <div class="logo-sub">Monitoring</div>
        </div>
      </div>

      <nav class="sidebar-nav">
        <Link :href="route('dashboard')" class="nav-item" :class="{ 'active': $page.url === '/' || $page.url === '/client/dashboard' }">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
          Dashboard
        </Link>

        <div class="nav-section-label">Monitoring</div>

        <Link :href="route('projects.index')" class="nav-item" :class="{ 'active': $page.url.startsWith('/projects') }">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" /></svg>
          Projects
        </Link>

        <Link :href="route('incidents.index')" class="nav-item" :class="{ 'active': $page.url.startsWith('/incidents') }">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
          Incidents
          <span v-if="$page.props.notifications.p1_active > 0" class="nav-badge">{{ $page.props.notifications.p1_active }}</span>
        </Link>

        <Link :href="route('daily-logs.index')" class="nav-item" :class="{ 'active': $page.url.startsWith('/daily-logs') }">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
          Daily Logs
        </Link>

        <Link :href="route('logs.index')" class="nav-item" :class="{ 'active': $page.url.startsWith('/logs') }">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
          Log Viewer
        </Link>

        <div class="nav-section-label">Security</div>

        <Link :href="route('file-integrity.index')" class="nav-item" :class="{ 'active': $page.url.startsWith('/file-integrity') }">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
          File Integrity
        </Link>

        <Link :href="route('audit.index')" class="nav-item" :class="{ 'active': $page.url.startsWith('/audit') }">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
          Security Audit
        </Link>

        <Link :href="route('vulnerabilities.index')" class="nav-item" :class="{ 'active': $page.url.startsWith('/vulnerabilities') }">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
          Vulnerabilities
        </Link>

        <div class="nav-section-label">Pipeline</div>

        <Link :href="route('improvements.index')" class="nav-item" :class="{ 'active': $page.url.startsWith('/improvements') }">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
          Improvements
        </Link>

        <Link :href="route('reports.index')" class="nav-item" :class="{ 'active': $page.url.startsWith('/reports') }">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
          SQA Reports
        </Link>

        <Link :href="route('alerts.index')" class="nav-item" :class="{ 'active': $page.url.startsWith('/alerts') }">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
          Alerts
        </Link>

        <div class="nav-section-label">System</div>

        <Link :href="route('settings.index')" class="nav-item" :class="{ 'active': $page.url.startsWith('/settings') }">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
          Settings
        </Link>
      </nav>

      <div class="sidebar-footer" v-if="$page.props.auth.user">
        <div class="sidebar-user">
          <div class="user-avatar">{{ $page.props.auth.user.initials }}</div>
          <div class="user-info">
            <div class="user-name">{{ $page.props.auth.user.name }}</div>
            <div class="user-role">{{ $page.props.auth.user.role }}</div>
          </div>
        </div>
      </div>
    </aside>

    <!-- MAIN -->
    <main class="main-content">
      <!-- Topbar -->
      <header class="topbar">
        <button class="mobile-menu-btn" @click="isSidebarOpen = true" title="Menu">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
        </button>

        <div class="topbar-search-wrap topbar-search">
          <svg class="topbar-search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
          <input type="text" placeholder="Search projects, incidents..." />
        </div>

        <div class="topbar-actions">
          <Link v-if="$page.props.notifications.p1_active > 0" :href="route('incidents.index')" style="display:flex;align-items:center;gap:6px;padding:6px 12px;background:rgba(255,71,87,0.12);border:1px solid rgba(255,71,87,0.3);border-radius:8px;color:var(--color-danger);font-size:0.78rem;font-weight:700;">
            <span style="width:7px;height:7px;background:var(--color-danger);border-radius:50%;animation:blink 1s ease-in-out infinite;"></span>
            {{ $page.props.notifications.p1_active }} P1 ACTIVE
          </Link>

          <button class="icon-btn" title="Notifications">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:18px;height:18px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
          </button>

          <form :action="route('logout')" method="POST" style="display:inline; margin:0; padding:0;">
            <input type="hidden" name="_token" :value="$page.props.csrf_token" />
            <button type="submit" class="icon-btn" title="Logout">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:18px;height:18px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
            </button>
          </form>
        </div>
      </header>

      <!-- Page Content: Vue mounts here per-page -->
      <div class="page-body">
        <slot />
      </div>
    </main>

    <!-- Toast Notifications Container -->
    <div style="position: fixed; bottom: 24px; right: 24px; z-index: 10000; display: flex; flex-direction: column; gap: 12px; pointer-events: none;">
      <div v-for="toast in toasts" :key="toast.id" 
           style="pointer-events: auto; background: #1e293b; padding: 16px 20px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); display: flex; align-items: center; gap: 12px; border: 1px solid rgba(255,255,255,0.1); min-width: 300px; animation: slideInRight 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;">
        
        <svg v-if="toast.type === 'success'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 24px; height: 24px; color: #10b981;">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <svg v-else-if="toast.type === 'error'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 24px; height: 24px; color: #ef4444;">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 24px; height: 24px; color: #3b82f6;">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>

        <span style="font-size: 14px; font-weight: 500; color: #f8f9fa;">{{ toast.message }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const isSidebarOpen = ref(false);
const page = usePage();
const toasts = ref([]);

const addToast = (type, message) => {
  if (!message) return;
  const id = Date.now() + Math.random();
  toasts.value.push({ id, type, message });
  setTimeout(() => {
    toasts.value = toasts.value.filter(t => t.id !== id);
  }, 4000);
};

watch(() => page.props.flash, (flash) => {
  if (!flash) return;
  if (flash.success) addToast('success', flash.success);
  if (flash.error) addToast('error', flash.error);
  if (flash.warning) addToast('warning', flash.warning);
  if (flash.info) addToast('info', flash.info);
}, { deep: true, immediate: true });

if (typeof window !== 'undefined') {
  window.addEventListener('toast', (e) => {
    addToast(e.detail.type || 'success', e.detail.message || e.detail.title);
  });
}
</script>

<style>
@keyframes slideInRight {
  from { opacity: 0; transform: translateX(100%); }
  to { opacity: 1; transform: translateX(0); }
}
</style>
