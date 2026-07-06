import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const lanHost = env.LAN_HOST || 'localhost';
    const vitePort = Number(env.VITE_PORT || 5173);
    const lanOrigin = `http://${lanHost}:${vitePort}`;

    return {
        plugins: [
            laravel({
                input: [
                    'resources/css/app.css',
                    'resources/js/app.js',
                    'resources/js/quill.js',
                    'resources/js/jsbarcode.js',
                ],
                refresh: true,
            }),
            tailwindcss(),
        ],
        server: {
            host: '0.0.0.0',
            port: vitePort,
            strictPort: true,
            origin: lanOrigin,
            cors: true,
            hmr: {
                host: lanHost,
                port: vitePort,
            },
        },
    };
});
