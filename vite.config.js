import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
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
    optimizeDeps: {
        // prod build'te kullanılmıyor ama dursun istiyorsan sade tut
        include: ['laravel-echo', 'pusher-js'],
    },
});
