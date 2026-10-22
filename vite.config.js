import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/dashboard.js','resources/js/admin-dashboard.js', 'resources/js/app.js', 'resources/js/saved-listings.js', 'resources/css/home.css', 'resources/css/auth.css',  ],
            refresh: true,
        }),
    ],
    
});
