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
            qs: path.resolve(__dirname, 'node_modules/qs/lib/index.js'),
            'side-channel': path.resolve(__dirname, 'node_modules/side-channel/index.js'),
        },
    },
    optimizeDeps: {
        include: ['qs', 'side-channel'],
    },
});
