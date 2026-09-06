// import { defineConfig } from 'vite';
// import laravel from 'laravel-vite-plugin';

// export default defineConfig({
//     plugins: [
//         laravel({
//             input: ['resources/css/app.css', 'resources/js/app.js'],
//             refresh: true,
//         }),
//     ],
// });


import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    // បន្ថែមផ្នែក server នេះចូល
    server: {
        host: '0.0.0.0',
        cors: true,
        hmr: {
            host: '192.168.1.2', // ដាក់ IP កុំព្យូទ័ររបស់អ្នកនៅទីនេះ
        },
    },
});