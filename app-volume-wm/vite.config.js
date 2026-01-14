import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js',
                // GAM Styles
                'resources/css/gam-css-loader.css',
                // GAM JavaScript
                'resources/js/gam-modules-loader.js',
            ],
            refresh: true,
        }),
    ],
});
