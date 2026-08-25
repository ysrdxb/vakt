import { createApp, defineAsyncComponent } from 'vue';

// --- Auto-register page component mounts ---
// Each page that needs Vue drops a <div id="vue-PROJECT-FORM"></div>
// and a window.__VUE_PROPS__['PROJECT-FORM'] = { ... } config object.
// app.js finds the div, reads the props, and mounts the matching component.

import LoginForm from './components/LoginForm.vue';
import ProjectForm from './components/ProjectForm.vue';
import OperatorDashboard from './components/OperatorDashboard.vue';
import ProjectList from './components/ProjectList.vue';
import ProjectDetail from './components/ProjectDetail.vue';
import IncidentList from './components/IncidentList.vue';
import IncidentDetail from './components/IncidentDetail.vue';
import LogViewer from './components/LogViewer.vue';
import FileIntegrityView from './components/FileIntegrityView.vue';
import AuditTracker from './components/AuditTracker.vue';
import DailyLogCalendar from './components/DailyLogCalendar.vue';
import VulnerabilityList from './components/VulnerabilityList.vue';
import AlertLog from './components/AlertLog.vue';

const PAGE_COMPONENTS = {
    'vue-login-form':   LoginForm,
    'vue-project-form': ProjectForm,
    'vue-operator-dashboard': OperatorDashboard,
    'vue-project-list': ProjectList,
    'vue-project-detail': ProjectDetail,
    'vue-incident-list': IncidentList,
    'vue-incident-detail': IncidentDetail,
    'vue-log-viewer': LogViewer,
    'vue-file-integrity-view': FileIntegrityView,
    'vue-audit-tracker': AuditTracker,
    'vue-daily-log-calendar': DailyLogCalendar,
    'vue-vulnerability-list': VulnerabilityList,
    'vue-alert-log': AlertLog,
};

window.addEventListener('DOMContentLoaded', () => {
    Object.entries(PAGE_COMPONENTS).forEach(([mountId, component]) => {
        const el = document.getElementById(mountId);
        if (!el) return;

        // Read props from a companion <script> tag:  window.__VUE_PROPS__['vue-project-form'] = {...}
        const props = (window.__VUE_PROPS__ && window.__VUE_PROPS__[mountId]) || {};

        const app = createApp(component, props);
        app.mount(el);
    });
});
