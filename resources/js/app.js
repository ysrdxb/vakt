import { createApp, h } from 'vue';
import { createInertiaApp, Link } from '@inertiajs/vue3';
import Layout from './Layout.vue';
import { ZiggyVue } from 'ziggy-js';

createInertiaApp({
    resolve: name => {
        const pages = import.meta.glob('./components/**/*.vue', { eager: true })
        let page = pages[`./components/${name}.vue`]
        page.default.layout = page.default.layout || Layout
        return page
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue);
        
        app.component('Link', Link);
        app.mount(el);
    },
});
