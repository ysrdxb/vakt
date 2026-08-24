import { createApp, defineAsyncComponent } from 'vue';

// --- Auto-register page component mounts ---
// Each page that needs Vue drops a <div id="vue-PROJECT-FORM"></div>
// and a window.__VUE_PROPS__['PROJECT-FORM'] = { ... } config object.
// app.js finds the div, reads the props, and mounts the matching component.

import LoginForm from './components/LoginForm.vue';
import ProjectForm from './components/ProjectForm.vue';
import VueCounter from './components/VueCounter.vue';

const PAGE_COMPONENTS = {
    'vue-login-form':   LoginForm,
    'vue-project-form': ProjectForm,
    'vue-counter':      VueCounter,
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
