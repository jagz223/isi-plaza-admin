import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import {
    defineConfig
} from 'vite';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            ssr: 'resources/js/ssr.jsx',
            refresh: true,
        }),
        react(),
        tailwindcss(),
    ],
    esbuild: {
        jsx: 'automatic',
    },
    optimizeDeps: {
        include: ['react', 'react-dom', '@inertiajs/react', 'laravel-vite-plugin/inertia-helpers'],
    },
    server: {
        watch: {
            ignored: ['**/storage/**', '**/vendor/**', '**/tests/**'],
        },
    },
});