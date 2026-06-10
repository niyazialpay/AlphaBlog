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

    const inputEntries = ['resources/js/panel.js', cssEntry, jsEntry]
        .filter((entry) => typeof entry === 'string' && entry.length > 0);

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
            alias: {
                '@': path.resolve(__dirname, 'resources/js'),
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
