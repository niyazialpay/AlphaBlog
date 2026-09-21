import './bootstrap';

import { createApp, h } from 'vue';
import { createInertiaApp, Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

import PanelLayout from './Layouts/PanelLayout.vue';
import { initTheme } from './composables/useTheme';
import { __, transChoice } from './composables/useLang';
import { pushToast } from './composables/useToast';

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
 * CSRF token'ini her Inertia yanitindan tazele.
 *
 * bootstrap.js basligi kok blade'deki <meta>'dan BIR KEZ kuruyor. Panel bir SPA
 * oldugu icin kok blade bir daha render edilmiyor; oturum yenilendiginde
 * (giris, uzun acik sekme, oturum suresi) o baslik bayatliyor ve sunucu
 * `X-CSRF-TOKEN`'i `X-XSRF-TOKEN` cerezine TERCIH ettigi icin taze cerez
 * degeri hic kullanilmiyordu — her POST 419 "oturumunuz sona erdi" donuyordu.
 */
/*
 * Inertia olmayan yanitlari OKUNABILIR hale getir.
 *
 * Varsayilanda Inertia, Inertia olmayan bir yanit alinca govdeyi tam ekran bir
 * iframe modalinda gosteriyor. Sunucu JSON ya da bir yonlendirme govdesi
 * donduyse ekranda ya bembeyaz bir kutu ya da "Redirecting" yazisi kaliyor;
 * kullanici ne oldugunu, biz de hangi ucun neden dustugunu goremiyoruz.
 *
 * Modal yerine durum kodunu ve govdenin basini toast olarak basiyoruz. Tipik
 * sebepler: oturum/CSRF dusmesi (419), yetki (403) ve `$request->inertia()`
 * yanlis dallanip JSON donen uclar.
 */
router.on('invalid', (event) => {
    event.preventDefault();

    const response = event.detail?.response;
    const status = response?.status ?? '?';
    const data = response?.data;

    let detail = '';

    if (typeof data === 'string') {
        // HTML govde: etiketleri at, ilk anlamli satiri al.
        detail = data.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim().slice(0, 160);
    } else if (data && typeof data === 'object') {
        detail = data.message || JSON.stringify(data).slice(0, 160);
    }

    // eslint-disable-next-line no-console
    console.error('Inertia olmayan yanit', status, data);

    pushToast(`${status} — ${detail || __('general.error')}`, 'error', 9000);
});

router.on('success', (event) => {
    const token = event.detail.page?.props?.csrfToken;

    if (token && window.axios) {
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
    }
});

/*
 * LAZY glob — eager DEĞİL. Eager olsaydı 40+ ekran, TinyMCE sarmalayıcısı ve
 * menü kurucu tek bir ilk chunk'a girerdi. Repodaki 6 tema da lazy kullanıyor.
 *
 * İkinci glob modül panel sayfalarını bağlar: modüller kendi dizinlerinde yaşar,
 * çekirdek repoya girmez; modül yoksa glob boş geçer ve build sorunsuz sürer.
 */
const corePages = import.meta.glob('./Pages/**/*.vue');
/*
 * Modul sayfalari `Modules/` yerine AYNADAN okunur (scripts/panel-modules.mjs,
 * `prebuild`/`predev` ile calisir).
 *
 * Deploy'da `Modules` paylasilan bir dizine symlink olabiliyor ve glob symlink'i
 * guvenilir gezemiyor: hicbir modul sayfasi bundle'a girmiyor, her modul ekrani
 * "Panel sayfasi bulunamadi" ile aciliyordu. Ayna proje ici GERCEK bir dizin
 * oldugu icin davranis her ortamda ayni.
 */
const modulePages = import.meta.glob('./.modules/*/Pages/**/*.vue');

const moduleLookup = Object.fromEntries(
    Object.entries(modulePages).map(([path, importer]) => {
        // ./.modules/valefix/Pages/Customers/Index.vue -> valefix::Customers/Index
        const match = path.match(/\.modules\/([^/]+)\/Pages\/(.+)\.vue$/);

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
            /*
             * Sayfa bundle'da YOK — pratikte hep modul sayfalarinda olur, cunku
             * modul kodu site-yereldir (`/Modules` gitignore'lu) ve o kurulumda
             * derlemeye girmemis olabilir.
             *
             * Eskiden burada `throw` vardi: Inertia promise'i reddediyor, hicbir
             * sey render edilmiyor ve kullanici BOS EKRAN goruyordu; tek iz
             * konsoldaki hataydi. Bunun yerine hata sayfasini dondururuz —
             * ekranda ne eksik oldugu yazar, sidebar ve gezinme calismaya devam eder.
             */
            // eslint-disable-next-line no-console
            console.error(`Panel sayfasi bundle'da yok: ${name}`);

            return corePages['./Pages/Errors/Error.vue']().then((module) => {
                if (module.default.layout === undefined) {
                    module.default.layout = PanelLayout;
                }

                return module;
            });
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
