import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { syncCsrfFromInertiaPage } from './csrf';

const xsrfCookieName =
    document.querySelector('meta[name="xsrf-cookie"]')?.getAttribute('content') ?? 'XSRF-TOKEN';

router.on('success', (event) => syncCsrfFromInertiaPage(event.detail.page));

createInertiaApp({
    http: {
        xsrfCookieName,
        xsrfHeaderName: 'X-XSRF-TOKEN',
    },
    title: (title) => {
        const appName =
            document.querySelector('meta[name="app-name"]')?.getAttribute('content') ?? 'Platform';
        return title ? `${title} - ${appName}` : appName;
    },
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        syncCsrfFromInertiaPage(props.initialPage);
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#000000',
    },
});
