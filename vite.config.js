import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
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
    build: {
        // 解决打包兼容性
        // target: 'chrome89',
        // 生产环境移除console
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true,
            },
        },
    }
});
