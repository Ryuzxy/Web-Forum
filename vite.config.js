import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/echo.js'],
            refresh: true,
        }),
    ],
    server: {
        host: 'web-forum.local', // atau 'localhost'
        hmr: {
            host: 'web-forum.local',
        },
    },
});
