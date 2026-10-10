import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            // Blade（画面のひな形）やルートを保存したら、ブラウザを自動で読み込み直す
            refresh: ['resources/views/**', 'routes/**', 'lang/**', 'app/**'],
        }),
        tailwindcss(),
    ],
    server: {
        // Docker の外（ブラウザ）から http://localhost:5174 でつなげるようにする
        host: '0.0.0.0',
        port: 5174,
        strictPort: true,
        hmr: { host: 'localhost' },
        watch: {
            ignored: ['**/storage/framework/views/**', '**/vendor/**'],
        },
    },
});
