import { createApp } from 'vue';

const app = createApp({});

// Automatically mount Vue 3 components from resources/js/components
const components = import.meta.glob('./components/**/*.vue', { eager: true });
Object.entries(components).forEach(([path, definition]) => {
    const componentName = path.split('/').pop().replace(/\.\w+$/, '');
    app.component(componentName, definition.default || definition);
});

// Mount to #app element if present
if (document.getElementById('app')) {
    app.mount('#app');
}
