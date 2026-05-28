import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const rootDir = path.dirname(fileURLToPath(import.meta.url));

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, rootDir, '');
    const appUrl = env.APP_URL || 'http://127.0.0.1:8000';
    const devOrigin = env.VITE_DEV_SERVER_URL || 'http://127.0.0.1:5173';

    return {
        resolve: {
            alias: {
                '@': path.resolve(rootDir, 'resources/js'),
            },
        },
        server: {
            host: '127.0.0.1',
            port: 5173,
            strictPort: true,
            origin: devOrigin,
            cors: true,
            hmr: {
                host: '127.0.0.1',
                port: 5173,
            },
        },
        plugins: [
            laravel({
                input: 'resources/js/app.js',
                refresh: true,
                detectTls: false,
                hotFile: 'public/hot',
            }),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
        ],
    };
});
