import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/homepage.css',
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/homepage.js'
            ],
            refresh: false,
            buildDirectory: 'build',
        }),
    ],
    build: {
        assetsDir: '',
    },
});
