import '../bootstrap';

import { createApp, h } from 'vue';
import { createInertiaApp, Head, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

import PanelLayout from './Layouts/PanelLayout.vue';
import { initTheme } from './composables/useTheme';
import { __, transChoice } from './composables/useLang';

import '../../css/panel.css';

/*
 * Panel bundle'ı SSR build'ine ASLA girmez (laravel-vite-plugin --ssr çalışırken
 * `input` yok sayılır). Yine de savunmacı davran: initTheme localStorage ve
 * matchMedia kullanıyor, Node tarafında patlardı.
 */
if (typeof window !== 'undefined') {
    initTheme();
    window.route = route;
}

const appName = document.querySelector('title')?.innerText || '';

/*
 * LAZY glob — eager DEĞİL. Eager olsaydı 40+ ekran, TinyMCE sarmalayıcısı ve
 * menü kurucu tek bir ilk chunk'a girerdi. Repodaki 6 tema da lazy kullanıyor.
 *
 * İkinci glob modül panel sayfalarını bağlar: modüller kendi dizinlerinde yaşar,
 * çekirdek repoya girmez; modül yoksa glob boş geçer ve build sorunsuz sürer.
 */
const corePages = import.meta.glob('./Pages/**/*.vue');
const modulePages = import.meta.glob('../../../Modules/*/resources/js/panel/Pages/**/*.vue');

const moduleLookup = Object.fromEntries(
    Object.entries(modulePages).map(([path, importer]) => {
        // ../../../Modules/ValeFix/resources/js/panel/Pages/Customers/Index.vue
        //   → valefix::Customers/Index
        const match = path.match(/Modules\/([^/]+)\/resources\/js\/panel\/Pages\/(.+)\.vue$/);

        return match ? [`${match[1].toLowerCase()}::${match[2]}`, importer] : [path, importer];
    }),
);

function resolvePage(name) {
    if (name.includes('::')) {
        const [namespace, path] = name.split('::');

        return moduleLookup[`${namespace.toLowerCase()}::${path}`];
    }

    return corePages[`./Pages/${name}.vue`];
}

createInertiaApp({
    title: (title) => [title, appName].filter(Boolean).join(' · '),
    progress: { color: '#4f46e5', showSpinner: false },
    resolve: (name) => {
        const importer = resolvePage(name);

        if (!importer) {
            throw new Error(`Panel sayfası bulunamadı: ${name}`);
        }

        return importer().then((module) => {
            // `=== undefined` bilinçli: `layout: null` (Auth ekranları) korunmalı,
            // `??=` kullanılsaydı null de ezilirdi.
            if (module.default.layout === undefined) {
                module.default.layout = PanelLayout;
            }

            return module;
        });
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        app.use(plugin);

        // Panel ayrı bir bundle; global kayıt temalara sızmaz ve modül
        // sayfalarının da bunları import etmeden kullanmasını sağlar.
        app.component('Link', Link);
        app.component('Head', Head);
        app.config.globalProperties.route = route;
        app.config.globalProperties.__ = __;
        app.config.globalProperties.$t = __;
        app.config.globalProperties.transChoice = transChoice;

        app.mount(el);
    },
});
