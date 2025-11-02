import './bootstrap';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} • ${appName}` : appName),
    resolve: (name) => {
        const pages = import.meta.glob('./**/*.vue');
        const path = `./${name}.vue`;

        if (! pages[path]) {
            console.error(`Inertia page not found for component "${name}" at path "${path}"`);
            throw new Error(`Inertia page not found: ${path}`);
        }

        return pages[path]();
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#2563eb',
    },
});
