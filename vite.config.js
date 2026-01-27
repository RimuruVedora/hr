import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/login/login.js', 'resources/css/login/login.css', 'resources/css/admin-sidebar/sidebar.css', 'resources/js/admin-sidebar/sidebar.js', 'resources/css/dashboard/dashboard.css',
                 'resources/css/competency/competency_main_dashboard.css', 
                 'resources/js/competency/competency_main_dashboard.js',
                 'resources/css/competency/competency-gap-analytics.css',
                 'resources/js/competency/competency-gap-analytics.js',
                 'resources/css/user_management/user_management.css',
                 'resources/js/user_management/user_management.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
