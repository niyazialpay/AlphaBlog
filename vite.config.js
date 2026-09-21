import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig(({ mode }) => {
    // SECURITY: load only the prefixes the build actually needs. An empty prefix
    // plus Object.assign(process.env, env) previously pulled ALL .env secrets
    // (DB/SMTP/API keys) into the build process — a bundle-leak vector.
    const env = loadEnv(mode, process.cwd(), ['VITE_', 'THEME_']);
    const cssEntry = env.THEME_CSS_ENTRY || 'resources/css/app.css';
    const jsEntry = env.THEME_JS_ENTRY || 'resources/js/app.js';

    const inputEntries = [
        // Eski AdminLTE kabugu. Henuz tasinmamis cekirdek ekranlar ve 7 modulun
        // panel blade'leri buna bagli - son modul tasinana kadar KALMALI.
        'resources/js/panel.js',
        // Yeni Inertia paneli. resources/css/panel.css'i kendisi import eder,
        // o yuzden ayri bir CSS girisi gerekmez.
        'resources/js/panel/app.js',
        cssEntry,
        jsEntry,
    ].filter((entry) => typeof entry === 'string' && entry.length > 0);

    // Panel SSR build'ine ASLA girmez: laravel-vite-plugin --ssr calisirken `input`
    // yok sayilir ve panel girisi localStorage/matchMedia kullaniyor.
    const ssrEntry = env.THEME_SSR_ENTRY || null;

    return {
        plugins: [
            laravel({
                input: Array.from(new Set(inputEntries)),
                ssr: ssrEntry || undefined,
                refresh: true,
            }),
            vue(),
        ],
        resolve: {
            /*
             * Deploy'da `Modules` dizini paylasilan (shared) bir dizine SYMLINK.
             *
             * Varsayilanda Vite/rolldown bir modulu GERCEK yoluna cozuyor, yani
             * `<release>/Modules/X/...` yerine `<shared>/Modules/X/...` goruyor.
             * Node cozumlemesi oradan yukari dogru `node_modules` ararken
             * release dizinine hic ugramiyor ve modul panel sayfalarindaki
             * `@inertiajs/vue3` importu cozulemiyor:
             *
             *   [vite]: Rolldown failed to resolve import "@inertiajs/vue3"
             *   from ".../shared/Modules/Birdergi/.../Subscribers/Index.vue"
             *
             * `preserveSymlinks` symlink yolunu oldugu gibi birakir; arama
             * `<release>/Modules/...` uzerinden yukari cikar ve
             * `<release>/node_modules`'i bulur. Yerelde `Modules` gercek dizin
             * oldugu icin bu ayarin yerel derlemeye etkisi yok.
             */
            preserveSymlinks: true,
            alias: {
                '@': path.resolve(__dirname, 'resources/js'),
                // Modul panel sayfalari cekirdek SDK'sini bu alias ile import eder:
                //   import DataTable from '~panel/components/DataTable.vue'
                '~panel': path.resolve(__dirname, 'resources/js/panel'),
                // qs ve side-channel aliaslarını KALDIR
            },
        },
        server: {
            watch: {
                ignored: ['**/public/themes/**'],
            },
        },
        optimizeDeps: {
            // prod build'te kullanılmıyor ama dursun istiyorsan sade tut
            include: ['laravel-echo', 'pusher-js'],
        },
    };
});
