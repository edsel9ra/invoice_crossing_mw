import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import AppLayout from './Layouts/AppLayout.vue';
import PublicLayout from './Layouts/PublicLayout.vue';

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        const page = pages[`./Pages/${name}.vue`];

        if (!page) {
            throw new Error(`Page not found: ${name}`);
        }

        if (name === 'Auth/Login') {
            page.default.layout = page.default.layout || PublicLayout;
        } else if (name === 'Crossings/Create') {
            page.default.layout = page.default.layout || PublicLayout;
        } else {
            page.default.layout = page.default.layout || AppLayout;
        }

        return page;
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    title: (title) => title ? `${title} — Invoice Crossing` : 'Invoice Crossing',
    progress: {
        delay: 80,
        color: '#f59e0b',
        includeCSS: true,
        showSpinner: false,
    },
});
